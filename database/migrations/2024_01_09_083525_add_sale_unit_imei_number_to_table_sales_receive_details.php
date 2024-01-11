<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSaleUnitImeiNumberToTableSalesReceiveDetails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sales_receive_details', function (Blueprint $table) {
            $table->integer('sale_unit_id')->nullable();
            $table->string('imei_number')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sales_receive_details', function (Blueprint $table) {
            //
        });
    }
}
