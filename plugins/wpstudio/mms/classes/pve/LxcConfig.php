<?php namespace Wpstudio\Mms\Classes\Pve;

use Illuminate\Support\Collection;
use Wpstudio\Mms\Classes\LinuxContainer;
use Wpstudio\Mms\Classes\ProxmoxServer;
use Wpstudio\Mms\Classes\Traits\PveAttributes;
use Wpstudio\Mms\Models\NetworkType;

/**
 * @see https://pve.proxmox.com/pve-docs/api-viewer/index.html#/nodes/%7Bnode%7D/lxc/%7Bvmid%7D/config
 */
class LxcConfig
{
    use PveAttributes;

    public ?LinuxContainer $linuxContainer;

    /**
     * @var string
     * @example amd
     */
    public string $arch;

    /**
     * @var int
     * @example 2
     */
    public int $cores;

    /**
     * @var string
     * @example deafa26c0f55ed762137ff6092dad6d42d09e99e
     */
    public string $digest;

    /**
     * @var string
     * @example nginx
     */
    public string $hostname;

    /**
     * @var int
     * @desc Size of RAM in megabytes
     * @example 1000
     */
    public int $memory;

    /**
     * @var string
     * @example 172.16.30.100
     */
    public string $nameserver;

    /**
     * @var string
     * @desc Network configuration
     * @example name=eth0,bridge=vmbr1,hwaddr=D6:FD:70:13:E6:6E,ip=dhcp,ip6=dhcp,type=veth
     */
    public string $net0;

    /**
     * @var int
     * @example 1
     */
    public int $onboot;

    /**
     * @var string
     * @example archlinux
     */
    public string $ostype;

    /**
     * @var string
     * @desc Parent ZFS snapshot name
     */
    public string $parent;

    /**
     * @var string
     * @example ssd:subvol-161-disk-0,size=12G
     */
    public string $rootfs;

    /**
     * @var int
     * @example 1614419681
     */
    public int $snaptime;

    /**
     * @var string
     * @example order=3
     */
    public string $startup;

    /**
     * @var int
     * @desc Size of swap in megabytes
     * @example 1000
     */
    public int $swap;

    /**
     * @var int
     * @example 1
     */
    public int $unprivileged;

    /**
     * @var string
     */
    public string $lock;

    private Collection $mountpoints;

    /**
     * @var int
     * @example 1
     * @see ProxmoxServer::prepareLxcStatues LXC templates is ignored
     */
    public int $template;

    /**
     * @var string
     * @example "clone\n"
     */
    public string $description;

    /**
     * @var array
     * @desc Attributes not existing in current public scalar properties
     */
    public array $more = [];

    public function __construct(array $data, ?LinuxContainer $linuxContainer = null)
    {
        if ($linuxContainer) {
            $this->setLinuxContainer($linuxContainer);
        }

        $this->prepareDataAttributes($data, ['digest']);

        $mpFoundedKeys = [];

        foreach ($this->more as $key => $value) {
            if (starts_with($key, 'mp')) {
                $mpFoundedKeys[] = $key;

                if (!isset($this->mountpoints)) {
                    $this->mountpoints = collect();
                }

                $this->mountpoints->add($value);
            }
        }

        foreach ($mpFoundedKeys as $mpFoundedKey) {
            unset($this->more[$mpFoundedKey]);
        }
    }

    public function hasLinuxContainer(): bool
    {
        return isset($this->linuxContainer);
    }

    public function setLinuxContainer(LinuxContainer $linuxContainer): void
    {
        $this->linuxContainer = $linuxContainer;
    }

    public function getNetworkType(): NetworkType
    {
        $netConfigParts = explode(',', $this->net0);

        $name = explode('=', $netConfigParts[0])[1];

        $bridge = explode('=', $netConfigParts[1])[1];

        $networkTypeCode = NetworkType::CODE_DIRECT;

        if ($bridge != 'vmbr0') {
            $networkTypeCode = NetworkType::CODE_INNER;
        }

        return NetworkType::whereCode($networkTypeCode)->firstOrFail();
    }

    public function hasMountpoints(): bool
    {
        return isset($this->mountpoints) && $this->mountpoints->count();
    }

    public function getMountpoints(): Collection
    {
        return $this->mountpoints;
    }

    public function getRootFsPath(): string
    {
        list($disk, $size) = explode(',', $this->rootfs);

        list($storageCode, $diskName) = explode(':', $disk);

        return sprintf('/%s/%s', $storageCode, $diskName);
    }
}
