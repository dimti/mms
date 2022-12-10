<?php namespace Wpstudio\Mms\Controllers;

use Backend\Classes\Controller;
use BackendMenu;
use Wpstudio\Mms\Controllers\Rescue\Handlers\PostInstallationActions;
use Wpstudio\Mms\Controllers\Rescue\Handlers\SetupHetznerProxmoxServer;

class Rescue extends Controller
{
    public $implement = [
        SetupHetznerProxmoxServer::class,
        PostInstallationActions::class,
    ];

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Wpstudio.Mms', 'main-menu-item', 'mms-rescue');
    }

    public function index()
    {

    }
}
