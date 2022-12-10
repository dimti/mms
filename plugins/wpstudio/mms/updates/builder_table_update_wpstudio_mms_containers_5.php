<?php namespace Wpstudio\Mms\Updates;

use Schema;
use Winter\Storm\Database\Schema\Blueprint;
use Winter\Storm\Database\Updates\Migration;

class BuilderTableUpdateWpstudioMmsContainers5 extends Migration
{
    public function up()
    {
        Schema::table('wpstudio_mms_containers', function (Blueprint $table) {
            $table->text('replication')->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('wpstudio_mms_containers', function (Blueprint $table) {
            $table->dropColumn('replication');
        });
    }
}
