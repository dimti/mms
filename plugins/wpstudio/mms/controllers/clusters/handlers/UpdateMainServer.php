<?php

namespace Wpstudio\Mms\Controllers\Clusters\Handlers;

use Backend\Classes\FormField;
use Winter\Storm\Extension\ExtensionBase;
use Winter\Storm\Support\Facades\Event;
use Winter\Storm\Support\Facades\Flash;
use Wpstudio\Mms\Models\Server;

class UpdateMainServer extends ExtensionBase
{
    public function onChooseServer()
    {
        $data = post();
        $server = null;

        if (isset($data["is_main_server"])) {
            $isMainServer = $data["is_main_server"] == 'on';
        }

        if (isset($data['checked'][0])) {
            $serverId = $data["checked"][0];

            $server = Server::find($serverId);
        }

        if ($server) {
            if($isMainServer && empty($server->hostname)){
                Flash::error('Главный сервер должен иметь hostname. Операция отклонена.');
                return;
            }

            $server->is_main_server = $isMainServer;

            if ($isMainServer) {
                Event::fire('wpstudio.mms.ServerChangedEvent', [$server]);
            }

            $server->save();
        }
    }
}