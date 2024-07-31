<?php namespace Wpstudio\Mms\Controllers\Containers\Handlers;

use Backend\Classes\BackendController;
use Illuminate\Support\Collection;
use Winter\Storm\Exception\ValidationException;
use Winter\Storm\Extension\ExtensionBase;
use Wpstudio\Mms\Classes\Cli;
use Wpstudio\Mms\Classes\Exceptions;
use Wpstudio\Mms\Classes\Nginx\NginxMasterProxy;
use Wpstudio\Mms\Classes\Nginx\NginxSite;
use Wpstudio\Mms\Classes\ProxmoxServer;
use Wpstudio\Mms\Classes\Enums;
use Wpstudio\Mms\Classes\Pve\ClusterTask;
use Wpstudio\Mms\Classes\Pve\UpIdStatus;
use Wpstudio\Mms\Classes\Traits\SiteInteractions;
use Wpstudio\Mms\Controllers\Containers;
use Wpstudio\Mms\Models;
use Validator;

class MoveSite extends ExtensionBase
{
    use SiteInteractions;

    private Models\Server $destinationServer;
    private ProxmoxServer $destinationProxmoxServer;
    private NginxMasterProxy $destinationNginxMasterProxy;
    private NginxSite $destinationNginxSite;

    public array $rulesInput = [
        'destinationServerId' => 'required|exists:' . Models\Server::class . ',id'
    ];

    public function __construct(Containers $controller)
    {
        if (array_reverse(explode('/', $controller->actionUrl()))[0] == 'update') {
            $this->rulesInput['destinationServerId'] .= '|not_in:' . BackendController::$params[0];
        }
    }

    /**
     * @throws Exceptions\MmsException
     * @throws Exceptions\MmsCliException
     * @throws ValidationException
     */
    public function onMoveSite(): void
    {
        $this->validateInput();
        $this->prepareModel();
        $this->prepareLinuxContainer();
        $this->prepareCurrentMasterNginxProxy();
        $this->prepareNginxSite();

        $this->prepareDestination();

        $this->testNginxOnSource();
        $this->testNginxOnDestination();

        $this->migrateLinuxContainer();

        $this->reassignContainerToDestinationServer();

        $this->moveNginxConfig();
        $this->copyNginxAuth();

        if (!$this->nginxSite->nginxConfig->isTemporarySelfSignedLetsencryptSsl()) {
            $this->moveSslDirsAndRenewalConfig();
        }

        $this->testNginxOnDestination();
        $this->reloadNginxOnDestination();

        $this->removeNginxConfigOnSource();

        if (!$this->nginxSite->nginxConfig->isTemporarySelfSignedLetsencryptSsl()) {
            $this->removeSslDirsAndRenewalConfigOnSource();
        }

        $this->testNginxOnSource();
        $this->reloadNginxOnSource();

        \Session::flash('onMoveSiteMessage', 'Сайт перенесен');
    }

