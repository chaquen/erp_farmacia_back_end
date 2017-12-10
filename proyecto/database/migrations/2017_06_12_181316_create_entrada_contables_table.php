<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEntradaContablesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('entrada_contables', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nombre_entrada');
            $table->string('descripcion_entrada');
            $table->decimal('maximo_valor_entrada');
            $table->enum('estado_entrada',['0','1'])->default('1');
            
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
        Schema::drop('entrada_contables');
    }
}
