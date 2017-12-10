<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateClientesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('clientes', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nombre_cliente');
            $table->string('documento');
            $table->string('email');
            $table->string('celular');
            $table->string('telefono');
            $table->string('direccion');
            $table->enum('estado_cliente',['1','0'])->default('1');
            $table->decimal('limite_de_credito');
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
        Schema::drop('clientes');
    }
}
