<?php namespace Wpstudio\Mms\Classes\Helpers;

use Conversion;

abstract class Formatters
{
    private function __construct() {}

    public static function memInGb(int $mem)
    {
        return sprintf(
            '%s Gb',
            Conversion::convert($mem, 'byte')->to('gigabyte')->format(2, '.')
        );
    }
}
