<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('productos', function (Blueprint $table) {
            $table->increments('id');
            $table->string("codigo_producto")->unique();
            $table->string('nombre_producto');
            $table->string('descripcion_producto');
            $table->enum('tipo_venta_producto',['PorUnidad','AGranel','Kit','Caja']);
            $table->string('unidades_por_caja');
            $table->decimal('precio_compra');
            $table->decimal('precio_venta');
            $table->decimal('precio_mayoreo');
            $table->integer('porcentaje_ganancia');
            $table->integer('minimo_inventario');
            $table->integer('maximo_inventario');
            $table->integer('fk_id_departamento')->unsigned();
            $table->foreign('fk_id_departamento')->references('id')->on('departamentos');
            $table->enum('estado_producto',['0','1'])->default('1');
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
        Schema::drop('productos');
    }
}
