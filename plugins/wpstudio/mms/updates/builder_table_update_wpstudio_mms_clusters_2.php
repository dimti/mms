<?php namespace Wpstudio\Mms\Updates;

use Schema;
use Winter\Storm\Database\Schema\Blueprint;
use Winter\Storm\Database\Updates\Migration;

class BuilderTableUpdateWpstudioMmsClusters2 extends Migration
{
    public function up()
    {
        Schema::table('wpstudio_mms_clusters', function (Blueprint $table) {
            $table->dropColumn('name');
        });
    }
    
    public function down()
    {
        Schema::table('wpstudio_mms_clusters', function (Blueprint $table) {
            $table->string('name', 255);
        });
    }
}
