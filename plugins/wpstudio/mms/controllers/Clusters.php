<?php namespace Wpstudio\Mms\Controllers;

use Backend\Classes\Controller;
use BackendMenu;
use Wpstudio\Mms\Controllers\Clusters\Handlers\UpdateContainers;

class Clusters extends Controller
{
    public $implement = [
        'Backend\Behaviors\ListController',
        'Backend\Behaviors\FormController',
        'Backend\Behaviors\ReorderController',
        'Backend\Behaviors\RelationController',
        UpdateContainers::class,
    ];

    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';
    public $reorderConfig = 'config_reorder.yaml';
    public $relationConfig = 'config_relation.yaml';

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Wpstudio.Mms', 'main-menu-item', 'mms-clusters');
    }

}
