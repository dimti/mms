<?php namespace Wpstudio\Mms\Classes\Pve;

use Illuminate\Contracts\Support\Arrayable;
use Proxmox\Api\Nodes\Node;
use Wpstudio\Mms\Classes\LinuxContainer;
use Wpstudio\Mms\Classes\Traits\PveAttributes;

class ReplicationStatus implements Arrayable
{
    use PveAttributes;

    public ?LinuxContainer $linuxContainer;

    /**
     * @var float
     * @example 14.35431
     */
    public float $duration;

    /**
     * @var int
     * @example 0
     */
    public int $fail_count;

    /**
     * @var int
     * @desc Container code. In PVE API presented as string.
     * @example "211"
     */
    public int $guest;

    /**
     * @var string
     * @desc Replication identifier
     * @example "211-0"
     */
    public string $id;

    /**
     * @var int
     * @desc in PVE API presented as string
     * @example "0"
     */
    public int $jobnum;

    /**
     * @var int
     * @desc timestamp
     * @example 1670163300
     */
    public int $last_sync;

    /**
     * @var int
     * @desc timestamp
     * @example 1670163300
     */
    public int $last_try;

    /**
     * @var int
     * @desc timestamp
     * @example 1670164200
     */
    public int $next_sync;

    /**
     * @var string
     * @example "dedic106"
     */
    public string $source;

    /**
     * @var string
     * @example "dedic100"
     */
    public string $target;

    /**
     * @var string
     * @example "local"
     */
    public string $type;

    /**
     * @var string
     * @example "lxc"
     */
    public string $vmtype;

    /**
     * @var array
     * @desc Attributes not existing in current public scalar properties
     */
    public array $more = [];

    public function __construct(array $data, Node $node, ?LinuxContainer $linuxContainer = null)
    {
        if ($linuxContainer) {
            $this->setLinuxContainer($linuxContainer);
        }

        $this->prepareDataAttributes($data);
    }

    public function hasLinuxContainer(): bool
    {
        return isset($this->linuxContainer);
    }

    public function setLinuxContainer(LinuxContainer $linuxContainer): void
    {
        $this->linuxContainer = $linuxContainer;
    }
}
