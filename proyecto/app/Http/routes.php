<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/
header('Access-Control-Allow-Origin: https://marketing-ic.co');
header( 'Access-Control-Allow-Headers: Authorization, Content-Type' );
header( 'Access-Control-Allow-Methods: GET,POST,PUT,DELETE,OPTIONS' );
use Illuminate\Http\Request;



Route::get('/', function () {
    //return view('welcome');
    return view('pruebas.index');
});
Route::get('/pedido_automatico', "PedidosController@pedido_automatico");
Route::get('/consulta_inicial/{id_sede}/{rol}/{fecha}', function ($id_sede,$rol,$fecha) {

    //var_dump($fecha);
    $sedes=DB::table('sedes')->get();
    $cajeros= $r=DB::table("users")
            ->join("rols","users.fk_id_rol","=","rols.id")
            //->join("detalle_cajero_sedes","detalle_cajero_sedes.fk_id_usuario","=","users.id")    
            //->join("sedes","sedes.id","=","detalle_cajero_sedes.fk_id_sede")
            //->where("sedes.id","=",$id_sede)   
            ->where("rols.nombre_rol","<>","root")
            ->select(
                'rols.nombre_rol',
                'users.nombres',
                'users.apellidos',
                'users.id',
                 'users.codigo_venta'
                //'detalle_cajero_sedes.tipo'   
                )
            ->get();
    $admin=$r=DB::table("users")
            ->join("rols","users.fk_id_rol","=","rols.id")
            ->where("rols.nombre_rol","=","admin")
            ->select(
                'rols.nombre_rol',
                'users.nombres',
                'users.apellidos',
                'users.id'
                )
            ->get();
    $roles=DB::table('rols')->get();
    $dep=DB::table('departamentos')->get();
    $pro=DB::table('proveedors')->get();
    
    $sali=DB::table('salida_contables')
        ->get(); 
    $ent=DB::table('entrada_contables')
            ->get();   
    $permisos=DB::table('permisos')
            ->join('detalle_permisos','permisos.id',"=",'detalle_permisos.fk_id_permiso')
            ->where("detalle_permisos.fk_id_rol","=",$rol)
            ->get();    
    $ent_inicial=DB::table("detalle_entrada_contables")
                ->join("entrada_contables","detalle_entrada_contables.fk_id_entrada_contable","=","entrada_contables.id")
                ->where([["detalle_entrada_contables.fecha_entrada","LIKE",$fecha." %"],["entrada_contables.nombre_entrada","=","CajaInicial"],["detalle_entrada_contables.fk_id_sede","=",$id_sede]])
                ->limit(1)
                ->get();       
        

    echo json_encode(["respuesta"=>true,
                    "mensaje"=>"","sedes"=>$sedes,
                                  "departamentos"=>$dep,
                                  "proveedores"=>$pro,
                                   "roles"=>$roles, 
                                  "cajeros"=>$cajeros,
                                    "administradores"=>$admin,
                                    "salidas"=>$sali,
                                    "entradas"=>$ent,
                                    "permisos"=>$permisos,
                                    "caja_inicial"=>$ent_inicial
                                    ]);
});


