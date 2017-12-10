<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDetalleCajeroSedesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('detalle_cajero_sedes', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('fk_id_usuario')->unsigned();
            $table->integer('fk_id_sede')->unsigned();
            $table->enum('tipo',['administrador','cajero','domiciliario']);
            $table->enum('estado',['0','1'])->default('0');
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
        Schema::drop('detalle_cajero_sedes');
    }
}
