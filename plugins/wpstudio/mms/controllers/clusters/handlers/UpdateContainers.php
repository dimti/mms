<?php namespace Wpstudio\Mms\Controllers\Clusters\Handlers;

use Backend\Classes\BackendController;
use Winter\Storm\Extension\ExtensionBase;
use Wpstudio\Mms\Classes\LinuxContainer;
use Wpstudio\Mms\Classes\Pve\LxcConfig;
use Wpstudio\Mms\Classes\Pve\LxcStatus;
use Wpstudio\Mms\Classes\Pve\NodeStatus;
use Wpstudio\Mms\Classes\ProxmoxCluster;
use Wpstudio\Mms\Classes\ProxmoxServer;
use Wpstudio\Mms\Controllers\Clusters;
use Wpstudio\Mms\Models;

class UpdateContainers extends ExtensionBase
{
    private Clusters $controller;

    private Models\Cluster $cluster;

    private ProxmoxCluster $proxmoxCluster;

    public function __construct(Clusters $controller)
    {
        $this->controller = $controller;
    }

    /**
     * @return void
     * @throws \Wpstudio\Mms\Classes\Exceptions\MmsException
     */
    public function onUpdateContainers(): void
    {
        $this->prepareModel();

        $this->prepareProxmoxCluster();

        $this->proxmoxCluster->updateStatus();

        $this->proxmoxCluster->prepareNodesStatuses();

        $this->proxmoxCluster->prepareNodes();

        $this->syncServers();

        $this->proxmoxCluster->prepareProxmoxServers();

        $this->proxmoxCluster->proxmoxServers->each(function (ProxmoxServer $proxmoxServer) {
            $proxmoxServer->updateStatus();

            $proxmoxServer->prepareLxcStatues();

            $proxmoxServer->prepareVmIds();

            $proxmoxServer->prepareLxcConfigs();

            $this->syncContainers($proxmoxServer);

            $proxmoxServer->prepareLinuxContainers();

            $proxmoxServer->linuxContainers->each(fn(LinuxContainer $linuxContainer) => $linuxContainer->updateStatusAndConfig());
        });
    }

    private function prepareModel(): void
    {
        $this->cluster = Models\Cluster::with([
            'servers',
            'servers.containers',
            'servers.containers.networkType',
        ])->whereKey(BackendController::$params[0])->firstOrFail();
    }

    private function prepareProxmoxCluster(): void
    {
        $this->proxmoxCluster = new ProxmoxCluster($this->cluster);
    }

    private function syncServers(): void
    {
        $ghostsServers = Models\Server::whereDoesntHave('cluster');

        $this->cluster->servers->keyBy('code')->diffKeys($this->proxmoxCluster->nodesStatuses)->each(
            fn(Models\Server $server) => $server->delete()
        );

        $nodeStatusesWithoutServer = $this->proxmoxCluster->nodesStatuses
            ->filter(
                fn(NodeStatus $nodeStatus, string $serverCode) =>
                !$this->cluster->servers->where('code', '=', $serverCode)->count()
            );

        $isNeedReloadRelation = !!$nodeStatusesWithoutServer->count();

        $nodeStatusesWithoutServer->each(function (NodeStatus $nodeStatus, string $serverCode) use ($ghostsServers) {
                $ghostServer = $ghostsServers->where('code', '=', $serverCode)->first();

                if ($ghostServer) {
                    $this->cluster->servers()->add($ghostServer);
                } else {
                    $server = new Models\Server;

                    $server->name = sprintf(
                        'Server %s automatic created in cluster "%s"',
                        $serverCode,
                        $this->proxmoxCluster->cluster->name,
                    );

                    $server->code = $serverCode;

                    $server->main_ip_address = $this->proxmoxCluster
                        ->getNode($nodeStatus->node)
                        ->network()
                        ->iface('vmbr0')
                        ->get()['data']['address'];

                    $server->cluster_id = $this->cluster->id;

                    $server->server_type_id = Models\ServerType::whereCode(Models\ServerType::CODE_PROXMOX)->first()->id;

                    $server->save();
                }
            });

        if ($isNeedReloadRelation) {
            $this->cluster->reloadRelations('servers');
        }
    }

    private function syncContainers(ProxmoxServer $proxmoxServer): void
    {
        $proxmoxServer->server->containers->keyBy('code')->diffKeys($proxmoxServer->lxcStatuses)->each(
            fn(Models\Container $container) => $container->delete()
        );

        $lxcStatusesWithoutContainer = $proxmoxServer->lxcStatuses
            ->filter(fn(LxcStatus $lxcStatus, int $containerVmId) => !$proxmoxServer->server->containers->where('code', '=', $containerVmId)->count());

        $isNeedReloadRelation = !!$lxcStatusesWithoutContainer->count();

        $lxcStatusesWithoutContainer->each(function (LxcStatus $lxcStatus, int $containerVmId) use ($proxmoxServer) {
                $container = new Models\Container;

                $container->name = $lxcStatus->name;

                $container->code = $containerVmId;

                $container->server_id = $proxmoxServer->server->id;

                $lxcConfig = $proxmoxServer->lxcConfigs->get($containerVmId);

                assert($lxcConfig instanceof LxcConfig);

                $container->network_type_id = $lxcConfig->getNetworkType()->id;

                $destinationRoleCode = null;

                if ($lxcStatus->name == 'nginx') {
                    $destinationRoleCode = Models\DestinationRole::CODE_NGINX_MASTER_PROXY;
                } elseif (strstr($lxcStatus->name, 'redis') !== false) {
                    $destinationRoleCode = Models\DestinationRole::CODE_REDIS;
                } elseif (strstr($lxcStatus->name, 'db') !== false) {
                    $destinationRoleCode = Models\DestinationRole::CODE_DATABASE;
                } elseif (strstr($lxcStatus->name, 's3') !== false) {
                    $destinationRoleCode = Models\DestinationRole::CODE_S3;
                }

                if ($destinationRoleCode) {
                    $container->destination_role_id = Models\DestinationRole::whereCode($destinationRoleCode)->firstOrFail()->id;
                }

                $container->save();
            });

        if ($isNeedReloadRelation) {
            $proxmoxServer->server->reloadRelations('containers');
        }
    }
}
