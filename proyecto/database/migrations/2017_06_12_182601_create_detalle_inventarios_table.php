<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDetalleInventariosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('detalle_inventarios', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('fk_id_producto')->unsigned();
            $table->foreign('fk_id_producto')->references('id')->on('productos');
            $table->integer('fk_id_sede')->unsigned();
            $table->foreign('fk_id_sede')->references('id')->on('sedes');
            $table->date('fecha_caducidad');
            $table->integer('cantidad_existencias');
            $table->integer('cantidad_devueltas');
            $table->decimal('precio_venta_sede')->default('0.0');
            $table->decimal('precio_mayoreo_sede')->default('0.0');
            $table->decimal('porcentaje_ganancia')->default('0.0');
            $table->integer('minimo_invemtario_sede')->default('0');
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
        Schema::drop('detalle_inventarios');
    }
}
