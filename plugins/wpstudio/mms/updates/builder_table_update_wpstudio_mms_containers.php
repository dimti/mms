<?php namespace Wpstudio\Mms\Updates;

use Schema;
use Winter\Storm\Database\Updates\Migration;

class BuilderTableUpdateWpstudioMmsContainers extends Migration
{
    public function up()
    {
        Schema::table('wpstudio_mms_containers', function($table)
        {
            $table->text('lxc_status')->nullable();
            $table->text('lxc_config')->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('wpstudio_mms_containers', function($table)
        {
            $table->dropColumn('lxc_status');
            $table->dropColumn('lxc_config');
        });
    }
}
