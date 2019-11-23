<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDateToMbdTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mbd', function (Blueprint $table) {
            $table->string('date')->default('');
            $table->index(['mbd_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mbd', function (Blueprint $table) {
            //
            $table->dropColumn('date');
            $table->dropIndex('mbd_mbd_id_date_index');
        });
    }
}
