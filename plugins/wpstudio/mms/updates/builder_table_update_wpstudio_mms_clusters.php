<?php namespace Wpstudio\Mms\Updates;

use Schema;
use Winter\Storm\Database\Schema\Blueprint;
use Winter\Storm\Database\Updates\Migration;

class BuilderTableUpdateWpstudioMmsClusters extends Migration
{
    public function up()
    {
        Schema::table('wpstudio_mms_clusters', function (Blueprint $table) {
            $table->text('cluster_status')->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('wpstudio_mms_clusters', function (Blueprint $table) {
            $table->dropColumn('cluster_status');
        });
    }
}
