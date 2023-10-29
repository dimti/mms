<?php namespace Wpstudio\Mms\Classes;

use Illuminate\Support\Collection;
use Proxmox\Api\Nodes\Node\Lxc\VmId;
use Wpstudio\Mms\Classes\Pve\LxcConfig;
use Wpstudio\Mms\Classes\Pve\LxcStatus;
use Wpstudio\Mms\Classes\Pve\ReplicationStatus;
use Wpstudio\Mms\Controllers\Clusters\Handlers\UpdateContainers;
use Wpstudio\Mms\Models;

class LinuxContainer
{
    public Models\Container $container;

    public ProxmoxServer $proxmoxServer;

    public ?VmId $vmId;

    public LxcStatus $lxcStatus;

    public LxcConfig $lxcConfig;

    /**
     * @var Collection | ReplicationStatus[]
     */
    public Collection $replication;

    /**
     * @param Models\Container $container
     * @param ProxmoxServer|null $proxmoxServer
     * @param VmId|null $vmId
     * @param LxcStatus|null $lxcStatus
     * @param LxcConfig|null $lxcConfig
     * @param Collection|ReplicationStatus[]|null $replication
     * @throws Exceptions\MmsException
     */
    public function __construct(Models\Container $container, ?ProxmoxServer $proxmoxServer = null, ?VmId $vmId = null, ?LxcStatus $lxcStatus = null, ?LxcConfig $lxcConfig = null, ?Collection $replication = null)
    {
        $this->container = $container;

        $this->prepareProxmoxServer($proxmoxServer);

        $this->prepareVmId($vmId);

        $this->prepareLxcStatus($lxcStatus);

        $this->prepareLxcConfig($lxcConfig);

        $this->prepareReplication($replication);
    }

    /**
     * @param ProxmoxServer $proxmoxServer
     */
    public function setProxmoxServer(ProxmoxServer $proxmoxServer): void
    {
        $this->proxmoxServer = $proxmoxServer;
    }

    /**
     * @param VmId|null $vmId
     */
    public function setVmId(?VmId $vmId): void
    {
        $this->vmId = $vmId;
    }

    private function prepareProxmoxServer(?ProxmoxServer $proxmoxServer = null): void
    {
        if ($proxmoxServer) {
            $this->setProxmoxServer($proxmoxServer);
        } else {
            $this->setProxmoxServer(new ProxmoxServer($this->container->server));
        }
    }

    private function prepareVmId(?VmId $vmId = null): void
    {
        if ($vmId) {
            $this->setVmId($vmId);
        } else {
            $this->setVmId($this->proxmoxServer->node->lxc()->vmId($this->container->code));
        }
    }

    /**
     * @param LxcStatus|null $lxcStatus
     * @return void
     * @throws Exceptions\MmsException
     */
    private function prepareLxcStatus(?LxcStatus $lxcStatus = null): void
    {
        if ($lxcStatus) {
            $this->setLxcStatus($lxcStatus);

            $lxcStatus->setLinuxContainer($this);
        } else {
            $vmId = $this->vmId->status()->current()->get();

            if (!$vmId) {
                throw new Exceptions\MmsLxcNotFoundException(sprintf(
                    'LXC container %d not existing on server node %s',
                    $this->container->code,
                    $this->proxmoxServer->server->code
                ));
            }

            $this->setLxcStatus(new LxcStatus($this->vmId->status()->current()->get()['data'], $this));
        }
    }

    private function prepareLxcConfig(?LxcConfig $lxcConfig = null): void
    {
        if ($lxcConfig) {
            $this->setLxcConfig($lxcConfig);

            $lxcConfig->setLinuxContainer($this);
        } else {
            $this->setLxcConfig(new LxcConfig($this->vmId->config()->get()['data'], $this));
        }
    }

    private function prepareReplication(?Collection $replication = null): void
    {
        if ($replication) {
            $this->setReplication($replication);
        } else {
            if ($this->proxmoxServer->getReplication()->has($this->container->code)) {
                $this->setReplication($this->proxmoxServer->getReplication()->get($this->container->code));
            } else {
                $this->setReplication(collect());
            }
        }

        $this->replication->each(fn(ReplicationStatus $replicationStatus) => $replicationStatus->setLinuxContainer($this));
    }

    public function hasExistsReplicationToDestination(string $targetServerCode): bool
    {
        return !!$this->replication->where('target', '=', $targetServerCode)->count();
    }

    public function getFilePathOnRootFs(string $filePath): string
    {
        return $this->lxcConfig->getRootFsPath() . $filePath;
    }

    public function hasVmId(): bool
    {
        return isset($this->vmId);
    }

    /**
     * @param LxcStatus $lxcStatus
     */
    public function setLxcStatus(LxcStatus $lxcStatus): void
    {
        $this->lxcStatus = $lxcStatus;
    }

    /**
     * @param LxcConfig $lxcConfig
     */
    public function setLxcConfig(LxcConfig $lxcConfig): void
    {
        $this->lxcConfig = $lxcConfig;
    }

    /**
     * @return void
     * @desc On this updated not only status and config, also updated container name and network type if necessary
     */
    public function updateStatusAndConfig(): void
    {
        if ($this->lxcConfig->hasMountpoints()) {
            $this->container->mountpoints = $this->lxcConfig->getMountpoints()->map(fn(string $mp) => ['mp' => $mp]);
        }

        $this->container->lxc_config = $this->lxcConfig->toArray();
        $this->container->lxc_status = $this->lxcStatus->toArray();
        $this->container->replication = $this->replication->toArray();

        if ($this->lxcStatus->name != $this->container->name) {
            $this->container->name = $this->lxcStatus->name;
        }

        /**
         * networkType relation desired defined on prepare cluster
         * @see UpdateContainers::prepareModel
         * Maybe not necessary, because on this use network_type_id, not relation
         */
        if ($this->lxcConfig->getNetworkType()->id != $this->container->network_type_id) {
            $this->container->network_type_id = $this->lxcConfig->getNetworkType()->id;
        }

        $this->container->save();
    }

    /**
     * @param Collection $replication
     */
    public function setReplication(Collection $replication): void
    {
        $this->replication = $replication;
    }
}
