<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBgImageHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bg_image_histories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('requested_by');
            $table->string('phone');
            $table->foreign('phone')
                ->references('name')
                ->on('phones')
                ->onDelete('cascade');
            $table->string('ip_address')->nullable();
            $table->string('image');
            $table->enum('status', ['in_progress', 'finished'])
                ->default('in_progress');
            $table->enum('result', ['success', 'fail']);
            $table->string('fail_reason')->nullable();
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
        Schema::dropIfExists('bg_image_histories');
    }
}
