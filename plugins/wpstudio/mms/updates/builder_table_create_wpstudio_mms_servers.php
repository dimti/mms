<?php namespace Wpstudio\Mms\Updates;

use Schema;
use Winter\Storm\Database\Schema\Blueprint;
use Winter\Storm\Database\Updates\Migration;

class BuilderTableCreateWpstudioMmsServers extends Migration
{
    public function up()
    {
        Schema::create('wpstudio_mms_servers', function (Blueprint $table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->string('code')->unique();
            $table->string('name');
            $table->integer('server_type_id')->unsigned();
            $table->string('main_ip_address');
            $table->text('additional_ip_addresses')->nullable();
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('wpstudio_mms_servers');
    }
}
