<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDetalleSalidaContablesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('detalle_salida_contables', function (Blueprint $table) {
            $table->increments('id');
            $table->integer("fk_id_salida_contable")->unsigned();
            $table->foreign('fk_id_salida_contable')->references('id')->on('salida_contables');
            $table->integer("fk_id_usuario")->unsigned();
            $table->foreign('fk_id_usuario')->references('id')->on('users');
            $table->integer("fk_id_sede")->unsigned();
            $table->foreign('fk_id_sede')->references('id')->on('sedes');
            $table->decimal("valor_salida");
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
        Schema::drop('detalle_salida_contables');
    }
}
