<?php namespace Wpstudio\Mms\Controllers;

use Backend\Classes\Controller;
use BackendMenu;
use Collective\Remote\Connection;
use Proxmox\PVE;
use SSH;
use Wpstudio\Mms\Classes\LinuxContainer;
use Wpstudio\Mms\Classes\Nginx\NginxMasterProxy;
use Wpstudio\Mms\Classes\Nginx\NginxSite;
use Wpstudio\Mms\Classes\ProxmoxServer;
use Wpstudio\Mms\Controllers\Servers\Handlers\GetAllCurrentMemUsage;

class Servers extends Controller
{
    public $implement = [
        'Backend\Behaviors\ListController',
        'Backend\Behaviors\FormController',
        'Backend\Behaviors\ReorderController',
        'Backend\Behaviors\RelationController',
        GetAllCurrentMemUsage::class,
    ];

    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';
    public $reorderConfig = 'config_reorder.yaml';
    public $relationConfig = 'config_relation.yaml';

    private PVE $proxmox;

    private array $vmConfig = [];

    private Connection $dedic100Connection;

    private Connection $dedic106Connection;

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Wpstudio.Mms', 'main-menu-item', 'servers');
    }
}
