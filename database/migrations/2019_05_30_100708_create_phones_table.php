<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePhonesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('phones', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('model');
            $table->string('device_pool');
            $table->enum('status', ['registered', 'unregistered', 'unknown'])->default('unknown');
            $table->json('realtime_data')->nullable();
            $table->unsignedBigInteger('ucm_id')->index();
            $table->foreign('ucm_id')
                ->references('id')
                ->on('ucms')
                ->onDelete('cascade');
            $table->timestamps();

            $table->unique(['name', 'ucm_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('phones');
    }
}
