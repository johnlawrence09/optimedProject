<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnToPurchaseReceiveDetails1 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('purchase_receive_details', function (Blueprint $table) {
            $table->integer('purchase_unit_id')->nullable()->after('cost')->index('purchase_unit_id_purchase');
            $table->text('imei_number')->after('product_variant_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('purchase_receive_details', function (Blueprint $table) {
            //
        });
    }
}
