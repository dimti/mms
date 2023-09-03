<?php namespace Wpstudio\Mms\Updates;

use Schema;
use Winter\Storm\Database\Schema\Blueprint;
use Winter\Storm\Database\Updates\Migration;

class BuilderTableUpdateWpstudioMmsSysadmins extends Migration
{
    public function up()
    {
        Schema::table('wpstudio_mms_sysadmins', function (Blueprint $table) {
            $table->integer('developer_id')->unsigned();
        });
    }
    
    public function down()
    {
        Schema::table('wpstudio_mms_sysadmins', function (Blueprint $table) {
            $table->dropColumn('developer_id');
        });
    }
}