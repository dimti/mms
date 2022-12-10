<?php namespace Wpstudio\Mms\Classes\Nginx;

use Wpstudio\Mms\Classes\Cli;
use Wpstudio\Mms\Classes\Helpers\FileContentHelper;
use Wpstudio\Mms\Classes\Exceptions;

class NginxAuthConfig
{
    const PASSWORD_CONFIG_LINE_SEARCH_QUERY = 'passwords.d';

    private NginxSite $nginxSite;

    private Cli $cli;

    private string $authConfigFileContent;

    /**
     * @example simple-auth.conf
     */
    private string $authConfigFileName;

    /**
     * @example simple.passwd
     */
    private string $passwordsFileName;

    public function __construct(NginxSite $nginxSite)
    {
        $this->nginxSite = $nginxSite;

        $this->cli = $this->nginxSite->nginxMasterProxy->linuxContainer->proxmoxServer->cli;
    }

    /**
     * @return string
     * @throws Exceptions\MmsCliFileNotFoundException
     * @throws Exceptions\MmsFileContentException
     */
    public function getPasswordsLine(): string
    {
        return FileContentHelper::getLineBySearchQuery(
            $this->getAuthConfigFileContent(),
            self::PASSWORD_CONFIG_LINE_SEARCH_QUERY,
        );
    }

    /**
     * @return string
     * @throws Exceptions\MmsCliFileNotFoundException
     */
    public function getAuthConfigFileContent(): string
    {
        if (!isset($this->authConfigFileContent)) {
            $this->authConfigFileContent = $this->getActualAuthConfigFileContent();
        }

        return $this->authConfigFileContent;
    }

    /**
     * @return string
     * @throws Exceptions\MmsCliFileNotFoundException
     */
    public function getActualAuthConfigFileContent(): string
    {
        try {
            return $this->cli->get($this->getAbsoluteAuthConfigFilePath());
        } catch (Exceptions\MmsCliFileNotFoundException $e) {
            throw $e->withServer($this->nginxSite->nginxMasterProxy->linuxContainer->proxmoxServer->server);
        }
    }

    /**
     * @return void
     * @throws Exceptions\MmsNginxException
     */
    public function checkExistsAuthConfigFile(): void
    {
        if (!$this->hasExistsAuthConfigFile()) {
            throw new Exceptions\MmsNginxException(sprintf(
                'Nginx auth config file on server %s not exists: %s',
                $this->nginxSite->nginxMasterProxy->linuxContainer->proxmoxServer->server->code,
                $this->getAbsoluteAuthConfigFilePath()
            ));
        }
    }

    public function hasExistsAuthConfigFile(): bool
    {
        return $this->cli->hasExistsFile($this->getAbsoluteAuthConfigFilePath());
    }

    public function getAbsoluteAuthConfigFilePath(): string
    {
        return $this->nginxSite->nginxMasterProxy->linuxContainer->getFilePathOnRootFs(
            $this->getAuthConfigFilePath()
        );
    }

    public function getAuthConfigFilePath(): string
    {
        return sprintf(
            '/etc/nginx/auth.d/%s',
            $this->getAuthConfigFileName(),
        );
    }

    public function getAuthConfigFileName(): string
    {
        if (!isset($this->authConfigFileName)) {
            /**
             * @example include auth.d/simple-auth.conf;
             */
            $authConfigLine = $this->nginxSite->nginxConfig->getAuthConfigLine();

            $this->authConfigFileName = trim(explode('/', $authConfigLine)[1], ';');
        }

        return $this->authConfigFileName;
    }


    /**
     * @return void
     * @throws Exceptions\MmsNginxException
     */
    public function checkExistsPasswordsFile(): void
    {
        if (!$this->hasExistsPasswordsFile()) {
            throw new Exceptions\MmsNginxException(sprintf(
                'Nginx passwords file on server %s not exists: %s',
                $this->nginxSite->nginxMasterProxy->linuxContainer->proxmoxServer->server->code,
                $this->getAbsolutePasswordsFilePath()
            ));
        }
    }

    public function hasExistsPasswordsFile(): bool
    {
        return $this->cli->hasExistsFile($this->getAbsolutePasswordsFilePath());
    }

    public function getAbsolutePasswordsFilePath(): string
    {
        return $this->nginxSite->nginxMasterProxy->linuxContainer->getFilePathOnRootFs(
            $this->getPasswordsFilePath()
        );
    }

    public function getPasswordsFilePath(): string
    {
        return sprintf(
            '/etc/nginx/passwords.d/%s',
            $this->getPasswordsFileName(),
        );
    }

    public function getPasswordsFileName(): string
    {
        if (!isset($this->passwordsFileName)) {
            /**
             * @example auth_basic_user_file passwords.d/simple.passwd;
             */
            $passwordsLine = $this->getPasswordsLine();

            $this->passwordsFileName = trim(explode('/', $passwordsLine)[1], ';');
        }

        return $this->passwordsFileName;
    }
}
