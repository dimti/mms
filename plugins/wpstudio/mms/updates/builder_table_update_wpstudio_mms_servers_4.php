<?php namespace Wpstudio\Mms\Updates;

use Schema;
use Winter\Storm\Database\Schema\Blueprint;
use Winter\Storm\Database\Updates\Migration;

class BuilderTableUpdateWpstudioMmsServers4 extends Migration
{
    public function up()
    {
        Schema::table('wpstudio_mms_servers', function (Blueprint $table) {
            $table->boolean('is_main_server')->nullable();

            $table->unique(['cluster_id', 'is_main_server'], 'unique_cluster_main_server');
        });
    }
    
    public function down()
    {
        Schema::table('wpstudio_mms_servers', function (Blueprint $table) {
            $table->dropColumn('is_main_server');
        });
    }
}