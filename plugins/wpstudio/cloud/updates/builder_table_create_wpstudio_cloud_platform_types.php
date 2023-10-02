<?php namespace wpstudio\cloud\Updates;

use Schema;
use Winter\Storm\Database\Schema\Blueprint;
use Winter\Storm\Database\Updates\Migration;

class BuilderTableCreateWpstudioCloudPlatformTypes extends Migration
{
    public function up()
    {
        Schema::create('wpstudio_cloud_platform_types', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('code')->unique();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('wpstudio_cloud_platform_types');
    }
}
