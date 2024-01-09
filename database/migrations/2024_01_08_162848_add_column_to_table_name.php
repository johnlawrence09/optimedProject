<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnToTableName extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sale_details', function (Blueprint $table) {
            $table->integer('quantity_receive')->nullable();
            $table->integer('is_receive')->nullable()->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sale_details', function (Blueprint $table) {
            $table->dropColumn('quantity_receive');
        });
    }
}
