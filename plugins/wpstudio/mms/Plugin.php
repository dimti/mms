<?php namespace Wpstudio\Mms;

use System\Classes\PluginBase;
use Winter\Storm\Support\Facades\Event;
use Wpstudio\Mms\Models\Server;

class Plugin extends PluginBase
{
    public function boot()
    {
        Event::listen('wpstudio.mms.ServerChangedEvent', 'Wpstudio\Mms\Classes\Events\ServerChangedEvent');

        Event::listen('model.beforeSave', function ($model){
            if ($model instanceof Server){
                $model->beforeSave();
            }
        });
    }
    
    public function registerComponents()
    {
    }

    public function registerSettings()
    {
    }
}
