<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMbdUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('mbd_user', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('mbd_user_id')->comment('user id');
            $table->integer('has_article')->default(0)->comment('是否有作品');
            $table->string('date')->default('')->comment('首次有作品日期');
            $table->integer('status')->default(0);
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));

            $table->index(['mbd_user_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::dropIfExists('mbd_user');
    }
}
