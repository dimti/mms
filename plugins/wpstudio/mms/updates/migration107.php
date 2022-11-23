<?php namespace Wpstudio\Mms\Updates;

use Schema;
use Winter\Storm\Database\Schema\Blueprint;
use Winter\Storm\Database\Updates\Migration;
use Wpstudio\Mms\Models\ServerType;

class Migration107 extends Migration
{
    public function up()
    {
         Schema::table('wpstudio_mms_servers', function (Blueprint $table) {
            $table->foreign(['server_type_id'])
                ->references(['id'])
                ->on('wpstudio_mms_server_types');
         });

         Schema::table('wpstudio_mms_containers', function (Blueprint $table) {
            $table->foreign(['destination_role_id'])
                ->references(['id'])
                ->on('wpstudio_mms_destination_roles');

            $table->foreign(['network_type_id'])
                ->references(['id'])
                ->on('wpstudio_mms_network_types');
         });
    }

    public function down()
    {
        Schema::table('wpstudio_mms_servers', function (Blueprint $table) {
            $table->dropForeign(['server_type_id']);
        });

        Schema::table('wpstudio_mms_containers', function (Blueprint $table) {
            $table->dropForeign(['destination_role_id']);
            $table->dropForeign(['network_type_id']);
        });
    }
}