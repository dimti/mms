<?php namespace Wpstudio\Mms\Classes\Nginx;

use Wpstudio\Mms\Classes\Cli;
use Wpstudio\Mms\Classes\Exceptions\MmsNginxException;
use Wpstudio\Mms\Classes\Helpers\FileContentHelper;

class LetsEncryptSslCert
{
    private NginxSite $nginxSite;

    private Cli $cli;

    /**
     * @desc Actual dir name for storing SSL key and cert symlinks in /etc/letsencrypt/live directory
     */
    private string $sslConfigName;

    public function __construct(NginxSite $nginxSite)
    {
        $this->nginxSite = $nginxSite;

        $this->cli = $this->nginxSite->nginxMasterProxy->linuxContainer->proxmoxServer->cli;
    }

    public function getSslConfigName(): string
    {
        if (!isset($this->sslConfigName)) {
            /**
             * @example ssl_certificate_key /etc/letsencrypt/live/tennisweekend.ru-0001/privkey.pem; # managed by Certbot
             */
            $sslCertNginxConfigLine = $this->nginxSite->nginxConfig->getSslCertConfigLine();

            $this->sslConfigName = explode('/', $sslCertNginxConfigLine)[4];
        }

        return $this->sslConfigName;
    }

    public function getActualRenewalConfigFileContent(): string
    {
        return $this->cli->get($this->getAbsoluteRenewalConfigFilePath());
    }

    /**
     * @return void
     * @throws MmsNginxException
     */
    public function checkExistsLiveDir(): void
    {
        if (!$this->hasExistsLiveDir()) {
            throw new MmsNginxException(sprintf(
                'Letsencrypt live dir on server %s not exists: %s',
                $this->nginxSite->nginxMasterProxy->linuxContainer->proxmoxServer->server->code,
                $this->getAbsoluteLiveDirPath()
            ));
        }
    }

    public function hasExistsLiveDir(): bool
    {
        return $this->cli->hasExistsDir($this->getAbsoluteLiveDirPath());
    }

    public function getAbsoluteLiveDirPath(): string
    {
        return $this->nginxSite->nginxMasterProxy->linuxContainer->getFilePathOnRootFs(
            $this->getLiveDirPath()
        );
    }

    public function getLiveDirPath(): string
    {
        return sprintf(
            '/etc/letsencrypt/live/%s',
            $this->getSslConfigName(),
        );
    }

    /**
     * @return void
     * @throws MmsNginxException
     */
    public function checkExistsArchiveDir(): void
    {
        if (!$this->hasExistsArchiveDir()) {
            throw new MmsNginxException(sprintf(
                'Letsencrypt archive dir on server %s not exists: %s',
                $this->nginxSite->nginxMasterProxy->linuxContainer->proxmoxServer->server->code,
                $this->getAbsoluteArchiveDirPath()
            ));
        }
    }

    public function hasExistsArchiveDir(): bool
    {
        return $this->cli->hasExistsDir($this->getAbsoluteArchiveDirPath());
    }

    public function getAbsoluteArchiveDirPath(): string
    {
        return $this->nginxSite->nginxMasterProxy->linuxContainer->getFilePathOnRootFs(
            $this->getArchiveDirPath()
        );
    }

    public function getArchiveDirPath(): string
    {
        return sprintf(
            '/etc/letsencrypt/archive/%s',
            $this->getSslConfigName(),
        );
    }

    /**
     * @return void
     * @throws MmsNginxException
     */
    public function checkExistsRenewalConfigFile(): void
    {
        if (!$this->hasExistsRenewalConfigFile()) {
            throw new MmsNginxException(sprintf(
                'Letsencrypt renewal config file on server %s not exists: %s',
                $this->nginxSite->nginxMasterProxy->linuxContainer->proxmoxServer->server->code,
                $this->getAbsoluteRenewalConfigFilePath()
            ));
        }
    }

    public function hasExistsRenewalConfigFile(): bool
    {
        return $this->cli->hasExistsFile($this->getAbsoluteRenewalConfigFilePath());
    }

    public function getAbsoluteRenewalConfigFilePath(): string
    {
        return $this->nginxSite->nginxMasterProxy->linuxContainer->getFilePathOnRootFs(
            $this->getRenewalConfigFilePath()
        );
    }

    public function getRenewalConfigFilePath(): string
    {
        return sprintf(
            '/etc/letsencrypt/renewal/%s.conf',
            $this->getSslConfigName(),
        );
    }
}
