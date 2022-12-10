<?php namespace Wpstudio\Mms\Updates;

use Schema;
use Winter\Storm\Database\Updates\Migration;

class BuilderTableCreateWpstudioMmsClusters extends Migration
{
    public function up()
    {
        Schema::create('wpstudio_mms_clusters', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('hostname');
            $table->string('username')->nullable();
            $table->encrypted('password');
            $table->smallInteger('port')->nullable()->unsigned();
            $table->smallInteger('auth_type')->default(0);
        });
    }

    public function down()
    {
        Schema::dropIfExists('wpstudio_mms_clusters');
    }
}
