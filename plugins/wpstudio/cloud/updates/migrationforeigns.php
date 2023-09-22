<?php namespace wpstudio\cloud\updates;

use Schema;
use Winter\Storm\Database\Schema\Blueprint;
use Winter\Storm\Database\Updates\Migration;

class MigrationForeigns extends Migration
{
    public function up()
    {
         Schema::table('wpstudio_cloud_vps', function (Blueprint $table) {
            $table->foreign(['platform_id'])
                ->references(['id'])
                ->on('wpstudio_cloud_platforms');
         });

         Schema::table('wpstudio_cloud_platform_types', function (Blueprint $table) {
            $table->foreign(['platform_id'])
                ->references(['id'])
                ->on('wpstudio_cloud_platforms');
         });
    }

    public function down()
    {
        Schema::table('wpstudio_cloud_vps', function (Blueprint $table) {
            $table->dropForeign(['platform_id']);
        });

        Schema::table('wpstudio_cloud_platforms', function (Blueprint $table) {
            $table->dropForeign(['type_id']);
        });
    }
}
