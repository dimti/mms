<?php namespace Wpstudio\Mms\Updates;

use Schema;
use Winter\Storm\Database\Schema\Blueprint;
use Winter\Storm\Database\Updates\Migration;

class BuilderTableUpdateWpstudioMmsContainers4 extends Migration
{
    public function up()
    {
        Schema::table('wpstudio_mms_containers', function (Blueprint $table) {
            $table->text('role_payload')->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('wpstudio_mms_containers', function (Blueprint $table) {
            $table->dropColumn('role_payload');
        });
    }
}
