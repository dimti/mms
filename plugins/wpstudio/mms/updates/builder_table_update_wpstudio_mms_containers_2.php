<?php namespace Wpstudio\Mms\Updates;

use Schema;
use Winter\Storm\Database\Schema\Blueprint;
use Winter\Storm\Database\Updates\Migration;

class BuilderTableUpdateWpstudioMmsContainers2 extends Migration
{
    public function up()
    {
        Schema::table('wpstudio_mms_containers', function (Blueprint $table) {
            $table->integer('server_id')->unsigned();

            $table->foreign(['server_id'])
                ->references(['id'])
                ->on('wpstudio_mms_servers');
        });
    }

    public function down()
    {
        Schema::table('wpstudio_mms_containers', function (Blueprint $table) {
            $table->dropForeign(['server_id']);
        });

        Schema::table('wpstudio_mms_containers', function (Blueprint$table)
        {
            $table->dropColumn('server_id');
        });
    }
}