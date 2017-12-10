<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFacturasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('facturas', function (Blueprint $table) {
            $table->increments('id');
            $table->string('numero_factura')->unique();
            $table->integer('fk_id_vendedor')->unsigned();
            $table->foreign('fk_id_vendedor')->references('id')->on('users');
            $table->integer('fk_id_cliente')->unsigned();
            $table->foreign('fk_id_cliente')->references('id')->on('clientes');
            $table->enum('estado_factura',['cancelada','abortada','paga','enmora','endeuda']);
            $table->integer('fk_id_sede')->unsigned();
            $table->foreign('fk_id_sede')->references('id')->on('sedes');
            $table->string('observaciones');
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
        Schema::drop('facturas');
    }
}
