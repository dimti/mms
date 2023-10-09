<?php

namespace wpstudio\cloud\controllers\vps\handlers;

use Backend\Classes\BackendController;
use Winter\Storm\Extension\ExtensionBase;
use wpstudio\cloud\classes\helpers\PlatformHelper;
use Wpstudio\Cloud\Models;
use wpstudio\cloud\Models\Vps;

class UpdateStatusVps extends ExtensionBase
{
    /**
     * @return void
     * @throws \Exception
     */
    public function onUpdateStatus(): void
    {
        try {
            $model = $this->prepareModel();
            $beget = PlatformHelper::selectPlatform($model['code'], $model['api_key']);
            $vps = $beget->getOneBegetVps($model['vps_id']);
            $this->updateStatusBegetVps($vps);
        } catch (\ErrorException $e) {
            echo $e->getMessage();
        }
    }

    /**
     * @return mixed
     * @throws \ErrorException
     */
    protected function prepareModel(): mixed
    {
        $model = Models\Vps::join(
            "wpstudio_cloud_platforms",
            'wpstudio_cloud_vps.platform_id',
            '=',
            "wpstudio_cloud_platforms.id"
        )
            ->join(
                "wpstudio_cloud_platform_types",
                'wpstudio_cloud_platforms.platform_type_id',
                '=',
                "wpstudio_cloud_platform_types.id"
            )
            ->where('wpstudio_cloud_vps.id', BackendController::$params[0])
            ->first();
        if ($model == null) {
            throw new \ErrorException('Нет такой модели');
        }
        return $model;
    }

    /**
     * @param array $vps
     * @return void
     * @throws \Exception
     */
    protected function updateStatusBegetVps($vps): void
    {
        $model = Vps::find(BackendController::$params[0]);
        if ($model == null) {
            throw new \ErrorException('Нет такой модели');
        }
        $model->update([
            'status' => json_encode([
                'status' => $vps['status'],
                'configuration' => $vps['configuration'],
            ])
        ]);
    }
}
