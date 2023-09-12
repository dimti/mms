<?php namespace Wpstudio\Mms\Controllers\Rescue\Handlers;

use Collective\Remote\Connection;
use Winter\Storm\Exception\ValidationException;
use Winter\Storm\Extension\ExtensionBase;
use Winter\Storm\Support\Facades\Http;
use Wpstudio\Mms\Classes\Cli;
use Wpstudio\Mms\Classes\Exceptions\MmsCliException;
use Wpstudio\Mms\Classes\Exceptions\MmsCliFileNotFoundException;
use Wpstudio\Mms\Classes\Helpers\FileContentHelper;
use Wpstudio\Mms\Classes\Helpers\SshHelper;
use Validator;

class SetupHetznerProxmoxServer extends ExtensionBase
{
    const PROXMOX_DOWNLOAD_URL = 'http://download.proxmox.com/iso/';
    const ISO_PROXMOX_VE_FILE_NAME_PREFIX = 'proxmox-ve_';

    private Connection $sshConnection;
    private Cli $cli;

    public string $ip;
    public string $password;
    public ?string $isoPath;
    public int $proxmoxMajorVersion;

    private int $proxmoxMinorVersion;

    private array $rulesInput = [
        'ip' => 'required|ip',
        'password' => 'required',
        'isoPath' => [
            'regex:#^(/)?([^/\0]+(/)?)+$#'
        ],
        'proxmoxMajorVersion' => 'required|integer|in:6,7',
    ];

    private array $rulesPrepared = [
        'proxmoxMinorVersion' => 'required|integer',
    ];

    /**
     * @return array
     * @throws ValidationException
     */
    public function onSetupHetznerProxmoxServer(): array
    {
        $result = [];

        $this->prepareInputVars();

        $this->validateInput();

        $this->prepareMinorProxmoxVersion();

        $this->validatePrepared();

        $this->prepareSshConnection();

        $this->downloadIsoAndStartQemu();

        $result['what_then'] = sprintf(
            'Turn on port forwarding. Open Terminal and execute "%s". And after that open TigerVNC and connect ot localhost',
            sprintf(
                'ssh root@%s -L 9501:127.0.0.1:5901',
                $this->ip
            )
        );

        return $result;
    }

    private function prepareSshConnection(): void
    {
        $this->sshConnection = SshHelper::getConnection($this->ip, $this->password);

        $this->cli = new Cli($this->sshConnection);
    }

    private function prepareInputVars(): void
    {
        $this->ip = input('ip');
        $this->password = input('password');
        $this->isoPath = input('isoPath');
        $this->proxmoxMajorVersion = input('proxmoxMajorVersion');
    }

    private function prepareMinorProxmoxVersion(): void
    {
        $proxmoxMajorVersionSearchQuery = sprintf(
            '%s%d',
            self::ISO_PROXMOX_VE_FILE_NAME_PREFIX,
            $this->proxmoxMajorVersion,
        );

        /**
         * @example <a href="proxmox-ve_7.3-1.iso">proxmox-ve_7.3-1.iso</a>                               22-Nov-2022 10:22          1108862976
         */
        $proxmoxVersionLine = FileContentHelper::getLineBySearchQuery(
            Http::get(self::PROXMOX_DOWNLOAD_URL)->body,
            $proxmoxMajorVersionSearchQuery
        );

        $this->proxmoxMinorVersion = substr(
            $proxmoxVersionLine,
            strpos($proxmoxVersionLine, $proxmoxMajorVersionSearchQuery) + strlen($proxmoxMajorVersionSearchQuery) + 1,
            1
        );
    }

    /**
     * @return void
     * @throws ValidationException
     */
    private function validateInput(): void
    {
        $validator = Validator::make(
            [
                'ip' => $this->ip,
                'password' => $this->password,
                'isoPath' => $this->isoPath,
                'proxmoxMajorVersion' => $this->proxmoxMajorVersion,
            ],
            $this->rulesInput,
        );

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    /**
     * @return void
     * @throws ValidationException
     */
    private function validatePrepared(): void
    {
        $validator = Validator::make(
            [
                'proxmoxMinorVersion' => $this->proxmoxMinorVersion,
            ],
            $this->rulesPrepared,
        );

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    private function getProxmoxIsoFileName(): string
    {
        /**
         * @example proxmox-ve_7.3-1.iso
         */
        return sprintf(
            '%s%d.%d-1.iso',
            self::ISO_PROXMOX_VE_FILE_NAME_PREFIX,
            $this->proxmoxMajorVersion,
            $this->proxmoxMinorVersion
        );
    }

    private function getActualIsoPathForInstall(): string
    {
        if ($this->hasIsoPath()) {
            return $this->getIsoPath();
        } else {
            return sprintf(
                './%s',
                $this->getProxmoxIsoFileName()
            );
        }
    }

    /**
     * @return void
     * @throws MmsCliException
     * @throws MmsCliFileNotFoundException
     */
    private function downloadIsoAndStartQemu(): void
    {
        if ($this->hasIsoPath()) {
            $this->cli->checkExistsFile($this->getIsoPath());
        } else {
            $this->cli->run([
                sprintf(
                    'wget %s%s',
                    self::PROXMOX_DOWNLOAD_URL,
                    $this->getProxmoxIsoFileName()
                ),
            ]);
        }

        $this->cli->run([
            sprintf(
                'qemu-system-x86_64 -enable-kvm -smp 4 -m 4096 -boot d -cdrom %s -drive file=/dev/nvme0n1,format=raw,media=disk,if=virtio -drive file=/dev/nvme1n1,format=raw,media=disk,if=virtio -vnc 127.0.0.1:1 &',
                $this->getActualIsoPathForInstall()
            )
        ]);
    }

    public function hasIsoPath(): bool
    {
        return !is_null($this->isoPath) && $this->isoPath;
    }

    /**
     * @return string|null
     */
    public function getIsoPath(): ?string
    {
        return $this->isoPath;
    }
}
