<?php namespace wpstudio\cloud\updates;

use Seeder;
use wpstudio\helpers\classes\traits\SluggableCode;

class Seeder1 extends Seeder
{
    use SluggableCode;
    const CODE_BEGET = 'beget';
    public function run()
    {
        \DB::table('wpstudio_cloud_platform_types')->insert([
            [
                'code' => self::CODE_BEGET,
                'name' => 'beget',
            ],
        ]);
    }
}
