<?php namespace Wpstudio\Mms\Classes;

use Collective\Remote\Connection;
use EloquentEncryption;
use Illuminate\Support\Collection;
use Proxmox\Api\Nodes\Node;
use SSH;
use Wpstudio\Mms\Classes\Cluster\ClusterNodeStatus;
use Wpstudio\Mms\Classes\Pve\LxcConfig;
use Wpstudio\Mms\Classes\Pve\LxcStatus;
use Wpstudio\Mms\Classes\Nginx\NginxMasterProxy;
use Wpstudio\Mms\Classes\Pve\NodeStatus;
use Wpstudio\Mms\Classes\Pve\ReplicationStatus;
use Wpstudio\Mms\Models;

class ProxmoxServer
{
    public Models\Server $server;

    public ProxmoxCluster $proxmoxCluster;

    public Node $node;

    public NodeStatus $nodeStatus;

    public Connection $sshConnection;

    public Cli $cli;

    /**
     * @var Collection | LxcStatus[]
     */
    public Collection $lxcStatuses;

    /**
     * @var Collection | Node\Lxc\VmId[] | Node\Qemu\VmId
     */
    public Collection $vmId;

    /**
     * @var Collection | LxcConfig[]
     */
    public Collection $lxcConfigs;

    /**
     * @var Collection | LinuxContainer[]
     */
    public Collection $linuxContainers;

    /**
     * @var Collection | ReplicationStatus[][]
     */
    private Collection $replication;

    public function __construct(Models\Server $server, ?ProxmoxCluster $proxmoxCluster = null, ?Node $node = null, ?NodeStatus $nodeStatus = null)
    {
        $this->server = $server;

        $this->prepareProxmoxCluster($proxmoxCluster);

        $this->prepareNode($node);

        $this->prepareNodeStatus($nodeStatus);

        $this->prepareSshConnection();
    }

    private function prepareProxmoxCluster(?ProxmoxCluster $proxmoxCluster = null): void
    {
        if ($proxmoxCluster) {
            $this->setProxmoxCluster($proxmoxCluster);
        } else {
            $this->setProxmoxCluster(new ProxmoxCluster($this->server->cluster));
        }
    }

    private function prepareNode(?Node $node = null): void
    {
        if ($node) {
            $this->setNode($node);
        } else {
            $this->setNode($this->proxmoxCluster->api->nodes()->node($this->server->code));
        }
    }

    private function prepareNodeStatus(?NodeStatus $nodeStatus = null): void
    {
        if ($nodeStatus) {
            $this->setNodeStatus($nodeStatus);

            $nodeStatus->setProxmoxServer($this);
        } else {
            $this->setNodeStatus(
                new NodeStatus(
                    collect($this->node->getPve()->nodes()->get()['data'])->keyBy('node')->get($this->server->code),
                    $this
                )
            );
        }
    }

    public function getReplication()
    {
        if (!isset($this->replication)) {
            $this->replication = collect($this->node->replication()->get()['data'])
                ->map(fn(array $replicationStatusItem) => new ReplicationStatus($replicationStatusItem, $this->node))
                ->groupBy('guest');
        }

        return $this->replication;
    }

    private function prepareSshConnection(): void
    {
        $this->sshConnection =  SSH::connect([
            'host' => $this->server->main_ip_address,
            'username' => 'root',
            'password' => '',
            'key' => '',
            'keytext' => EloquentEncryption::getKey(),
            'keyphrase' => '',
            'agent' => '',
            'timeout' => 10,
        ]);

        $this->cli = new Cli($this->sshConnection);
    }

    public function prepareLxcStatues(): void
    {
        $this->lxcStatuses = collect();

        $filteringOnlyLxcWithoutTemplates = function (array $lxcStatusItem): bool {
            if (array_key_exists('template', $lxcStatusItem) && $lxcStatusItem['template'] == 1) {
                return false;
            }

            return true;
        };

        collect($this->node->lxc()->get()['data'])->filter($filteringOnlyLxcWithoutTemplates)->each(
            fn(array $lxcItem) => $this->lxcStatuses->offsetSet(
                $lxcItem['vmid'],
                new LxcStatus($lxcItem)
            )
        );
    }

