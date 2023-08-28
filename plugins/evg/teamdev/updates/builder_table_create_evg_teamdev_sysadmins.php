<?php namespace Evg\Teamdev\Updates;

use Schema;
use Winter\Storm\Database\Schema\Blueprint;
use Winter\Storm\Database\Updates\Migration;

class BuilderTableCreateEvgTeamdevSysadmins extends Migration
{
    public function up()
    {
        Schema::create('evg_teamdev_sysadmins', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->integer('developer_id')->unsigned();
            $table->text('ssh_key')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('evg_teamdev_sysadmins');
    }
}
