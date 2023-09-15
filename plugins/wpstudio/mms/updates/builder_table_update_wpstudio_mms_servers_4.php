<?php namespace Wpstudio\Mms\Updates;

use Schema;
use Winter\Storm\Database\Schema\Blueprint;
use Winter\Storm\Database\Updates\Migration;

class BuilderTableUpdateWpstudioMmsServers4 extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('wpstudio_mms_servers', 'is_main_server')){
            Schema::table('wpstudio_mms_servers', function (Blueprint $table) {
                $table->boolean('is_main_server')->nullable();

                $table->unique(['cluster_id', 'is_main_server'], 'unique_cluster_main_server');
            });
        }
    }
    
    public function down()
    {
        if (Schema::hasColumn('wpstudio_mms_servers', 'is_main_server')) {
            Schema::table('wpstudio_mms_servers', function (Blueprint $table) {
                $table->dropUnique('unique_cluster_main_server');

                $table->dropColumn('is_main_server');
            });
        }
    }
}