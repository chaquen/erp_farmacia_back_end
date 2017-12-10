<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // $this->call(UsersTableSeeder::class);
        //factory(App\Rol::class,"admin",1)->create();
        //factory(App\Rol::class,"vendedor",1)->create();
        //factory(App\Rol::class,"domiciliario",1)->create();
        //factory(App\Rol::class,"super_admin",1)->create();
        //factory(App\Rol::class,"admin",1)->create();
        //factory(App\User::class,10)->create();
        //factory(App\Departamento::class,20)->create();
        //factory(App\Producto::class,'granel',5000)->create();
        //factory(App\Producto::class,'kit',5000)->create();
        //factory(App\Producto::class,'unidad',5000)->create();
        //factory(App\Proveedor::class,50)->create();
        //factory(App\Cliente::class,1000)->create();
        //factory(App\Permisos::class,50)->create();
        //factory(App\DetallePermiso::class,250)->create();
        //factory(App\Sede::class,50)->create();
        factory(App\Impuesto::class,10)->create();
    }
}
