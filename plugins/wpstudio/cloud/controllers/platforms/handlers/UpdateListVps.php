<?php

namespace wpstudio\cloud\controllers\platforms\handlers;

use Backend\Classes\BackendController;
use Winter\Storm\Extension\ExtensionBase;
use wpstudio\cloud\classes\helpers\PlatformHelper;
use Wpstudio\Cloud\Models;

class UpdateListVps extends ExtensionBase
{
    /**
     * @return void
     * @throws \OpenAPI\Client\ApiException
     */
    public function onGetListVPS(): void
    {
        try {
            $model = $this->prepareModel();
            $platform = PlatformHelper::selectPlatform($model['code'],$model['api_key']);
            $beget = $platform->getListBegetVps();
            $this->updateListVpsBeget($beget);
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
        $model = Models\Platform::join(
            "wpstudio_cloud_platform_types",
            'wpstudio_cloud_platforms.platform_type_id',
            '=',
            "wpstudio_cloud_platform_types.id"
        )
            ->where('wpstudio_cloud_platforms.id', BackendController::$params[0])
            ->first();
        if ($model == null) {
            throw new \ErrorException('Нет такой платформы');
        }
        return $model;
    }

    /**
     * @param array $list
     * @return void
     */
    protected function updateListVpsBeget(array $list): void
    {
        foreach ($list as $vps) {
            Models\Vps::updateOrCreate(
                ['vps_id' => $vps['id']],
                [
                    'vps_id' => $vps['id'],
                    'vps_name' => $vps['display_name'],
                    'slug' => $vps['slug'],
                    'hostname' => $vps['hostname'],
                    'ip_address' => $vps['ip_address'],
                    'status' => json_encode([
                        'status' => $vps['status'],
                        'configuration' => $vps['configuration'],
                    ]),
                    'platform_id' => BackendController::$params[0],
                ]
            );
        }
    }
}
