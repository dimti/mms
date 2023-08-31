<?php namespace Wpstudio\Mms\Updates;

use Schema;
use Winter\Storm\Database\Schema\Blueprint;
use Winter\Storm\Database\Updates\Migration;

class Migration200 extends Migration
{
    public function up()
    {
        Schema::create('wpstudio_mms_operating_systems', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();

            $table->string('name')->unique();
            $table->string('code')->unique();
            $table->string('version');
            $table->string('version_code_name')->nullable();
            $table->text('scripts')->nullable();
        });

        Schema::create('wpstudio_mms_buckets', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();

            $table->string('name')->unique();
            $table->string('code')->unique();
            $table->integer('operating_system_id')->index()->nullable()->unsigned();
            $table->text('description')->nullable();
            $table->foreign('operating_system_id')->references('id')->on('wpstudio_mms_operating_systems');
        });

        Schema::create('wpstudio_mms_programs', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();

            $table->string('name')->unique();
            $table->string('code')->unique();
            $table->text('scripts')->nullable();
        });

        Schema::create('wpstudio_mms_extensions', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();

            $table->unsignedInteger('program_id');

            $table->string('name')->index()->unique();
            $table->text('scripts')->nullable();
            $table->foreign('program_id')->references('id')->on('wpstudio_mms_programs');
        });

        Schema::create('wpstudio_mms_versions', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();

            $table->string('version');

            $table->string('versionable_type');
            $table->unsignedInteger('versionable_id')->unsigned();

            $table->text('scripts')->nullable();
            $table->foreign('versionable_id')->references('id')->on('wpstudio_mms_programs');
            $table->foreign('versionable_id')->references('id')->on('wpstudio_mms_extensions');
        });

        Schema::create('wpstudio_mms_bucket_program', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();

            $table->unsignedInteger('bucket_id')->index();
            $table->unsignedInteger('program_id')->index();
            $table->unsignedInteger('version_id')->index()->nullable();

            $table->foreign('bucket_id')->references('id')->on('wpstudio_mms_buckets');
            $table->foreign('program_id')->references('id')->on('wpstudio_mms_programs');
            $table->foreign('version_id')->references('id')->on('wpstudio_mms_versions');
        });

        Schema::create('wpstudio_mms_bucket_program_extension', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();

            $table->unsignedInteger('buckets_program_id');
            $table->unsignedInteger('extension_id');
            $table->unsignedInteger('version_id')->nullable();

            $table->foreign('buckets_program_id')->references('id')->on('buckets_programs');
            $table->foreign('extension_id')->references('id')->on('extensions');
            $table->foreign('version_id')->references('id')->on('versions');
        });
    }

    public function down()
    {
        Schema::table('wpstudio_mms_table', function (Blueprint $table) {

        });
    }
}
