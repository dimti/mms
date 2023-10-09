<?php

namespace wpstudio\cloud\classes\helpers;

use wpstudio\cloud\classes\BegetApi;

class PlatformHelper
{
    public static function selectPlatform(string $code,string $key): BegetApi
    {
        if ($code == 'beget') {
            return new BegetApi($key);
        } else {
            throw new \ErrorException('Нет такой платформы');
        }
    }
}