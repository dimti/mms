<?php namespace Wpstudio\Mms\Controllers\Rescue\Handlers;

use Collective\Remote\Connection;
use Validator;
use Winter\Storm\Exception\ValidationException;
use Wpstudio\Mms\Classes\Helpers\FileContentHelper;
use Wpstudio\Mms\Classes\Helpers\SshHelper;
use Wpstudio\Mms\Classes\Exceptions\MmsException;
use Wpstudio\Mms\Models;

class PostInstallationActions extends \Winter\Storm\Extension\ExtensionBase
{
    const ZFS_ROOT_DATASET = 'rpool/ROOT/pve-1';
    const ROOT_FS_MOUNTPOINT = '/rpool/ROOT/pve-1';

    const NET_CONFIG_FILE_PATH = '/etc/network/interfaces';
    const MOD_PROBE_ZFS_CONF_FILE_PATH = '/etc/modprobe.d/zfs.conf';

    const APT_SOURCE_LIST_DIR_PATH = '/etc/apt/sources.list.d';
    const PVE_ENTERPRISE_APT_SOURCE_FILE_NAME = 'pve-enterprise.list';
    const PVE_NO_SUBSCRIPTION_APT_SOURCE_FILE_NAME = 'pve-no-subscription.list';

    const HOSTNAME_FILE_PATH = '/etc/hostname';
    const HOSTS_FILE_PATH = '/etc/hosts';
    const POSTFIX_MAIN_CF_FILE_PATH = '/etc/postfix/main.cf';

    const SSHD_CONFIG_FILE_PATH = '/etc/ssh/sshd_config';

    const SSH_DIR_PATH = '/root/.ssh';
    const SSH_DIR_MODE = '755';

    const SSH_AUTHORIZED_KEYS_FILE_NAME = 'authorized_keys';
    const SSH_AUTHORIZED_KEYS_FILE_MODE = '600';

    public string $ip;
    public string $password;
    public string $hostname;

    private Connection $sshConnection;

    private string $realNetName;

    private string $gatewayIpAddress;

    private array $rulesInput = [
        'ip' => 'required|ip',
        'password' => 'required',
        'hostname' => [
            'required',
            'regex:#(?=^.{4,253}$)(^((?!-)[a-zA-Z0-9-]{0,62}[a-zA-Z0-9]\.)+[a-zA-Z]{2,63}$)#'
        ],
    ];

    private array $rulesPrepared = [
        'realNetName' => [
            'required',
            'regex:#enp\d{1,2}s0#'
        ],
        'gatewayIpAddress' => 'required|ip',
    ];

    /**
     * @return array
     * @throws ValidationException
     */
    public function onPostInstallationActions(): array
    {
        $result = [];

        $this->prepareInputVars();

        $this->validateInput();

        $this->prepareSshConnection();
        $this->killQemu();

        $this->prepareNetData();

        $this->validatePrepared();

        $this->mountPveRootFs();

        $this->writeNetConfig();
        $this->writeModProbeZfsConf();

        $this->replacePveEnterpriseAptSourceToNoSubscription();
        $this->setHostname();

        $this->disableSshPasswordAuthentication();
        $this->addingNeedfulSshKeys();

        $this->createClusterAndServerModels();

        $this->unmountPveRootFs();
        $this->rebootNode();

        return $result;
    }

    private function prepareInputVars(): void
    {
        $this->ip = input('ip');
        $this->password = input('password');
        $this->hostname = input('hostname');
    }

    private function prepareSshConnection(): void
    {
        $this->sshConnection = SshHelper::getConnection($this->ip, $this->password);
    }

    private function killQemu(): void
    {
        /**
         * Unnecessary turned off virtual machine from previous action
         * @see SetupHetznerProxmoxServer::onSetupHetznerProxmoxServer()
         */
        $this->sshConnection->run('pkill -9 qemu');
    }

    private function prepareNetData(): void
    {
        /**
         * @see https://en.wikipedia.org/wiki/Whitespace_character#Unicode
         */
        $delimiterValue = "' '";

        /**
         * @example "    altname enp8s0"
         */
        $netNameFieldIndex = 6;

        $this->realNetName = SshHelper::getOutput($this->sshConnection, sprintf(
            'ip a | grep altname | cut -d%s -f%d',
            $delimiterValue,
            $netNameFieldIndex
        ));

        $gatewayIpFieldIndex = 3;

        /**
         * @example "default via 65.109.105.193 dev eth0"
         */
        $this->gatewayIpAddress = SshHelper::getOutput($this->sshConnection, sprintf(
            'ip route list | grep default | cut -d%s -f%d',
            $delimiterValue,
            $gatewayIpFieldIndex
        ));
    }

