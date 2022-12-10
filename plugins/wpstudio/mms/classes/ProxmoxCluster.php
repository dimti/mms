<?php namespace Wpstudio\Mms\Classes;

use Config;
use Illuminate\Support\Collection;
use Proxmox\Api\Cluster;
use Proxmox\Api\Nodes\Node;
use Proxmox\PVE;
use Wpstudio\Mms\Classes\Cluster\ClusterNodeStatus;
use Wpstudio\Mms\Classes\Cluster\ClusterStatus;
use Wpstudio\Mms\Classes\Pve\NodeStatus;
use Wpstudio\Mms\Models;

class ProxmoxCluster
{
    public PVE $api;

    public Models\Cluster $cluster;

    public Cluster $pveCluster;

    public ClusterStatus $clusterStatus;

    /**
     * @var Collection | ClusterNodeStatus[]
     */
    public Collection $clusterNodesStatuses;

    /**
     * @var Collection|ProxmoxServer[]
     */
    public Collection $proxmoxServers;

    /**
     * @var Collection|NodeStatus[]
     */
    public Collection $nodesStatuses;

    /**
     * @var Collection|Node[]
     */
    public Collection $nodes;

    public function __construct(Models\Cluster $cluster)
    {
        $this->cluster = $cluster;

        $this->api = new PVE($this->cluster->hostname, $this->cluster->username, $this->cluster->password, $this->cluster->port, $this->cluster->auth_type, Config::get('app.debug'));

        $this->pveCluster = $this->api->cluster();

        $clusterStatusData = $this->pveCluster->status()->get()['data'];

        $this->clusterStatus = new ClusterStatus($clusterStatusData[0], $this);

        $this->clusterNodesStatuses = collect();

        collect($clusterStatusData)->slice(1)->each(
            fn(array $clusterNodeStatusItem) => $this->clusterNodesStatuses->offsetSet(
                $clusterNodeStatusItem['name'],
                new ClusterNodeStatus($clusterNodeStatusItem, $this)
            )
        );
    }

    public function prepareNodesStatuses(): void
    {
        $this->nodesStatuses = collect();

        collect($this->api->nodes()->get()['data'])->each(
            fn(array $nodeStatusItem) => $this->nodesStatuses->offsetSet(
                $nodeStatusItem['node'],
                new NodeStatus($nodeStatusItem)
            )
        );
    }

    public function prepareNodes(): void
    {
        $this->nodes = collect();

        collect($this->nodesStatuses)->each(
            fn(NodeStatus $nodeStatus) => $this->nodes->offsetSet(
                $nodeStatus->node,
                $this->api->nodes()->node($nodeStatus->node)
            )
        );
    }

    public function getNode(string $serverCode): Node
    {
        if (!$this->nodes->offsetExists($serverCode)) {
            $this->nodes->offsetSet(
                $serverCode,
                $this->api->nodes()->node($serverCode)
            );
        }

        return $this->nodes->get($serverCode);
    }

    public function prepareProxmoxServers(): void
    {
        $this->proxmoxServers = collect();

        $this->cluster->servers->each(
            fn(Models\Server $server) => $this->proxmoxServers->offsetSet(
                $server->code,
                new ProxmoxServer(
                    $server,
                    $this,
                    $this->getNode($server->code),
                    $this->nodesStatuses->get($server->code)
                )
            )
        );
    }

    public function updateStatus(): void
    {
        $this->cluster->cluster_status = $this->clusterStatus->toArray();

        $this->cluster->save();
    }
}
