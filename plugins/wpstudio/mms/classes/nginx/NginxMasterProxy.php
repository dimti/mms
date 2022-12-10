<?php namespace Wpstudio\Mms\Classes\Nginx;

use Wpstudio\Mms\Classes\LinuxContainer;

class NginxMasterProxy
{
    public LinuxContainer $linuxContainer;

    public function __construct(LinuxContainer $linuxContainer)
    {
        $this->linuxContainer = $linuxContainer;
    }
}
