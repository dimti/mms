<?php

namespace Wpstudio\Mms\Controllers\Clusters\Handlers;

use Backend\Classes\FormField;
use Illuminate\Support\Facades\Request;
use Winter\Storm\Extension\ExtensionBase;
use Winter\Storm\Support\Facades\Event;
use Winter\Storm\Support\Facades\Flash;
use Wpstudio\Mms\Models\Cluster;
use Wpstudio\Mms\Models\Server;

class UpdateMainServer extends ExtensionBase
{
    public function onChooseServer()
    {
//        $clusterId = $this->params(0);
        $clusterId = Request::segment(6);

        $data = post();
        $server = null;
        $serverId = null;
        $isMainServer = false;

        if (isset($data["is_main_server"])) {
            $isMainServer = $data["is_main_server"] == 'on';
        }

        if (isset($data['checked'][0])) {
            $serverId = $data["checked"][0];
        }

        if ($serverId) {
            $server = Server::find($serverId);

            if (!$server){
                Flash::error('Сервер не найден. Операция отклонена.');
                return;
            }

            if ($server->cluster_id != $clusterId) {
                Flash::error('Сервер не принадлежит к данному кластеру. Операция отклонена.');
                return;
            }

            $server->is_main_server = !$server->is_main_server;

            if($isMainServer && empty($server->hostname)){
                Flash::error('Главный сервер должен иметь hostname. Операция отклонена.');
                return;
            }

            $previousMainServer = Server::where('is_main_server', true)
                ->where('cluster_id', $clusterId)
                ->first();

            if ($previousMainServer){
                $previousMainServer->is_main_server = false;
                $previousMainServer->save();
            }

            $server->save();

//            if ($isMainServer) {
//                Event::fire('wpstudio.mms.ServerChangedEvent', [$server, $clusterId]);
//            }
            if ($server->is_main_server) {
                Event::fire('wpstudio.mms.ServerChangedEvent', [$server, $clusterId, true]);
            } else {
                Event::fire('wpstudio.mms.ServerChangedEvent', [$server, $clusterId, false]);
            }

            return response()->json([
                'message'   => 'Successfully',
                'server'    => $server,
                'clusterId' => $clusterId,
            ]);
        }
    }
}