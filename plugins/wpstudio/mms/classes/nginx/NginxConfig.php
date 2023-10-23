<?php namespace Wpstudio\Mms\Classes\Nginx;

use Wpstudio\Mms\Classes\Cli;
use Wpstudio\Mms\Classes\Exceptions\MmsCliFileNotFoundException;
use Wpstudio\Mms\Classes\Exceptions\MmsFileContentException;
use Wpstudio\Mms\Classes\Helpers\FileContentHelper;
use Wpstudio\Mms\Classes\Exceptions;

class NginxConfig
{
    const SSL_CERT_LINE_SEARCH_QUERY = 'ssl_certificate_key';
    const AUTH_LINE_SEARCH_QUERY = 'auth.d';
    const TEMPORARY_LETSENCRYPT_SSL_KEY_PATH_PLACEHOLDER = 'tmp/domain';

    private NginxSite $nginxSite;

    private string $configFileContent;

    private Cli $cli;

    private string|false $authConfigLine;

    private string $sslConfigLine;

    public function __construct(NginxSite $nginxSite)
    {
        $this->nginxSite = $nginxSite;

        $this->cli = $this->nginxSite->nginxMasterProxy->linuxContainer->proxmoxServer->cli;
    }

    /**
     * @return void
     * @throws Exceptions\MmsNginxException
     */
    public function checkExistsConfigFile(): void
    {
        if (!$this->hasExistsConfigFile()) {
            throw new Exceptions\MmsNginxException(sprintf(
                'Nginx configuration file on server %s not exists: %s',
                $this->nginxSite->nginxMasterProxy->linuxContainer->proxmoxServer->server->code,
                $this->getAbsoluteConfigFilePath()
            ));
        }
    }

    public function hasExistsConfigFile(): bool
    {
        return $this->cli->hasExistsFile($this->getAbsoluteConfigFilePath());
    }

    public function getAbsoluteConfigFilePath(): string
    {
        return $this->nginxSite->nginxMasterProxy->linuxContainer->getFilePathOnRootFs(
            $this->getConfigFilePath()
        );
    }

    public function getConfigFilePath(): string
    {
        return sprintf(
            '/etc/nginx/conf.d/%s.conf',
            $this->nginxSite->siteCode,
        );
    }

    /**
     * @return string
     * @throws Exceptions\MmsFileContentException
     * @throws MmsCliFileNotFoundException
     */
    public function getSslCertConfigLine(): string
    {
        if (!isset($this->sslConfigLine)) {
            $this->sslConfigLine = FileContentHelper::getLineBySearchQuery(
                $this->getConfigFileContent(),
                self::SSL_CERT_LINE_SEARCH_QUERY,
            );
        }

        return $this->sslConfigLine;
    }

    /**
     * @throws MmsCliFileNotFoundException
     * @throws MmsFileContentException
     */
    public function isTemporarySelfSignedLetsencryptSsl(): bool
    {
        return str_contains($this->getSslCertConfigLine(), self::TEMPORARY_LETSENCRYPT_SSL_KEY_PATH_PLACEHOLDER);
    }

    /**
     * @return string
     * @throws Exceptions\MmsCliFileNotFoundException
     */
    public function getAuthConfigLine(): string
    {
        if (!isset($this->authConfigLine)) {
            try {
                $this->authConfigLine = FileContentHelper::getLineBySearchQuery(
                    $this->getConfigFileContent(),
                    self::AUTH_LINE_SEARCH_QUERY,
                );
            } catch (Exceptions\MmsFileContentException $e) {
                $this->authConfigLine = false;
            }
        }

        return $this->authConfigLine === false ? '' : $this->authConfigLine;
    }

    /**
     * @return bool
     * @throws Exceptions\MmsCliFileNotFoundException
     */
    public function hasExistsAuthConfigLine(): bool
    {
        $this->getAuthConfigLine();

        return !($this->authConfigLine === false);
    }

    /**
     * @return string
     * @throws Exceptions\MmsCliFileNotFoundException
     */
    public function getConfigFileContent(): string
    {
        if (!isset($this->configFileContent)) {
            $this->configFileContent = $this->getActualConfigFileContent();
        }

        return $this->configFileContent;
    }

    /**
     * @return string
     * @throws Exceptions\MmsCliFileNotFoundException
     */
    public function getActualConfigFileContent(): string
    {
        try {
            return $this->cli->get($this->getAbsoluteConfigFilePath());
        } catch (Exceptions\MmsCliFileNotFoundException $e) {
            throw $e->withServer($this->nginxSite->nginxMasterProxy->linuxContainer->proxmoxServer->server);
        }
    }

    /**
     * @param string $configFileContent
     */
    public function setConfigFileContent(string $configFileContent): void
    {
        $this->configFileContent = $configFileContent;
    }
}
