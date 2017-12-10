<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProveedorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('proveedors', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nombre_proveedor');
            $table->string('nit');
            $table->string('nombre_contacto_proveedor');
            $table->string('telefono_contacto_proveedor');
            $table->string('direccion_contacto_proveedor');
            $table->string('email_contacto_proveedor');
            $table->enum('estado_proveedor',['1','0'])->default('1');
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
        Schema::drop('proveedors');
    }
}
