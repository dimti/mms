<?php namespace wpstudio\cloud\Controllers;

use Backend\Classes\Controller;
use BackendMenu;
use wpstudio\cloud\controllers\platforms\handlers\UpdateListVps;

class Platforms extends Controller
{
    public $implement = [
        'Backend\Behaviors\ListController',
        'Backend\Behaviors\FormController',
        'Backend\Behaviors\ReorderController',
        'Backend\Behaviors\RelationController',
        UpdateListVps::class,
    ];
    
    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';
    public $reorderConfig = 'config_reorder.yaml';

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('wpstudio.cloud', 'main-menu-item', 'cloud-platforms');
    }
}
