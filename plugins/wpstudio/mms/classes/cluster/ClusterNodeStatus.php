<?php namespace Wpstudio\Mms\Classes\Cluster;

use Wpstudio\Mms\Classes\ProxmoxCluster;
use Wpstudio\Mms\Classes\Traits\PveAttributes;

class ClusterNodeStatus
{
    use PveAttributes;

    public ProxmoxCluster $proxmoxCluster;

    /**
     * @var int
     * @example 1
     */
    public int $online;

    /**
     * @var string
     * @example 90.80.70.210
     */
    public string $ip;

    public string $level;

    /**
     * @var string
     * @example node/dedic106
     */
    public string $id;

    /**
     * @var int
     * @example 1
     */
    public int $nodeid;

    /**
     * @var int
     * @example 1
     */
    public int $local;

    /**
     * @var string
     * @example dedic106
     */
    public string $name;

    /**
     * @var string
     * @example node
     */
    public string $type;

    /**
     * @var array
     * @desc Attributes not existing in current public scalar properties
     */
    public array $more = [];

    public function __construct(array $data, ProxmoxCluster $proxmoxCluster)
    {
        $this->proxmoxCluster = $proxmoxCluster;

        $this->prepareDataAttributes($data);
    }
}
