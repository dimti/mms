<?php

namespace Wpstudio\Mms\Controllers\Clusters\Handlers;

use Backend\Classes\FormField;
use Winter\Storm\Extension\ExtensionBase;
use Wpstudio\Mms\Models\Server;

class UpdateMainServer extends ExtensionBase
{
    public function onChooseServer()
    {
        $data = post();
//        dd($data["is_main_server"]);
        if (isset($data['checked'][0])) {
            $serverId = $data["checked"][0];
            $server = Server::find($serverId);
//        dd($server->is_main_server);
        }

        if ($server) {
            $server->is_main_server = $data["is_main_server"] == 'on' ? 1 : 0;
        }

        $server->save();
    }
}