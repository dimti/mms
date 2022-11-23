<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {

        $this->call(WpstudioMmsServerTypesTableSeeder::class);
        $this->call(WpstudioMmsNetworkTypesTableSeeder::class);
        $this->call(WpstudioMmsDestinationRolesTableSeeder::class);
    }
}
