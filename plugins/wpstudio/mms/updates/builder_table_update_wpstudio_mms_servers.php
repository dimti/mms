<?php namespace Wpstudio\Mms\Updates;

use Schema;
use Winter\Storm\Database\Schema\Blueprint;
use Winter\Storm\Database\Updates\Migration;

class BuilderTableUpdateWpstudioMmsServers extends Migration
{
    public function up()
    {
        Schema::table('wpstudio_mms_servers', function (Blueprint $table)
        {
            $table->integer('cluster_id')->nullable()->unsigned();

            $table->foreign(['cluster_id'])
                ->references(['id'])
                ->on('wpstudio_mms_clusters');
        });
    }

    public function down()
    {
        Schema::table('wpstudio_mms_servers', function (Blueprint $table)
        {
            $table->dropForeign(['cluster_id']);
        });

        Schema::table('wpstudio_mms_servers', function (Blueprint $table)
        {
            $table->dropColumn('cluster_id');
        });
    }
}