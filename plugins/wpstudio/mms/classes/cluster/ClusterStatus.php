<?php namespace Wpstudio\Mms\Classes\Cluster;

use Wpstudio\Mms\Classes\ProxmoxCluster;
use Wpstudio\Mms\Classes\Traits\PveAttributes;

class ClusterStatus
{
    use PveAttributes;

    public ProxmoxCluster $proxmoxCluster;

    /**
     * @var int
     * @example 1
     */
    public int $quorate;

    /**
     * @var string
     * @example cluster
     */
    public string $id;

    /**
     * @var string
     * @example dedic106
     */
    public string $name;

    /**
     * @var int
     * @example 2
     */
    public int $version;

    /**
     * @var string
     * @example cluster
     */
    public string $type;

    /**
     * @var int
     * @example 2
     */
    public int $nodes;

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
