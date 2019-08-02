<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateErasersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('erasers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('phone');
            $table->foreign('phone')
                    ->references('name')
                    ->on('phones')
                    ->onDelete('cascade');
            $table->string('ip_address')->nullable();
            $table->enum('type', ['itl'])
                    ->default('itl');
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
        Schema::dropIfExists('erasers');
    }
}
