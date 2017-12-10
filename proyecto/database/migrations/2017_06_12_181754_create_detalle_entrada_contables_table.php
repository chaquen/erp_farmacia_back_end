<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDetalleEntradaContablesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('detalle_entrada_contables', function (Blueprint $table) {
            $table->increments('id');
            $table->integer("fk_id_entrada_contable")->unsigned();
            $table->foreign('fk_id_entrada_contable')->references('id')->on('entrada_contables');
            $table->integer("fk_id_usuario")->unsigned();
            $table->foreign('fk_id_usuario')->references('id')->on('users');
            $table->integer("fk_id_sede")->unsigned();
            $table->foreign('fk_id_sede')->references('id')->on('sedes');
            $table->decimal("valor_entrada");
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
        Schema::drop('detalle_entrada_contables');
    }
}
