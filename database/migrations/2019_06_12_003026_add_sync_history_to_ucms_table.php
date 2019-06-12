<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSyncHistoryToUcmsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ucms', function (Blueprint $table) {
            $table->json('sync_history')
                ->after('sync_in_progress')
                ->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ucms', function (Blueprint $table) {
            $table->dropColumn('sync_history');
        });
    }
}
