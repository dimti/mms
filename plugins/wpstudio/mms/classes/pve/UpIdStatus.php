<?php namespace Wpstudio\Mms\Classes\Pve;

use Wpstudio\Mms\Classes\Enums;
use Wpstudio\Mms\Classes\ProxmoxServer;
use Wpstudio\Mms\Classes\Traits\PveAttributes;

/**
 * @see ClusterTask
 */
class UpIdStatus
{
    use PveAttributes;

    public ProxmoxServer $proxmoxServer;

    /**
     * @var string
     * @desc From PVE API returned as string
     * @desc Presents container ID or empty string
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
     * @var int
     * @example 647582543
     */
    public int $pstart;

    /**
     * @var int
     * @example 1670308133
     */
    public int $starttime;

    public Enums\UpIdStatus\TaskStatus $status;

    public Enums\UpIdStatus\TaskType|string $type;

    /**
     * @var string
     * @example "UPID:dedic100:0000641F:2699534F:638EE125:vzmigrate:206:root@pam:"
     */
    public string $upid;

    /**
     * @var string
     * @example "root@pam"
     */
    public string $user;

    public Enums\UpIdStatus\ExitStatus|string|null $exitstatus;

    /**
     * @var array
     * @desc Attributes not existing in current public scalar properties
     */
    public array $more = [];

    protected array $enums = [
        'status' => Enums\UpIdStatus\TaskStatus::class,
        'type' => Enums\UpIdStatus\TaskType::class,
        'exitstatus' => Enums\UpIdStatus\ExitStatus::class,
    ];

    public function __construct(array $data, ?ProxmoxServer $proxmoxServer = null)
    {
        if ($proxmoxServer) {
            $this->setProxmoxServer($proxmoxServer);
        }

        $this->prepareDataAttributes($data);
    }

    public function hasProxmoxServer(): bool
    {
        return isset($this->proxmoxServer);
    }

    public function setProxmoxServer(ProxmoxServer $proxmoxServer): void
    {
        $this->proxmoxServer = $proxmoxServer;
    }
}
