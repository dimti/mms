<?php namespace Wpstudio\Mms\Updates;

use Schema;
use Winter\Storm\Database\Schema\Blueprint;
use Winter\Storm\Database\Updates\Migration;

class BuilderTableUpdateWpstudioMmsServers3 extends Migration
{
    public function up()
    {
        Schema::table('wpstudio_mms_servers', function (Blueprint $table) {
            $table->text('hostname');
            $table->dropColumn('name');
        });
    }
    
    public function down()
    {
        Schema::table('wpstudio_mms_servers', function (Blueprint $table) {
            $table->dropColumn('hostname');
            $table->string('name', 255);
        });
    }
}
