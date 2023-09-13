<?php namespace Evg\Teamdev\Updates;

use Schema;
use Winter\Storm\Database\Schema\Blueprint;
use Winter\Storm\Database\Updates\Migration;

class BuilderTableUpdateEvgTeamdevDeveloperTeam extends Migration
{
    public function up()
    {
        Schema::rename('evg_teamdev_developer_teams', 'evg_teamdev_developer_team');
        Schema::table('evg_teamdev_developer_team', function (Blueprint $table) {
            $table->dropColumn('created_at');
            $table->dropColumn('updated_at');
            $table->dropColumn('deleted_at');
        });
    }
    
    public function down()
    {
        Schema::rename('evg_teamdev_developer_team', 'evg_teamdev_developer_teams');
        Schema::table('evg_teamdev_developer_teams', function (Blueprint $table) {
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
        });
    }
}