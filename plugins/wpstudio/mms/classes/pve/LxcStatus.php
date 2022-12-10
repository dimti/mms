<?php namespace Wpstudio\Mms\Classes\Pve;

use Wpstudio\Mms\Classes\LinuxContainer;
use Wpstudio\Mms\Classes\Traits\PveAttributes;

class LxcStatus
{
    use PveAttributes;

    public ?LinuxContainer $linuxContainer;

    /**
     * @var int
     * @example "196"
     */
    public int $vmid;

    /**
     * @var string
     * @desc Appropriate hostname option in LxcConfig
     * @see LxcConfig::$hostname
     * @example db-zelenoemore-test
     */
    public string $name;

    /**
     * @var string
     * @example lxc
     */
    public string $type;

    /**
     * @var int
     * @example 31307878
     */
    public int $uptime;

    /**
     * @var string|int
     */
    public string|int $template;

    /**
     * @var int
     * @see LxcConfig::$swap
     * @see self::$swap
     * @example 1048576000
     */
    public int $maxswap;
    /**
     * @var int
     * @desc Current swap value
     * @see self::$maxswap
     * @example 278528
     */
    public int $swap;

    /**
     * @var int
     * @see LxcConfig::$memory
     * @see self::$mem
     * @example 4194304000
     */
    public int $maxmem;

    /**
     * @var int
     * @example 100089856
     */
    public int $mem;

    /**
     * @var int
     * @see LxcConfig::$cores
     */
    public int $cpus;

    /**
     * @var int
     * @desc Option is not presented actual value of vmId list from api
     */
    public int $cpu;

    /**
     * @var string
     * @desc Currently used root disk space in bytes
     */
    public string $disk;

    /**
     * @var string
     * @see LxcConfig::$rootfs
     * @see self::$disk
     * @example "22548578304"
     */
    public string $maxdisk;

    /**
     * @var int
     * @see self::$diskread
     * @example 0
     */
    public int $diskwrite;

    /**
     * @var int
     * @see self::$diskwrite
     * @example 0
     */
    public int $diskread;

    /**
     * @var int
     * @see self::$netout
     * @example 621585952
     */
    public int $netin;

    /**
     * @var int
     * @see self::$netin
     * @example 1464944
     */
    public int $netout;

    /**
     * @var string
     */
    public string $lock;

    /**
     * @var string
     * @example running
     */
    public string $status;

    /**
     * @var int
     * @desc "13708"
     */
    public int $pid;

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

        $this->prepareDataAttributes($data, ['ha']);
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
