<?php namespace Wpstudio\Mms\Updates;

use Schema;
use Winter\Storm\Database\Schema\Blueprint;
use Winter\Storm\Database\Updates\Migration;

class Migration1023 extends Migration
{
    public function up()
    {
        Schema::table('wpstudio_mms_servers', function (Blueprint $table) {
            $table->unique(['code', 'cluster_id']);
        });

        Schema::table('wpstudio_mms_containers', function (Blueprint $table) {
            $table->unique(['code', 'server_id']);
        });

        Schema::table('wpstudio_mms_servers', function (Blueprint $table) {
            $table->dropUnique('wpstudio_mms_servers_code_unique');
        });

        Schema::table('wpstudio_mms_containers', function (Blueprint $table) {
            $table->dropUnique('wpstudio_mms_containers_code_unique');
        });
    }

    public function down()
    {
        Schema::table('wpstudio_mms_servers', function (Blueprint $table) {
            $table->dropUnique(['code', 'cluster_id']);
        });

        Schema::table('wpstudio_mms_containers', function (Blueprint $table) {
            $table->dropUnique(['code', 'server_id']);
        });

        Schema::table('wpstudio_mms_servers', function (Blueprint $table) {
            $table->unique('code');
        });

        Schema::table('wpstudio_mms_containers', function (Blueprint $table) {
            $table->unique('code');
        });
    }
}