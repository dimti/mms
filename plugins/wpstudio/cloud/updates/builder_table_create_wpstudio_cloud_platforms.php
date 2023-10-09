<?php namespace wpstudio\cloud\Updates;

use Schema;
use Winter\Storm\Database\Schema\Blueprint;
use Winter\Storm\Database\Updates\Migration;

class BuilderTableCreateWpstudioCloudPlatforms2 extends Migration
{
    public function up()
    {
        Schema::create('wpstudio_cloud_platforms', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('name');
            $table->encrypted('api_key');
            $table->string('provider')->nullable();
            $table->string('region')->nullable();
            $table->integer('platform_type_id')->unsigned();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('wpstudio_cloud_platforms');
    }
}