Route::resource('/clientes','ClienteController',['except'=>['create','edit']]);
Route::resource('/creditos','CreditoController',['except'=>['create','edit']]);
Route::resource('/departamentos','DepartamentosController',['except'=>['create','edit']]);
Route::resource('/usuarios','UsersController',['except'=>['create','edit']]);
Route::get('/mostrar_user_id/{id}','UsersController@buscar_user_por_id');
Route::get('/mostrar_administradores','UsersController@mostrar_administradores');
Route::get('/mostrar_cajeros/{sede}','UsersController@mostrar_cajeros');
Route::post('/login','UsersController@login');
Route::post('/recuperar_clave',"UsersController@recuperar_clave");
Route::resource('/sedes','SedesController',['except'=>['create','edit']]);
Route::delete('/eliminar_detalle_cajero_sede/{id}','SedesController@eliminar_detalle_cajero_sede');
Route::resource('/roles','RolesController',['except'=>['create','edit']]);
Route::resource('/proveedores','ProveedoresController',['except'=>['create','edit']]);
Route::resource('/productos','ProductosController',['except'=>['create','edit']]);
Route::get('/traer_proveedores','ProductosController@buscar_proveedor');
Route::resource('/impuestos','ImpuestosController',['except'=>['create','edit']]);
Route::resource('/entrada_contable','EntradaContableController',['except'=>['create','edit']]);
Route::resource('/detalle_entrada_contable','DetalleEntradaContableController',['except'=>['create','edit']]);
Route::resource('/salida_contable','SalidaContableController',['except'=>['create','edit']]);
Route::resource('/detalle_salida_contable','DetalleSalidaContableController',['except'=>['create','edit']]);
Route::resource('/facturas','FacturaController',['except'=>['create','edit']]);
Route::resource('/detalle_factura','DetalleFacturaController',['except'=>['create','edit']]);
Route::post('/facturas_del_dia','FacturaController@facturas_del_dia');


Route::resource('/permisos','PermisosController',['except'=>['create','edit']]);
Route::resource('/detalle_permisos','DetallePermisoController',['except'=>['create','edit']]);
Route::resource('/detalle_inventarios','DetalleInventarioController',['except'=>['create','edit']]);
Route::put('/detalle_inventarios_ajuste/{id}','DetalleInventarioController@detalle_inventarios_ajuste');
Route::resource('/pedidos','PedidosController',['except'=>['create','edit']]);
Route::get("/pedido_por_proveedor/{id_proveedor}","PedidosController@pedido_por_proveedor");
Route::post("/volver_a_generar_pedido/{id_pedido}","PedidosController@volver_a_generar_pedido");
Route::post('/registrar_promo/{id_producto}/{id_sede}','DetalleInventarioController@agregar_promocion');
Route::get('/consultar_promociones','DetalleInventarioController@consultar_promociones');

Route::post('/traer_productos/{pro}/{sede}',"ProductosController@traer_productos");
Route::post('/traer_productos_para_factura/{pro}/{sede}',"ProductosController@traer_productos_para_factura");
Route::get('/traer_productos_para_factura/{pro}/{sede}',"ProductosController@traer_productos_para_factura");
Route::get('/traer_productos_por_proveedor/{producto}/{proveedor}/{sede}',"ProductosController@traer_productos_por_proveedor");

Route::post('/reporte_inventario','ReportesController@reporte_inventario');
Route::post('/reporte_bajo_inventario','ReportesController@reporte_bajo_inventario');
Route::post('/reporte_movimientos_inventario','ReportesController@reporte_movimientos_inventario');
Route::post('/reporte_saldos','ReportesController@reporte_saldos');
Route::post('/reporte_ventas_por_periodo','ReportesController@reporte_ventas_por_periodo');
Route::post('/reporte_corte_diario','ReportesController@reporte_corte_diario');