    /**
     * @return void
     * @throws ValidationException
     */
    private function validateInput(): void
    {
        $validator = Validator::make(
            [
                'ip' => $this->ip,
                'password' => $this->password,
                'hostname' => $this->hostname,
            ],
            $this->rulesInput,
        );

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    /**
     * @return void
     * @throws ValidationException
     */
    private function validatePrepared(): void
    {
        $validator = Validator::make(
            [
                'realNetName' => $this->realNetName,
                'gatewayIpAddress' => $this->gatewayIpAddress,
            ],
            $this->rulesPrepared,
        );

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    private function mountPveRootFs(): void
    {
        $this->sshConnection->run([
            'zpool import rpool',
            sprintf(
                'zfs set mountpoint=%s %s',
                self::ROOT_FS_MOUNTPOINT,
                self::ZFS_ROOT_DATASET,
            ),
        ]);
    }

    private function unmountPveRootFs(): void
    {
        $this->sshConnection->run([
            sprintf(
                'zfs set mountpoint=/ %s',
                self::ZFS_ROOT_DATASET,
            ),
            'zpool export rpool',
        ]);
    }

    private function rebootNode(): void
    {
        $this->sshConnection->run('sh -c "sleep 2 && reboot" &');
    }

    private function writeNetConfig(): void
    {
        $this->sshConnection->putString(
            self::ROOT_FS_MOUNTPOINT . self::NET_CONFIG_FILE_PATH,
            <<<EOF
auto lo
iface lo inet loopback

auto $this->realNetName
iface $this->realNetName inet manual

auto vmbr0
iface vmbr0 inet static
        address $this->ip/26
        gateway $this->gatewayIpAddress
        bridge-ports $this->realNetName
        bridge-stp off
        bridge-fd 0

auto vmbr1
iface vmbr1 inet static
        address 172.16.30.100/24
        bridge-ports none
        bridge-stp off
        bridge-fd 0

EOF
        );
    }

    private function writeModProbeZfsConf(): void
    {
        $this->sshConnection->putString(
            self::ROOT_FS_MOUNTPOINT . self::MOD_PROBE_ZFS_CONF_FILE_PATH,
            <<<EOF
options zfs l2arc_noprefetch=0
options zfs l2arc_write_max=134217728
options zfs l2arc_write_boost=134217728

EOF
        );
    }

    private function getAptSourcePveEnterpriseFilePath(): string
    {
        return self::ROOT_FS_MOUNTPOINT . self::APT_SOURCE_LIST_DIR_PATH . '/' .self::PVE_ENTERPRISE_APT_SOURCE_FILE_NAME;
    }

    private function getAptSourcePveNoSubscriptionFilePath(): string
    {
        return self::ROOT_FS_MOUNTPOINT . self::APT_SOURCE_LIST_DIR_PATH . '/' . self::PVE_NO_SUBSCRIPTION_APT_SOURCE_FILE_NAME;
    }

    private function getFirstPartOfHostname(): string
    {
        return explode('.', $this->hostname)[0];
    }

    /**
     * @return void
     * @throws \Wpstudio\Mms\Classes\MmsException
     */
    private function setHostname(): void
    {
        /**
         * Set hosts content
         */
        $hostsContent = $this->sshConnection->getString(
            self::ROOT_FS_MOUNTPOINT . self::HOSTS_FILE_PATH
        );

        FileContentHelper::replaceLine($hostsContent, 2, sprintf(
            '%s %s %s',
            $this->ip,
            $this->hostname,
            $this->getFirstPartOfHostname()
        ));

        $this->sshConnection->putString(
            self::ROOT_FS_MOUNTPOINT . self::HOSTS_FILE_PATH,
            $hostsContent
        );

        /**
         * Set hostname
         */
        $this->sshConnection->putString(
            self::ROOT_FS_MOUNTPOINT . self::HOSTNAME_FILE_PATH,
            $this->hostname
        );

        /**
         * Change myhostname in postfix "main.cf"
         */
        $postfixMainCfContent = $this->sshConnection->getString(
            self::ROOT_FS_MOUNTPOINT . self::POSTFIX_MAIN_CF_FILE_PATH
        );

        $lineNumberOfPostfixMyhostnameOption = FileContentHelper::getLineNumberBySearchQuery($postfixMainCfContent, 'myhostname=');

        FileContentHelper::replaceLine($postfixMainCfContent, $lineNumberOfPostfixMyhostnameOption, sprintf(
            'myhostname=%s',
            $this->hostname
        ));

        $this->sshConnection->putString(
            self::ROOT_FS_MOUNTPOINT . self::POSTFIX_MAIN_CF_FILE_PATH,
            $postfixMainCfContent
        );
    }

    private function replacePveEnterpriseAptSourceToNoSubscription(): void
    {
        /**
         * @example "deb https://enterprise.proxmox.com/debian/pve bullseye pve-enterprise"
         */
        $aptSourcePveEnterprise = $this->sshConnection->getString(
            $this->getAptSourcePveEnterpriseFilePath()
        );

        $aptSourcePveNoSubscription = str_replace('pve-enterprise', 'pve-no-subscription', $aptSourcePveEnterprise);
        $aptSourcePveNoSubscription = str_replace('https', 'http', $aptSourcePveNoSubscription);
        $aptSourcePveNoSubscription = str_replace('enterprise', 'download', $aptSourcePveNoSubscription);

        $this->sshConnection->run(sprintf(
            'mv %s %s',
            $this->getAptSourcePveEnterpriseFilePath(),
            $this->getAptSourcePveNoSubscriptionFilePath(),
        ));

        $this->sshConnection->putString(
            $this->getAptSourcePveNoSubscriptionFilePath(),
            $aptSourcePveNoSubscription
        );
    }

    private function disableSshPasswordAuthentication(): void
    {
        $this->sshConnection->run(sprintf(
            'sed -i "s/#PasswordAuthentication.*/PasswordAuthentication no/" %s',
            self::ROOT_FS_MOUNTPOINT . self::SSHD_CONFIG_FILE_PATH
        ));
    }

    private function getSshAuthorizedKeysFilePath(): string
    {
        return self::ROOT_FS_MOUNTPOINT . self::SSH_DIR_PATH . '/' . self::SSH_AUTHORIZED_KEYS_FILE_NAME;
    }

    private function addingNeedfulSshKeys(): void
    {
        $mmsPublicKey = trim(SshHelper::getPublicKey());

        $masterSysadminsSshKeys = collect(Models\Sysadmin::getSysadminByNickname(Models\Sysadmin::NICKNAME_DIMTI)->ssh_keys)
            ->pluck('pubkey')->map(fn(string $pubkey) => trim($pubkey));

        $sshKeys = $masterSysadminsSshKeys->add($mmsPublicKey)->implode(PHP_EOL);

        $this->sshConnection->run([
            sprintf(
                'mkdir %s',
                self::ROOT_FS_MOUNTPOINT . self::SSH_DIR_PATH
            ),
            sprintf(
                'chmod %s %s',
                self::SSH_DIR_MODE,
                self::ROOT_FS_MOUNTPOINT . self::SSH_DIR_PATH
            ),
        ]);

        $this->sshConnection->run([
            sprintf(
                'touch %s',
                $this->getSshAuthorizedKeysFilePath()
            ),
            sprintf(
                'chmod %s %s',
                self::SSH_AUTHORIZED_KEYS_FILE_MODE,
                $this->getSshAuthorizedKeysFilePath()
            ),
        ]);

        $this->sshConnection->putString(
            $this->getSshAuthorizedKeysFilePath(),
            $sshKeys . PHP_EOL
        );
    }

    private function createClusterAndServerModels(): void
    {
        $cluster = new Models\Cluster();

        $cluster->hostname = $this->hostname;

        $cluster->save();

        $cluster->reload();

        $cluster->sysadmins()->attach(
            Models\Sysadmin::getSysadminByNickname(Models\Sysadmin::NICKNAME_DIMTI)
        );

        $server = new Models\Server();

        $server->code = $this->getFirstPartOfHostname();
        $server->hostname = $this->hostname;
        $server->main_ip_address = $this->ip;
        $server->server_type_id = Models\ServerType::getProxmoxServerType()->id;
        $server->cluster_id = $cluster->id;

        $server->save();

        $server->reload();
    }
}
