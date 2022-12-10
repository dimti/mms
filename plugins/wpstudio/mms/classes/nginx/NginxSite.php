<?php

namespace Wpstudio\Mms\Classes\Nginx;

use Wpstudio\Mms\Classes\Exceptions\MmsException;

class NginxSite
{
    public NginxMasterProxy $nginxMasterProxy;

    public NginxConfig $nginxConfig;

    public LetsEncryptSslCert $sslCert;

    public NginxAuthConfig $nginxAuthConfig;

    public string $siteCode;

    /**
     * @param NginxMasterProxy $nginxMasterProxy
     * @param string $siteCode
     * @example siteCode=tw
     * @param string|null $configFileContent
     * @throws \Wpstudio\Mms\Classes\Exceptions\MmsCliFileNotFoundException
     */
    public function __construct(NginxMasterProxy $nginxMasterProxy, string $siteCode, ?string $configFileContent = null)
    {
        $this->nginxMasterProxy = $nginxMasterProxy;

        $this->siteCode = $siteCode;

        $this->nginxConfig = new NginxConfig($this);

        $this->sslCert = new LetsEncryptSslCert($this);

        if (!is_null($configFileContent)) {
            $this->nginxConfig->setConfigFileContent($configFileContent);
        }

        if ($this->nginxConfig->hasExistsAuthConfigLine()) {
            $this->nginxAuthConfig = new NginxAuthConfig($this);
        }
    }

    public function hasNginxAuthConfig(): bool
    {
        return isset($this->nginxAuthConfig);
    }
}
