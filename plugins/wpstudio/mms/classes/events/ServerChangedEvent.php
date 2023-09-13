<?php

namespace Wpstudio\Mms\Classes\Events;

use Winter\Storm\Support\Facades\Flash;
use Wpstudio\Mms\Models\Cluster;

class ServerChangedEvent
{
    public function handle($server)
    {
        // Server domain name without protocol and slashes
        $domain = parse_url($server->hostname, PHP_URL_HOST);

        // The cluster this server belongs to
        $cluster = $server->cluster;

        if ($cluster) {
            if (empty($server->hostname)){
                Flash::error('Главный сервер должен иметь hostname. Операция отклонена.');
                return;
            }

            // Setting the server domain name as the cluster codename
            $cluster->name = $domain;

            $cluster->save();
        }
    }
}