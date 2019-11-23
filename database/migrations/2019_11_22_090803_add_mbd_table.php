<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMbdTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mbd', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('mbd_id');
            $table->text('data');
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->string('date')->default('');

            $table->string('userid')->default('');
            $table->string('category')->default('');
            $table->string('productname')->default('');
            $table->string('productsize')->default('');
            $table->string('productprice')->default('');
            $table->string('productversion')->default('');
            $table->string('viewcount')->default('');
            $table->string('soldcount')->default('');
            $table->string('allincome')->default('');
            $table->string('agreevalue')->default('');
            $table->string('rank')->default('');
            $table->string('publishtime')->default('');

            $table->integer('status')->default(0);

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
        Schema::dropIfExists('mbd');
    }
}
