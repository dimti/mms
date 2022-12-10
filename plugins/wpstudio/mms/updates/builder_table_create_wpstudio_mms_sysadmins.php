<?php namespace Wpstudio\Mms\Updates;

use Schema;
use Winter\Storm\Database\Schema\Blueprint;
use Winter\Storm\Database\Updates\Migration;

class BuilderTableCreateWpstudioMmsSysadmins extends Migration
{
    public function up()
    {
        Schema::create('wpstudio_mms_sysadmins', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->string('name');
            $table->string('nickname');
            $table->text('ssh_keys');
        });
    }

    public function down()
    {
        Schema::dropIfExists('wpstudio_mms_sysadmins');
    }
}
