<?php

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|*/

$factory->define(App\User::class, function(Faker\Generator $faker){
	$cc=$faker->randomNumber;
	return [
		'nombres'=>$faker->name,
		'apellidos'=>$faker->lastName,
		'usuario'=>$cc,
		'documento'=>$cc,
		'email'=>$faker->email,
		'password'=>bcrypt('123'),
		'fk_id_rol'=>$faker->numberBetween(1,5),
	];
});
//Rol super admin
$factory->defineAs(App\Rol::class,'super_admin',function(Faker\Generator $faker ){
	return [
	'nombre_rol'=>"super_admin",
	'descripcion_rol'=>$faker->sentence];
});
//Rol Admin
$factory->defineAs(App\Rol::class,'admin',function(Faker\Generator $faker ){
	return [
	'nombre_rol'=>"admin",
	'descripcion_rol'=>$faker->sentence];
});
//Rol Vendedor
$factory->defineAs(App\Rol::class,'vendedor',function(Faker\Generator $faker ){
	return [
	'nombre_rol'=>"vendedor",
	'descripcion_rol'=>$faker->sentence];
});
//Rol Domiciliario
$factory->defineAs(App\Rol::class,'domiciliario',function(Faker\Generator $faker ){
	return [
	'nombre_rol'=>"domiciliario",
	'descripcion_rol'=>$faker->sentence];
});
//departamentos
$factory->define(App\Departamento::class, function (Faker\Generator $faker) {
	
    return [

        'nombre_departamento' => $faker->name,
        
    ];
});
//Productos
$factory->defineAs(App\Producto::class,'granel', function (Faker\Generator $faker) {
	
    return [
		'codigo_producto' => $faker->isbn13,
        'nombre_producto'=>$faker->name,
        'descripcion_producto'=>$faker->sentence,
        'tipo_venta_producto'=>'AGranel',
        'precio_compra'=>$faker->randomFloat(NULL,1000,NULL),
        'precio_venta'=>$faker->randomFloat(NULL,1300,NULL),
        'precio_mayoreo'=>$faker->randomFloat(NULL,900,NULL),
        'porcentaje_ganancia'=>$faker->numberBetween(10,30),
        'minimo_inventario'=>$faker->numberBetween(5,15),
        'fk_id_departamento'=>$faker->numberBetween(1,20),
        
    ];
});
$factory->defineAs(App\Producto::class,'unidad', function (Faker\Generator $faker) {
	
    return [

        'codigo_producto' => $faker->isbn13,
        'nombre_producto'=>$faker->name,
        'descripcion_producto'=>$faker->sentence,
        'tipo_venta_producto'=>'PorUnidad',
        'precio_compra'=>$faker->randomFloat(NULL,1000,NULL),
        'precio_venta'=>$faker->randomFloat(NULL,1300,NULL),
        'precio_mayoreo'=>$faker->randomFloat(NULL,900,NULL),
        'porcentaje_ganancia'=>$faker->numberBetween(10,30),
        'minimo_inventario'=>$faker->numberBetween(5,15),
        'fk_id_departamento'=>$faker->numberBetween(1,20),
        
    ];
});
$factory->defineAs(App\Producto::class,'kit', function (Faker\Generator $faker) {
	
    return [

        'codigo_producto' => $faker->isbn13,
        'nombre_producto'=>$faker->name,
        'descripcion_producto'=>$faker->sentence,
        'tipo_venta_producto'=>'Kit',
        'precio_compra'=>$faker->randomFloat(NULL,1000,NULL),
        'precio_venta'=>$faker->randomFloat(NULL,1300,NULL),
        'precio_mayoreo'=>$faker->randomFloat(NULL,900,NULL),
        'porcentaje_ganancia'=>$faker->numberBetween(10,30),
        'minimo_inventario'=>$faker->numberBetween(5,15),
        'fk_id_departamento'=>$faker->numberBetween(1,20),

        
    ];
});
//proveedor
$factory->define(App\Proveedor::class, function (Faker\Generator $faker) {
	
    return [

        'nombre_proveedor' => $faker->name,
        'nit'=>$faker->randomNumber,
        'nombre_contacto_proveedor'=>$faker->name,
        'telefono_contacto_proveedor'=>$faker->phoneNumber,
        'direccion_contacto_proveedor'=>$faker->address,
        'email_contacto_proveedor'=>$faker->email,

    ];
});
//Cliente
$factory->define(App\Cliente::class, function (Faker\Generator $faker) {
	
    return [

        'nombre_cliente' => $faker->name,
        'documento'=>$faker->randomNumber,
        'email'=>$faker->email,
        'celular'=>$faker->phoneNumber,
        'telefono'=>$faker->tollFreePhoneNumber,
        'direccion'=>$faker->address,

    ];
});
//sedes
$factory->define(App\Sede::class, function (Faker\Generator $faker) {
	
    return [

        'nombre_sede' => $faker->name,
        'direccion_sede'=>$faker->address,
        'telefono_sede'=>$faker->phoneNumber,
        'horario'=>json_encode(
        			array(
        				array("L","8-5"),
        				array("M","8-5"),
        				array("M","8-5"),
        				array("J","8-5"),
        				array("V","8-5"),
        				array("S","8-2"),
        				array("D","8-5"),
        				array("F","9-4")
        			)
        	),
        'fk_id_administrador'=>1
    ];
});
//impuestos
$factory->define(App\Impuesto::class, function(Faker\Generator $faker){
	return [
	'nombre_impuesto'=>'Impuesto '.$faker->randomDigit."".$faker->randomLetter,
	'valor_impuesto'=>$faker->randomDigit
	];
});
//detalle impuestos
$factory->define(App\DetalleImpuestoProducto::class, function(Faker\Generator $faker){
	return [
		'fk_id_impuesto'=>$faker->numberBetween(1,5),
		'fk_id_detalle_inventario'=>$faker->numberBetween(1,5)
	];
});
//ENTRADAS CONTABLES
$factory->define(App\EntradaContable::class, function(Faker\Generator $faker){
	return [
	'nombre_entrada'=>$faker->word,
	'descripcion_entrada'=>$faker->text,
	'maximo_valor_entrada'=>$faker->randomNumber];
});
//detalle entrada contable
$factory->define(App\DetalleEntradaContable::class,function(Faker\Generator $faker){

	return [
		'fk_id_entrada_contable'=>$faker->numberBetween(1,5),
		'fk_id_usuario'=>$faker->numberBetween(1,5),
		'valor_entrada'=>$faker->randomNumber,

	];
});
//salidas contables
$factory->define(App\SalidaContable::class, function(Faker\Generator $faker){
	return [
	'nombre_salida'=>$faker->word,
	'descripcion_salida'=>$faker->text,
	'maximo_valor_salida'=>$faker->randomNumber];
});
//detalle de salida contable
$factory->define(App\DetalleSalidaContable::class,function(Faker\Generator $faker){
	return [
		'fk_id_salida_contable'=>$faker->numberBetween(1,5),
		'fk_id_usuario'=>$faker->numberBetween(1,5),
		'valor_salida'=>$faker->randomNumber,

	];
});
//permisos
$factory->define(App\Permisos::class, function(Faker\Generator $faker){
	return [
	'nombre_permiso'=>$faker->word,
	'descripcion_permiso'=>$faker->text(20),
	];
});
//detalle permiso
$factory->define(App\DetallePermiso::class, function(Faker\Generator $faker){
	return [
		'fk_id_rol'=>$faker->numberBetween(1,5),
		'fk_id_permiso'=>$faker->numberBetween(1,50),
		'consultar'=>$faker->numberBetween(0,1),
		'editar'=>$faker->numberBetween(0,1),
		'crear'=>$faker->numberBetween(0,1),
		'eliminar'=>$faker->numberBetween(0,1)
	];
});
//detalle de inventario
$factory->define(App\DetalleInventario::class, function(Faker\Generator $faker){
	return  [
		'fk_id_producto'=>$faker->numberBetween(1,20),
		'fk_id_sede'=>$faker->numberBetween(1,5),
		'fecha_caducidad'=>$faker->date,
		'cantidad_existencias'=>$faker->numberBetween(1,500),
		'cantidad_devueltas'=>$faker->numberBetween(1,5),
	];
});
//facturas 
$factory->define(App\Factura::class, function(Faker\Generator $faker){
	return  [
		'numero_factura'=>$faker->randomNumber,
		'fk_id_sede'=>$faker->numberBetween(1,5),
		'fk_id_vendedor'=>$faker->numberBetween(1,2),
		'fk_id_cliente'=>$faker->numberBetween(1,5)
	];
});
//detalle factura
$factory->define(App\DetalleFactura::class, function(Faker\Generator $faker){
	$cant=$faker->numberBetween(10,50);
	$pre=$faker->numberBetween(1000,500000);
	return [
		'fk_id_factura'=>$faker->numberBetween(1,5),
		'fk_id_producto'=>$faker->numberBetween(1,5),
		'cantidad_producto'=>$cant,
		'descuento'=>0,
		'valor_item'=>$cant*$pre,
	];
});
//detalle entrada producto proveedor
$factory->define(App\DetalleEntradaProductoProveedors::class, function(){
	return [
		'fk_id_proveedor'=>$faker->numberBetween(1,5),
		'fk_id_detalle_inventario'=>$faker->numberBetween(1,20),
		'cantidad_entrada'=>$faker->numberBetween(1,20),
		'fecha_caducidad'=>$faker->date,
		'Observaciones'=>$faker->sentence,

	];
});

