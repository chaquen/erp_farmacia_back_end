<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCreditosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('creditos', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('fk_id_cliente')->unsigned();
             $table->foreign('fk_id_cliente')->references('id')->on('clientes');
            $table->integer('fk_id_factura')->unsigned();
             $table->foreign('fk_id_factura')->references('id')->on('facturas');
            $table->enum('estado_credito',['pendiente','cancelado','en mora']);
            $table->timestamps();
            $table->decimal('valor_credito',8,2);
            $table->decimal('valor_actual_credito',10,2);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('creditos');
    }
}
