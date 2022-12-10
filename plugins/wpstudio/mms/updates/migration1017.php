<?php namespace Wpstudio\Mms\Updates;

use Schema;
use Winter\Storm\Database\Schema\Blueprint;
use Winter\Storm\Database\Updates\Migration;
use Wpstudio\Mms\Models;

class Migration1017 extends Migration
{
    public function up()
    {
        Schema::create('wpstudio_mms_cluster_sysadmin', function (Blueprint $table) {
            $table->increments('id')->unsigned();

            $table->unsignedInteger('cluster_id');

            $table->unsignedInteger('sysadmin_id');

            $table->foreign(['cluster_id'])
                ->references(['id'])
                ->on('wpstudio_mms_clusters');

            $table->foreign(['sysadmin_id'])
                ->references(['id'])
                ->on('wpstudio_mms_sysadmins');
        });

        Schema::create('wpstudio_mms_server_sysadmin', function (Blueprint $table) {
            $table->increments('id')->unsigned();

            $table->unsignedInteger('server_id');

            $table->unsignedInteger('sysadmin_id');

            $table->foreign(['server_id'])
                ->references(['id'])
                ->on('wpstudio_mms_servers');

            $table->foreign(['sysadmin_id'])
                ->references(['id'])
                ->on('wpstudio_mms_sysadmins');
        });
    }

    public function down()
    {
        Schema::drop('wpstudio_mms_cluster_sysadmin');

        Schema::drop('wpstudio_mms_server_sysadmin');
    }
}