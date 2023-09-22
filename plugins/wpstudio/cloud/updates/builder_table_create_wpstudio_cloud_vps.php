<?php namespace wpstudio\cloud\Updates;

use Schema;
use Winter\Storm\Database\Schema\Blueprint;
use Winter\Storm\Database\Updates\Migration;

class BuilderTableCreateWpstudioCloudVps extends Migration
{
    public function up()
    {
        Schema::create('wpstudio_cloud_vps', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('vps_id');
            $table->string('hostname');
            $table->string('ip_address');
            $table->string('status');
            $table->integer('platform_id')->unsigned();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('wpstudio_cloud_vps');
    }
}
