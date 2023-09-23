<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddQuantityReceiveIsReceiveToPurchaseDetails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('purchase_details', function (Blueprint $table) {
            $table->float('quantity_receive', 10, 0);
            $table->boolean('is_receive')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('purchase_details', function (Blueprint $table) {
            //
        });
    }
}
