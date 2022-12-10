<?php namespace Wpstudio\Mms\Classes\Pve;

use Wpstudio\Mms\Classes\Enums;
use Wpstudio\Mms\Classes\ProxmoxCluster;
use Wpstudio\Mms\Classes\Traits\PveAttributes;

/**
 * @see UpIdStatus
 */
class ClusterTask
{
    use PveAttributes;

    public ProxmoxCluster $proxmoxCluster;

    /**
     * @var int|null
     * @example 1670311076
     */
    public int|null $endtime;

    /**
     * @var string
     * @desc Maybe id of container
     * @example "206"
     * @example ""
     */
    public string $id;

    /**
     * @var string
     * @example "dedic100"
     */
    public string $node;

    /**
     * @var int
     * @example 25631
     */
    public int $pid;

    /**
     * @var Enums\ClusterTask\TaskSaved
     * @desc If task running - saved has "0" value. If task completed - then "1"
     * @example "1"
     */
    public Enums\ClusterTask\TaskSaved $saved;

    /**
     * @var int
     * @example 1670311073
     */
    public int $starttime;

    /**
     * @var Enums\ClusterTask\TaskStatus|string|null
     * @example "OK"
     * @example "command 'apt-get update' failed: exit code 100"
     */
    public Enums\ClusterTask\TaskStatus|string|null $status;

    /**
     * @var Enums\UpIdStatus\TaskType|string
     * @example "aptupdate"
     * @example "vzstart"
     */
    public Enums\UpIdStatus\TaskType|string $type;

    /**
     * @var string
     * @example "UPID:dedic100:00000DA4:269DCF9D:638EECA1:vzstart:206:root@pam:"
     */
    public string $upid;

    /**
     * @var string "root@pam"
     */
    public string $user;

    /**
     * @var array
     * @desc Attributes not existing in current public scalar properties
     */
    public array $more = [];

    protected array $enums = [
        'saved' => Enums\ClusterTask\TaskSaved::class,
        'status' => Enums\ClusterTask\TaskStatus::class,
        'type' => Enums\UpIdStatus\TaskType::class,
    ];

    public function __construct(array $data, ?ProxmoxCluster $proxmoxCluster = null)
    {
        if ($proxmoxCluster) {
            $this->setProxmoxCluster($proxmoxCluster);
        }

        $this->prepareDataAttributes($data);
    }

    public function hasProxmoxCluster(): bool
    {
        return isset($this->proxmoxCluster);
    }

    public function setProxmoxCluster(ProxmoxCluster $proxmoxCluster): void
    {
        $this->proxmoxCluster = $proxmoxCluster;
    }
}