Route::post("/exportar/{tipo_reporte}","ExportarController@exportar_a_xls");
Route::post("/importacion","ImportarController@importar_xls");
Route::post("/importacion_ftp","ImportarController@importar_xls_ftp");
Route::get("/importacion_ftp_get/{sede}/{id_usuario}/{nombre_archivo}/{tipo_importacion}","ImportarController@importar_xls_ftp_get");
Route::get("/mi_ftp",function(){
        $ruta=$des=substr(base_path(),0,-8)."ftp";  
        
        $arr=array();
        //Abrir directorio y listarlo
        if(is_dir($ruta)){
            if($dh=  opendir($ruta)){
                $i=0;                
               // $arr=  scandir($ruta);
                while(($archivo=  readdir($dh))!== false){
                    //var_dump($dh);
                    //var_dump($archivo);
                    if($archivo!="." && $archivo!=".."){
                        $arr[$i]=$archivo;
                        $i++;
                    }
                    
                }
                echo json_encode(["respuesta"=>true,"mensaje"=>"ok","datos"=>$arr]);
            }
            closedir($dh);
        }else{
            echo json_encode(["respuesta"=>false,"mensaje"=>"ruta no existe"]);
        }
});
Route::post("/notificaciones","NotificacionesController@notificaciones");
Route::get("/notificaciones/{id_sede}","NotificacionesController@buscar_notificaciones");
Route::put("/editar_perfil/{ID}","UsersController@editar_perfil");
Route::post("/asociar_pedido_inventario/{id_pedido}","PedidosController@asociar_pedido_inventario");
Route::post("/subir_pedido","PedidosController@subir_pedido");
//Route::post("/actualizar_unidades_reservadas","ProductosController@actualizar_unidades_reservadas");
Route::put("/eliminar_ticket/{id}","DetalleFacturaController@destroy");
Route::get("/obtener_tickets_pendientes/{id_sede}/{id_usuario}/{numero_factura}","FacturaController@obtener_tickets_pendientes");
Route::post("/registro_facturas","FacturaController@registro_facturas");
Route::post("/editar_informacion","ProductosController@editar_informacion");
Route::post("/productos_inventario","ProductosController@crear_productos_inventario");
Route::get("/actualizar_precio_compra",function(){
    $p=DB::table("productos")
        ->get();
        foreach ($p as $key => $value) {
            
            echo "==========================\n";
            /*echo $value->codigo_producto."\n";
            echo $value->precio_compra."\n";
            echo $value->precio_compra_blister."\n";
            echo $value->precio_compra_unidad."\n";
            echo $value->unidades_por_caja."\n";
            echo $value->unidades_por_blister."\n";*/
            echo $value->id ;
            echo "<br>"; 
            echo $value->precio_compra/$value->unidades_por_caja;
            echo "<br>"; 
            echo ($value->precio_compra/$value->unidades_por_caja)/$value->unidades_por_blister; 
            echo "<br>"; 
            echo "==========================\n";
            
            DB::table("productos")
                ->where("id","=",$value->id)
                ->update(["precio_compra_blister"=>$value->precio_compra/$value->unidades_por_caja,
                    "precio_compra_unidad"=>($value->precio_compra/$value->unidades_por_caja)/$value->unidades_por_blister]);
            
        }
});
Route::get("/actualizar_cantidades_existencias",function(){
    $p=DB::table("detalle_inventarios")
           ->join("productos","productos.id","=","detalle_inventarios.fk_id_producto") 
           ->select("productos.unidades_por_caja",
                    "productos.unidades_por_blister",
                    "detalle_inventarios.cantidad_existencias_unidades",
                    "detalle_inventarios.cantidad_existencias_blister",
                    "detalle_inventarios.cantidad_existencias",
                    "detalle_inventarios.id")
           ->get();
        foreach ($p as $key => $value) {
            
            echo "==========================\n";
            /*echo $value->codigo_producto."\n";
            echo $value->precio_compra."\n";
            echo $value->precio_compra_blister."\n";
            echo $value->precio_compra_unidad."\n";
            echo $value->unidades_por_caja."\n";
            echo $value->unidades_por_blister."\n";*/
            echo $value->id ;
            echo "<br>"; 
            echo floor($value->cantidad_existencias_unidades/$value->unidades_por_blister);
            echo "<br>"; 
            echo floor(floor($value->cantidad_existencias_unidades/$value->unidades_por_blister)/$value->unidades_por_caja); 
            echo "<br>"; 
            echo "==========================\n";
            
            DB::table("detalle_inventarios")
                ->where("id","=",$value->id)
                ->update([
                        "cantidad_existencias_blister"=>floor($value->cantidad_existencias_unidades/$value->unidades_por_blister),
                         "cantidad_existencias"=>floor(floor($value->cantidad_existencias_unidades/$value->unidades_por_blister)/$value->unidades_por_caja)]);
            
        }
});