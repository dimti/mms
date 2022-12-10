<?php namespace Wpstudio\Mms\Updates;

use Schema;
use Winter\Storm\Database\Schema\Blueprint;
use Winter\Storm\Database\Updates\Migration;

class BuilderTableUpdateWpstudioMmsServers2 extends Migration
{
    public function up()
    {
        Schema::table('wpstudio_mms_servers', function (Blueprint $table) {
            $table->text('cluster_node_status')->nullable();
            $table->text('node_status')->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('wpstudio_mms_servers', function (Blueprint $table) {
            $table->dropColumn('cluster_node_status');
            $table->dropColumn('node_status');
        });
    }
}