    /**
     * @return void
     * @throws ValidationException
     */
    private function validateInput(): void
    {
        $validator = Validator::make(
            [
                'destinationServerId' => $this->getInputDestinationServerId(),
            ],
            $this->rulesInput,
        );

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    private function getInputDestinationServerId(): int
    {
        return intval(input('MoveSite.destinationServerId'));
    }

    /**
     * @return void
     * @throws Exceptions\MmsException
     */
    private function prepareDestination(): void
    {
        $this->destinationServer = Models\Server::find($this->getInputDestinationServerId());
        $this->destinationProxmoxServer = $this->destinationServer->getProxmoxServer();

        $this->destinationMasterNginxProxy = $this->destinationServer->getNginxMasterProxy();

        $this->destinationNginxSite = new NginxSite(
            $this->destinationServer->getNginxMasterProxy(),
            $this->container->getNginxSiteCode(),
            $this->nginxSite->nginxConfig->getConfigFileContent()
        );


        if ($this->destinationNginxSite->nginxConfig->hasExistsConfigFile()) {
            throw new Exceptions\MmsException(sprintf(
                'Nginx config file "%s" on destination server "%s" is exists.',
                $this->destinationNginxSite->nginxConfig->getAbsoluteConfigFilePath(),
                $this->destinationServer->code
            ));
        }

        if ($this->destinationNginxSite->sslCert->hasExistsLiveDir()) {
            throw new Exceptions\MmsException(sprintf(
                'Letsencrypt live dir "%s" on destination server "%s" is exists.',
                $this->destinationNginxSite->sslCert->getAbsoluteLiveDirPath(),
                $this->destinationServer->code
            ));
        }

        if ($this->destinationNginxSite->sslCert->hasExistsArchiveDir()) {
            throw new Exceptions\MmsException(sprintf(
                'Letsencrypt archive dir "%s" on destination server "%s" is exists.',
                $this->destinationNginxSite->sslCert->getAbsoluteArchiveDirPath(),
                $this->destinationServer->code
            ));
        }

        if ($this->destinationNginxSite->sslCert->hasExistsRenewalConfigFile()) {
            throw new Exceptions\MmsException(sprintf(
                'Letsencrypt renewal config file "%s" on destination server "%s" is exists.',
                $this->destinationNginxSite->sslCert->getAbsoluteRenewalConfigFilePath(),
                $this->destinationServer->code
            ));
        }

    }

    /**
     * @return void
     * @throws Exceptions\MmsException
     */
    private function migrateLinuxContainer(): void
    {
        /**
         * @see https://pve.proxmox.com/pve-docs/api-viewer/index.html#/nodes/%7Bnode%7D/lxc/%7Bvmid%7D/migrate
         */
        $migrateResult = $this->linuxContainer->vmId->migrate()->post([
            'node' => $this->linuxContainer->proxmoxServer->server->code,
            'target' => $this->destinationServer->code,
            'vmid' => $this->linuxContainer->container->code,
            'restart' => true,
        ]);

        if (!is_array($migrateResult) || !array_key_exists('data', $migrateResult)) {
            throw new Exceptions\MmsException(sprintf(
                'Migrate task from %s to %s for %s fails. Not returned data from api.',
                $this->linuxContainer->proxmoxServer->server->code,
                $this->destinationServer->code,
                $this->linuxContainer->container->code
            ));
        }

        $migrateTaskUpId = $migrateResult['data'];

        do {
            sleep(5);

            $upIdStatus = new UpIdStatus(
                $this->linuxContainer->proxmoxServer->node->tasks()->upId($migrateTaskUpId)->status()->get()['data'],
                $this->linuxContainer->proxmoxServer
            );
        } while ($upIdStatus->status != Enums\UpIdStatus\TaskStatus::Stopped);

        if ($upIdStatus->exitstatus != Enums\UpIdStatus\ExitStatus::OK) {
            throw new Exceptions\MmsException(sprintf(
                'Migration fails with exist status: %s',
                $upIdStatus->exitstatus
            ));
        }

        $isStartedLinuxContainerOnDestination = false;

        $allowedRetriesForFetchingVzStartClusterTask = 4;

        $currentRetryOfFetchingVzStartClusterTask = 1;

        $vzStartClusterTaskUpId = null;

        do {
            if ($currentRetryOfFetchingVzStartClusterTask == $allowedRetriesForFetchingVzStartClusterTask) {
                throw new Exceptions\MmsException(sprintf(
                    'Unable to fetch vzstart cluster task after %d retries',
                    $currentRetryOfFetchingVzStartClusterTask
                ));
            }

            sleep(5);

            $clusterTasks = collect($this->linuxContainer->proxmoxServer->node->getPve()->cluster()->tasks()->get()['data'])
                ->map(fn(array $clusterTaskItem) => new ClusterTask($clusterTaskItem, $this->linuxContainer->proxmoxServer->proxmoxCluster));

            assert($clusterTasks instanceof Collection);

            $migrateClusterTask = $clusterTasks->filter(
                fn(ClusterTask $clusterTask) => $clusterTask->id == $this->linuxContainer->container->code &&
                    $clusterTask->type == Enums\UpIdStatus\TaskType::VzMigrate &&
                    $clusterTask->node == $this->linuxContainer->proxmoxServer->server->code
            )->first();

            if (!$migrateClusterTask) {
                throw new Exceptions\MmsException(sprintf(
                    'Unable to find migrate tasks in cluster tasks'
                ));
            }

            assert($migrateClusterTask instanceof ClusterTask);

            if (is_null($vzStartClusterTaskUpId)) {
                $vzStartClusterTask = $clusterTasks->filter(
                    fn(ClusterTask $clusterTask) => $clusterTask->id == $this->linuxContainer->container->code &&
                        $clusterTask->type == Enums\UpIdStatus\TaskType::VzStart &&
                        $clusterTask->node == $this->destinationProxmoxServer->server->code &&
                        $clusterTask->starttime > $migrateClusterTask->starttime
                )->first();
            } else {
                $vzStartClusterTask = $clusterTasks->firstWhere('upid', $vzStartClusterTaskUpId);
            }

            if ($vzStartClusterTask) {
                $vzStartClusterTaskUpId = $vzStartClusterTask->upid;

                assert($vzStartClusterTask instanceof ClusterTask);

                if (!is_null($vzStartClusterTask->status)) {
                    if ($vzStartClusterTask->status != Enums\ClusterTask\TaskStatus::OK) {
                        throw new Exceptions\MmsException(sprintf(
                            'Fails to start migrated container: %s',
                            $vzStartClusterTask->status
                        ));
                    } else {
                        $isStartedLinuxContainerOnDestination = true;
                    }
                }
            } else {
                $currentRetryOfFetchingVzStartClusterTask++;
            }
        } while (!$isStartedLinuxContainerOnDestination);
    }

    private function reassignContainerToDestinationServer(): void
    {
        $this->container->server_id = $this->destinationServer->id;

        $this->container->save();
    }

    /**
     * @return void
     * @throws Exceptions\MmsCliException
     * @throws Exceptions\MmsCliFileNotFoundException
     */
    private function moveNginxConfig(): void
    {
        $this->linuxContainer->proxmoxServer->cli->copyFileToRemote(
            $this->nginxSite->nginxConfig->getAbsoluteConfigFilePath(),
            $this->destinationNginxSite->nginxConfig->getAbsoluteConfigFilePath(),
            $this->destinationProxmoxServer->server->main_ip_address
        );

        $this->destinationProxmoxServer->cli->chmodFile(
            $this->destinationNginxSite->nginxConfig->getAbsoluteConfigFilePath(),
            Cli::CGROUPS_ROOT_UID
        );
    }

    /**
     * @return void
     * @throws Exceptions\MmsCliException
     * @throws Exceptions\MmsCliFileNotFoundException
     */
    private function copyNginxAuth(): void
    {
        if ($this->nginxSite->hasNginxAuthConfig()) {
            if (!$this->destinationNginxSite->nginxAuthConfig->hasExistsAuthConfigFile() &&
                $this->nginxSite->nginxAuthConfig->hasExistsAuthConfigFile()
            ) {
                $this->linuxContainer->proxmoxServer->cli->copyFileToRemote(
                    $this->nginxSite->nginxAuthConfig->getAbsoluteAuthConfigFilePath(),
                    $this->destinationNginxSite->nginxAuthConfig->getAbsoluteAuthConfigFilePath(),
                    $this->destinationProxmoxServer->server->main_ip_address
                );

                $this->destinationProxmoxServer->cli->chmodFile(
                    $this->destinationNginxSite->nginxAuthConfig->getAbsoluteAuthConfigFilePath(),
                    Cli::CGROUPS_ROOT_UID
                );
            }

            if (!$this->destinationNginxSite->nginxAuthConfig->hasExistsPasswordsFile() &&
                $this->nginxSite->nginxAuthConfig->hasExistsPasswordsFile()
            ) {
                $this->linuxContainer->proxmoxServer->cli->copyFileToRemote(
                    $this->nginxSite->nginxAuthConfig->getAbsolutePasswordsFilePath(),
                    $this->destinationNginxSite->nginxAuthConfig->getAbsolutePasswordsFilePath(),
                    $this->destinationProxmoxServer->server->main_ip_address
                );

                $this->destinationProxmoxServer->cli->chmodFile(
                    $this->destinationNginxSite->nginxAuthConfig->getAbsolutePasswordsFilePath(),
                    Cli::CGROUPS_ROOT_UID
                );
            }
        }
    }

    /**
     * @return void
     * @throws Exceptions\MmsCliException
     * @throws Exceptions\MmsCliFileNotFoundException
     */
    private function moveSslDirsAndRenewalConfig(): void
    {
        $this->linuxContainer->proxmoxServer->cli->copyDirToRemote(
            $this->nginxSite->sslCert->getAbsoluteLiveDirPath(),
            $this->destinationNginxSite->sslCert->getAbsoluteLiveDirPath(),
            $this->destinationProxmoxServer->server->main_ip_address
        );

        $this->linuxContainer->proxmoxServer->cli->copyDirToRemote(
            $this->nginxSite->sslCert->getAbsoluteArchiveDirPath(),
            $this->destinationNginxSite->sslCert->getAbsoluteArchiveDirPath(),
            $this->destinationProxmoxServer->server->main_ip_address
        );

        $this->linuxContainer->proxmoxServer->cli->copyFileToRemote(
            $this->nginxSite->sslCert->getAbsoluteRenewalConfigFilePath(),
            $this->destinationNginxSite->sslCert->getAbsoluteRenewalConfigFilePath(),
            $this->destinationProxmoxServer->server->main_ip_address
        );

        $this->destinationProxmoxServer->cli->chmodFile(
            $this->destinationNginxSite->sslCert->getAbsoluteRenewalConfigFilePath(),
            Cli::CGROUPS_ROOT_UID
        );
    }

    /**
     * @return void
     * @throws Exceptions\MmsCliException
     */
    private function testNginxOnDestination(): void
    {
        $this->destinationProxmoxServer->cli->run([
            sprintf(
                'pct exec %d -- %s',
                $this->destinationMasterNginxProxy->linuxContainer->container->code,
                'nginx -t'
            ),
        ]);
    }

    /**
     * @return void
     * @throws Exceptions\MmsCliException
     */
    private function testNginxOnSource(): void
    {
        $this->linuxContainer->proxmoxServer->cli->run([
            sprintf(
                'pct exec %d -- %s',
                $this->currentMasterNginxProxy->linuxContainer->container->code,
                'nginx -t'
            ),
        ]);
    }

    /**
     * @return void
     * @throws Exceptions\MmsCliException
     */
    private function reloadNginxOnDestination(): void
    {
        $this->destinationProxmoxServer->cli->run([
            sprintf(
                'pct exec %d -- %s',
                $this->destinationMasterNginxProxy->linuxContainer->container->code,
                'nginx -s reload'
            ),
        ]);
    }

    /**
     * @return void
     * @throws Exceptions\MmsCliException
     */
    private function reloadNginxOnSource(): void
    {
        $this->linuxContainer->proxmoxServer->cli->run([
            sprintf(
                'pct exec %d -- %s',
                $this->currentMasterNginxProxy->linuxContainer->container->code,
                'nginx -s reload'
            ),
        ]);
    }

    /**
     * @return void
     * @throws Exceptions\MmsCliException
     */
    private function removeNginxConfigOnSource(): void
    {
        $this->linuxContainer->proxmoxServer->cli->rmFile($this->nginxSite->nginxConfig->getAbsoluteConfigFilePath());
    }

    /**
     * @return void
     * @throws Exceptions\MmsCliException
     */
    private function removeSslDirsAndRenewalConfigOnSource(): void
    {
        $this->linuxContainer->proxmoxServer->cli->rmDir($this->nginxSite->sslCert->getAbsoluteLiveDirPath());
        $this->linuxContainer->proxmoxServer->cli->rmDir($this->nginxSite->sslCert->getAbsoluteArchiveDirPath());
        $this->linuxContainer->proxmoxServer->cli->rmFile($this->nginxSite->sslCert->getAbsoluteRenewalConfigFilePath());
    }

}
