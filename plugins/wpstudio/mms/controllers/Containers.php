<?php namespace Wpstudio\Mms\Controllers;

use Backend\Classes\Controller;
use BackendMenu;
use Winter\Storm\Database\Builder;
use Wpstudio\Mms\Controllers\Containers\Handlers\CheckNginxSiteCode;
use Wpstudio\Mms\Controllers\Containers\Handlers\MoveSite;
use Wpstudio\Mms\Controllers\Containers\Handlers\MoveSiteNginxConfigAndSslCerts;
use Wpstudio\Mms\Controllers\Containers\Handlers\UpdateContainer;

class Containers extends Controller
{
    public $implement = [
        'Backend\Behaviors\ListController',
        'Backend\Behaviors\FormController',
        'Backend\Behaviors\ReorderController',
        'Backend\Behaviors\RelationController',
        MoveSite::class,
        UpdateContainer::class,
        CheckNginxSiteCode::class,
    ];

    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';
    public $reorderConfig = 'config_reorder.yaml';
    public $relationConfig = 'config_relation.yaml';

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Wpstudio.Mms', 'main-menu-item', 'containers');
    }

    public function listExtendQuery(Builder $query)
    {
        $query->selectRaw('JSON_UNQUOTE(JSON_EXTRACT(lxc_status, ?)) as status', ['$.status']);
    }
}
