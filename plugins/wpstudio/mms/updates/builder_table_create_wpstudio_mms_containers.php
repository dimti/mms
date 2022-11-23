<?php namespace Wpstudio\Mms\Updates;

use Schema;
use Winter\Storm\Database\Schema\Blueprint;
use Winter\Storm\Database\Updates\Migration;

class BuilderTableCreateWpstudioMmsContainers extends Migration
{
    public function up()
    {
        Schema::create('wpstudio_mms_containers', function (Blueprint $table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->smallInteger('code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('destination_role_id')->nullable()->unsigned();
            $table->integer('network_type_id')->nullable()->unsigned();
            $table->unsignedInteger('sort_order')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('wpstudio_mms_containers');
    }
}
