<?php namespace Wpstudio\Mms\Classes\Traits;

use Backend\Classes\BackendController;
use Wpstudio\Mms\Classes\Exceptions\MmsException;
use Wpstudio\Mms\Classes\LinuxContainer;
use Wpstudio\Mms\Classes\Nginx\NginxMasterProxy;
use Wpstudio\Mms\Classes\Nginx\NginxSite;
use Wpstudio\Mms\Models;

trait SiteInteractions
{
    protected Models\Container $container;
    protected LinuxContainer $linuxContainer;

    protected NginxMasterProxy $currentMasterNginxProxy;
    protected NginxMasterProxy $destinationMasterNginxProxy;

    protected NginxSite $nginxSite;

    private function prepareModel(): void
    {
        $this->container = Models\Container::with([
            'networkType',
            'server',
            'server.cluster',
        ])->whereKey(BackendController::$params[0])->firstOrFail();
    }

    protected function prepareLinuxContainer(): void
    {
        $this->linuxContainer = $this->container->getLinuxContainer();
    }

    /**
     * @return void
     * @throws MmsException
     */
    protected function prepareCurrentMasterNginxProxy(): void
    {
        $this->currentMasterNginxProxy = $this->container->server->getNginxMasterProxy();
    }

    /**
     * @param string $siteCode
     * @return void
     * @throws MmsException
     */
    protected function prepareNginxSite(): void
    {
        $this->nginxSite = $this->container->getNginxSite();
    }
}
