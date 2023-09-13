<?php namespace Wpstudio\Mms\Controllers\Servers\Handlers;

use Backend\Classes\BackendController;
use Winter\Storm\Extension\ExtensionBase;
use Wpstudio\Mms\Classes\Helpers\Formatters;
use Wpstudio\Mms\Models;

class GetAllCurrentMemUsage extends ExtensionBase
{
    private Models\Server $server;

    public function onGetAllCurrentMemUsage()
    {
        $this->prepareModel();

        $allCurrentUsageRam = 0;

        $this->getServer()->containers->each(function (Models\Container $container) use (&$allCurrentUsageRam) {
            $allCurrentUsageRam += $container->lxc_status['mem'];
        });

        return [
            'result' => Formatters::memInGb($allCurrentUsageRam),
        ];
    }

    private function prepareModel(): void
    {
        $this->server = Models\Server::with([
            'containers' => fn($qContainers) => $qContainers->select([
                'id',
                'server_id',
                'lxc_status',
            ])
        ])->whereKey(BackendController::$params[0])->firstOrFail();
    }

    /**
     * @return Server
     */
    public function getServer(): Models\Server
    {
        return $this->server;
    }
}