    /**
     * @return void
     * TODO: Adding support $this->node->qemu()
     */
    public function prepareVmIds(): void
    {
        $this->vmId = collect();

        collect($this->lxcStatuses)->each(
            fn(LxcStatus $lxcStatus) => $this->vmId->offsetSet(
                $lxcStatus->vmid,
                $this->node->lxc()->vmId($lxcStatus->vmid)
            )
        );
    }

    public function prepareLxcConfigs(): void
    {
        $this->lxcConfigs = collect();

        collect($this->vmId)->each(
            fn(Node\Lxc\VmId $vmId, $containerCode) => $this->lxcConfigs->offsetSet(
                $containerCode,
                new LxcConfig($vmId->config()->get()['data'])
            )
        );
    }

    public function getVmId(int $vmId): Node\Lxc\VmId
    {
        if (!$this->vmId->offsetExists($vmId)) {
            $this->vmId->offsetSet(
                $vmId,
                $this->node->lxc()->vmId($vmId)
            );
        }

        return $this->vmId->get($vmId);
    }

    /**
     * @return void
     * @throws Exceptions\MmsException
     */
    public function prepareLinuxContainers(): void
    {
        $this->linuxContainers = collect();

        $this->server->containers->each(
            function (Models\Container $container) {
                try {
                    $linuxContainer = new LinuxContainer(
                        $container,
                        $this,
                        $this->getVmId($container->code),
                        $this->lxcStatuses->get($container->code),
                        $this->lxcConfigs->get($container->code),
                        $this->getReplication()->has($container->code) ? $this->getReplication()->get($container->code) : collect()
                    );
                } catch (Exceptions\MmsLxcNotFoundException $e) {
                    logger()->warning($e->getMessage(), $e->getTrace());

                    return;
                }

                $this->linuxContainers->offsetSet(
                    $container->code,
                    $linuxContainer
                );
            }
        );
    }

    /**
     * @param ProxmoxCluster $proxmoxCluster
     */
    public function setProxmoxCluster(ProxmoxCluster $proxmoxCluster): void
    {
        $this->proxmoxCluster = $proxmoxCluster;
    }

    /**
     * @param Node $node
     */
    public function setNode(Node $node): void
    {
        $this->node = $node;
    }

    /**
     * @param NodeStatus $nodeStatus
     */
    public function setNodeStatus(NodeStatus $nodeStatus): void
    {
        $this->nodeStatus = $nodeStatus;
    }

    public function updateStatus(): void
    {
        $clusterNodeStatus = $this->proxmoxCluster->clusterNodesStatuses->get($this->server->code);

        assert($clusterNodeStatus instanceof ClusterNodeStatus);

        $this->server->cluster_node_status = $clusterNodeStatus->toArray();

        $this->server->node_status = $this->nodeStatus->toArray();

        $this->server->save();
    }

    /**
     * @return NginxMasterProxy
     * @throws Exceptions\MmsException
     */
    public function getMasterNginxProxy(): NginxMasterProxy
    {
        $this->server->loadMissing(['containers']);

        $masterNginxProxyDestinationRole = Models\DestinationRole::getMasterNginxProxyDestinationRole();

        $containerWithMasterNginxProxyDestinationRole = $this->server->containers->where('destination_role_id', '=', $masterNginxProxyDestinationRole->id)->first();

        if (!$containerWithMasterNginxProxyDestinationRole) {
            throw new Exceptions\MmsNginxException(sprintf(
                'Not found master nginx proxy container on the server %s (ID: %d)',
                $this->server->code,
                $this->server->id
            ));
        }

        $linuxContainer = new LinuxContainer($containerWithMasterNginxProxyDestinationRole, $this);

        return new NginxMasterProxy($linuxContainer);
    }
}
