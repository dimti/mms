<?php namespace Wpstudio\Mms\Classes\Pve;

use Wpstudio\Mms\Classes\ProxmoxServer;
use Wpstudio\Mms\Classes\Traits\PveAttributes;

class NodeStatus
{
    use PveAttributes;

    public ?ProxmoxServer $proxmoxServer;

    /**
     * @var int
     * @example 5642788
     */
    public int $uptime;

    /**
     * @var string
     * @example dedic100
     */
    public string $node;

    public string $level;

    /**
     * @var string
     * @example online
     */
    public string $status;

    /**
     * @var int
     * @example 101360324608
     */
    public int $maxmem;

    /**
     * @var int
     * @example 84112351232
     */
    public int $mem;

    /**
     * @var int
     * @example 24
     */
    public int $maxcpu;

    /**
     * @var float
     * @example 0.23537154526429
     */
    public float $cpu;

    /**
     * @var string
     * @example 4A:39:21:4B:5B:2C:59:7F:BB:02:7D:5E:56:2C:8D:04:C5:B4:B4:62:94:55:87:DC:B7:42:73:CD:C3:14:20:F7
     */
    public string $ssl_fingerprint;

    /**
     * @var string
     * @example node/dedic100
     */
    public string $id;

    /**
     * @var int
     * @example 99882598400
     */
    public int $maxdisk;

    /**
     * @var int
     * @example 85196673024
     */
    public int $disk;

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
