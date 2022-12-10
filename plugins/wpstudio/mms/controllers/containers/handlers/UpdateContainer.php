<?php namespace Wpstudio\Mms\Controllers\Containers\Handlers;

use Wpstudio\Mms\Classes\Traits\SiteInteractions;
use Wpstudio\Mms\Models;

class UpdateContainer extends \Winter\Storm\Extension\ExtensionBase
{
    use SiteInteractions;

    public function onUpdateContainer(): void
    {
        $this->prepareModel();

        $this->prepareLinuxContainer();

        $this->linuxContainer->updateStatusAndConfig();
    }
}
