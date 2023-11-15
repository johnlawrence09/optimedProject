<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSaleUnitIdToQuotationDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('quotation_details', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->integer('sale_unit_id')->nullable()->after('price')->index('sale_unit_id_quotation');
            $table->foreign('sale_unit_id', 'sale_unit_id_quotation')->references('id')->on('units')->onUpdate('NO ACTION')->onDelete('NO ACTION');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('quotation_details', function (Blueprint $table) {
            $table->dropForeign('sale_unit_id_quotation');
        });
    }
}
