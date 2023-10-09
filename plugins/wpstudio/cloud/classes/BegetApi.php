<?php

namespace wpstudio\cloud\classes;

use GuzzleHttp\Client;
use OpenAPI\Client\Api\ManageServiceApi;
use OpenAPI\Client\Configuration;

class BegetApi
{

    private $init;

    /**
     * @param string $key
     */
    public function __construct(string $key)
    {
        $this->initBeget($key);
    }

    /**
     * @param string $key
     * @return void
     */
    private function initBeget(string $key): void
    {;
        $config = Configuration::getDefaultConfiguration()->setAccessToken($key);
        $this->init = new ManageServiceApi(
            new Client(),
            $config
        );
    }

    /**
     * @param string $vpsId
     * @return mixed
     */
    public function getOneBegetVps(string $vpsId): mixed
    {
        $vps = $this->init->manageServiceGetInfo($vpsId);
        return $vps->getVps();
    }

    /**
     * @return mixed
     */
    public function getListBegetVps(): mixed
    {
        $vps = $this->init->manageServiceGetList();
        return $vps->getVps();
    }
}