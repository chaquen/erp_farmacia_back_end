<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDetalleImpuestoProductosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('detalle_impuesto_productos', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('fk_id_impuesto')->unsigned();
            $table->foreign('fk_id_impuesto')->references('id')->on('impuestos');
            $table->integer('fk_id_detalle_inventario')->unsigned();
            $table->foreign('fk_id_detalle_inventario')->references('id')->on('detalle_inventarios');
            $table->enum('estado',['1','0'])->default('0');
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
        Schema::drop('detalle_impuesto_productos');
    }
}
