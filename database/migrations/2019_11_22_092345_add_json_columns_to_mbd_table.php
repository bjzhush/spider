<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddJsonColumnsToMbdTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mbd', function (Blueprint $table) {
            //
            $table->string('userid')->virtualAs('data->>"$.userid.id"');
            $table->string('category')->virtualAs('data->>"$.category"');
            $table->string('productname')->virtualAs('data->>"$.productname"');
            $table->string('productsize')->virtualAs('data->>"$.productsize"');
            $table->string('productprice')->virtualAs('data->>"$.productprice"');
            $table->string('productversion')->virtualAs('data->>"$.productversion"');
            $table->string('viewcount')->virtualAs('data->>"$.viewcount"');
            $table->string('soldcount')->virtualAs('data->>"$.soldcount"');
            $table->string('allincome')->virtualAs('data->>"$.allincome"');
            $table->string('agreevalue')->virtualAs('data->>"$.agreevalue"');
            $table->string('rank')->virtualAs('data->>"$.rank"');
            $table->string('publishtime')->virtualAs('data->>"$.publishtime"');
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
            $table->dropColumn('userid');
            $table->dropColumn('category');
            $table->dropColumn('productname');
            $table->dropColumn('productsize');
            $table->dropColumn('productprice');
            $table->dropColumn('productversion');
            $table->dropColumn('viewcount');
            $table->dropColumn('soldcount');
            $table->dropColumn('allincome');
            $table->dropColumn('agreevalue');
            $table->dropColumn('rank');
            $table->dropColumn('publishtime');
        });
    }
}
