<?php namespace Wpstudio\Mms\Updates;

use Seeder;
use DB;
use Wpstudio\Mms\Models\ServerType;

class Seeder108 extends Seeder
{
    public function run()
    {
        \DB::table('wpstudio_mms_destination_roles')->insert([
            [
                'code' => 'nginx-master-proxy',
                'name' => 'Nginx Master Proxy',
                'description' => '',
                'sort_order' => 1,
            ],
            [
                'code' => 'site',
                'name' => 'Сайт',
                'description' => 'Сайт, например WordPress',
                'sort_order' => 2,
            ],
            [
                'code' => 'database',
                'name' => 'База данных',
                'description' => '',
                'sort_order' => 3,
            ],
            [
                'code' => 'redis',
                'name' => 'Redis',
                'description' => '',
                'sort_order' => 4,
            ],
        ]);

        \DB::table('wpstudio_mms_network_types')->insert([
            [
                'code' => 'direct',
                'name' => 'Direct',
                'description' => 'Сеть, смотрящая наружу, с прямым внешним IP-адресом',
                'sort_order' => 2,
            ],
            [
                'code' => 'inner',
                'name' => 'Inner',
                'description' => 'Локальная сеть, под внутренним DHCP',
                'sort_order' => 1,
            ],
        ]);

        \DB::table('wpstudio_mms_server_types')->insert([
            [
                'code' => 'proxmox',
                'name' => 'Proxmox',
                'description' => '',
                'sort_order' => 1,
            ],
            [
                'code' => 'bare-metal',
                'name' => 'Bare Metal',
                'description' => '',
                'sort_order' => 3,
            ],
            [
                'code' => 'caprover',
                'name' => 'Cap Rover',
                'description' => '',
                'sort_order' => 2,
            ],
        ]);
    }
}