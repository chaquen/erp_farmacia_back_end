<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDetalleFacturasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('detalle_facturas', function (Blueprint $table) {
            $table->increments('id');
            $table->integer("fk_id_factura")->unsigned();
            $table->foreign('fk_id_factura')->references('id')->on('facturas');
            $table->integer("fk_id_producto")->unsigned();
            $table->foreign("fk_id_producto")->references('id')->on('productos');
            $table->integer("cantidad_producto");
            $table->integer("descuento");
            $table->decimal("valor_item");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('detalle_facturas');
    }
}
