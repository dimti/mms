<?php namespace Wpstudio\Mms\Controllers\Containers\Handlers;

use Winter\Storm\Extension\ExtensionBase;
use Wpstudio\Mms\Classes\Exceptions\MmsException;
use Wpstudio\Mms\Classes\Traits\SiteInteractions;
use Session;


class CheckNginxSiteCode extends ExtensionBase
{
    use SiteInteractions;

    /**
     * @return void
     * @throws MmsException
     */
    public function onCheckNginxSiteCode(): void
    {
        $this->prepareModel();
        $this->prepareLinuxContainer();
        $this->prepareCurrentMasterNginxProxy();
        $this->prepareNginxSite();

        $this->nginxSite->nginxConfig->checkExistsConfigFile();

        $this->nginxSite->sslCert->checkExistsLiveDir();
        $this->nginxSite->sslCert->checkExistsArchiveDir();
        $this->nginxSite->sslCert->checkExistsRenewalConfigFile();

        Session::flash('onCheckNginxSiteCodeMessage', 'Конфигурационный файл найден');
    }
}
