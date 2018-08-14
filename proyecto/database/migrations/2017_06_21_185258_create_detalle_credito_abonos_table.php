<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDetalleCreditoAbonosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('detalle_credito_abonos', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('fk_id_credito');
            $table->foreign('fk_id_credito')->references('id')->on('creditos');
            $table->string('observacion');
            $table->decimal('abono',10,2);
            $table->datetime('fecha_abono');            
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
        Schema::drop('detalle_credito_abonos');
    }
}
