<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDetalleEntradaProductoProveedorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('detalle_entrada_producto_proveedors', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('fk_id_proveedor')->unsigned();
            $table->foreign('fk_id_proveedor')->references('id')->on('proveedors');
            $table->integer('fk_id_det_inventario')->unsigned();
            $table->foreign('fk_id_det_inventario')->references('id')->on('detalle_inventarios');
            $table->integer('cantidad_entrada');
            $table->date('fecha_caducidad');
            $table->string('Observaciones');
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
        Schema::drop('detalle_entrada_producto_proveedors');
    }
}
