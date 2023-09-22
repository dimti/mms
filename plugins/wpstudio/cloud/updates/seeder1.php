<?php namespace wpstudio\cloud\updates;

use Seeder;

class Seeder1 extends Seeder
{
    public function run()
    {
        \DB::table('wpstudio_cloud_platform_types')->insert([
            [
                'code' => 'code',
                'name' => '123456code',
                'description' => 'Code 123456',
            ],
            [
                'code' => 'word',
                'name' => 'code159456',
                'description' => '',
            ],
            [
                'code' => 'winter',
                'name' => 'cmswinter',
                'description' => '123winter',
            ],
            [
                'code' => '1234lara',
                'name' => 'laravel',
                'description' => 'laravel website',
            ],
            [
                'code' => 'demo',
                'name' => 'sitewinter',
                'description' => 'site winter cms',
            ],
        ]);
    }
}
