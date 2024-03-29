<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUcmsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ucms', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->unique();
            $table->string('ip_address')->unique();
            $table->string('username');
            $table->text('password');
            $table->string('timezone')->default('US/Eastern');
            $table->string('version');
            $table->boolean('verify_peer')->default(0);
            $table->time('sync_at');
            $table->boolean('sync_schedule_enabled')->default(1);
            $table->boolean('sync_in_progress')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ucms');
    }
}
