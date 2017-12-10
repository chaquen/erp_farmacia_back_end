<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSalidaContablesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('salida_contables', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nombre_salida');
            $table->string('descripcion_salida');
            $table->decimal('maximo_valor_salida');
            $table->enum('estado_salida',['0','1'])->default('1');
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
        Schema::drop('salida_contables');
    }
}
