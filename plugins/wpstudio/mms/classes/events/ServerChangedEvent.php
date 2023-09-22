<?php

namespace Wpstudio\Mms\Classes\Events;

use Winter\Storm\Support\Facades\Flash;
use Wpstudio\Mms\Models\Cluster;

class ServerChangedEvent
{
    public function handle($server, $clusterId, $isMainServer)
    {
        $domain = parse_url($server->hostname, PHP_URL_HOST);

        $cluster = Cluster::find($clusterId);

        if ($cluster) {
            if (empty($server->hostname)){
                Flash::error('Главный сервер должен иметь hostname. Операция отклонена.');
                return;
            }

            if (!$isMainServer){
                $cluster->code = '';
            } else {
                $cluster->code = $domain;
            }

            $cluster->save();
        }
    }
}