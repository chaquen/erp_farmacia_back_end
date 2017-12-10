<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nombres');
            $table->string('apellidos');
            $table->string('usuario');
            $table->string('documento');
            $table->string('email')->unique();
            $table->string('password');
            $table->integer("fk_id_rol")->unsigned();           
            $table->foreign('fk_id_rol')->references('id')->on('rols');
            $table->enum('estado_usuario',['0','1'])->default('1');
            $table->rememberToken();
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
        Schema::drop('users');
    }
}
