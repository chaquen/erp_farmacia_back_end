<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use Mail;

use DB; 

use File;

use Storage;

use Maatwebsite\Excel\Facades\Excel;

use App\Reportes;

class PedidosController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        $datos= json_decode($request->get("datos"));
        //var_dump($datos->datos->productos_pedido);
        $arr_pedido=[];
        $ruta="";
        $i=0;
        
        $id_pedido=DB::table('pedidos')
            ->insertGetId([
                "fk_id_usuario"=>$datos->datos->fk_id_usuario,
                "fk_id_proveedor"=>$datos->datos->fk_id_proveedor,
                "fecha_pedido"=>explode(" ",$datos->hora_cliente)[0],
                "estado_pedido"=>"pendiente",
                 "created_at"=>$datos->hora_cliente,
                "updated_at"=>$datos->hora_cliente,
            ]);    
                
                
        foreach ($datos->datos->productos_pedido as $key => $value) {
            $arr_pedido[$i]=[
                "fk_id_pedido"=>$id_pedido,
                
                
                "fk_id_producto"=>$value->id,
                "cantidad_pedido"=>$value->cantidad_solicitada,
                
                "created_at"=>$datos->hora_cliente,
                "updated_at"=>$datos->hora_cliente,
            ];
            $i++;
        }
        if(count($arr_pedido)>0){
            DB::table("detalle_pedidos")
                ->insert($arr_pedido);
           
            
            if($datos->datos->tipo_exportacion=="txt"){
                //CREAR ARCHIVO PLANO
                $mi_archivo="pedidos".explode(" ",$datos->hora_cliente)[0].".txt";
                $ruta="archivos/pedidos/txt/".$mi_archivo;
                    if($datos->datos->tipo_separacion_archivo_plano==""){
                        $datos->datos->tipo_separacion_archivo_plano=" ";
                    }
                    $contenido="";
                    foreach ($datos->datos->productos_pedido as $key => $value) {
                       
                       $contenido.=trim($value->id)
                                   .$datos->datos->tipo_separacion_archivo_plano
                                   .trim($value->codigo_producto)
                                   .$datos->datos->tipo_separacion_archivo_plano
                                   .trim($value->nombre_producto)
                                   .$datos->datos->tipo_separacion_archivo_plano
                                    .trim($value->cantidad_solicitada)
                                    .$datos->datos->tipo_separacion_archivo_plano
                                    .trim($value->codigo_distribuidor).PHP_EOL;//CONSTANTE SALTO DE LINEA SEGUN EL S.O
                                    
                        
                    }
                    //echo $datos->datos->tipo_separacion_archivo_plano;
                    //var_dump($contenido);
                    File::put($ruta,$contenido);
                    
                     $rpro=["mensaje"=>"Pedido creado con exito","respuesta"=>true,"archivo"=>$ruta];
                    
                   
                
                
            }
            elseif($datos->datos->tipo_exportacion=="xls"){
                $mi_archivo="pedidos".explode(" ",$datos->hora_cliente)[0];
                $ruta2="archivos/pedidos/xls/";
                //$ruta=substr(base_path(),0,-8).$ruta.$mi_archivo;
                $ruta=$ruta2.$mi_archivo;
                $arr=[];
                $i=0;
                foreach ($datos->datos->productos_pedido as $key => $value) {
                    //var_dump($value->codigo_distribuidor);
                    $arr[$i]=["id"=>$value->id,
                            "codigo_producto"=>$value->codigo_producto,
                            "codigo_distribuidor"=>$value->codigo_distribuidor,
                            "nombre_producto"=>$value->nombre_producto,
                            "tipo_presentacion"=>$value->tipo_presentacion,
                            "cantidad_solicitada"=>$value->cantidad_solicitada];  
                    $i++;
                }
                Excel::create($mi_archivo, function($excel) use($arr){
                         // use($datos->datos->nombre_reporte)   
                        $excel->sheet('productos',function($sheet) use($arr){
                                
                                //var_dump($arr);
                           
                                $sheet->fromArray($arr);
                        });
                    })->store('xls',$ruta2 );
              $ruta.=".xls";
                    
                $rpro=["mensaje"=>"Pedido creado con exito","respuesta"=>true];
            }else{
                $rpro=["mensaje"=>"Pedido creado con exito","respuesta"=>true];
            }
              //var_dump($arr);
             
             if($datos->datos->email!=""){
                     //echo "aqui comenzo" .var_dump($datos->datos->email); 
                     
                    Mail::send("email.pedidos",["datos_pedido"=>$datos],function($msn) use($datos,$ruta){
                                $msn->from('erp@asopharma.com',"ERP ASOPHARMA");
                                $msn->to($datos->datos->email);
                                if($ruta!=""){
                                 
                                    $msn->attach($ruta);
                                }
                                
                                $msn->subject("PEDIDO GENERADO POR ERP-ASOPHARMA");
                        });
                      
                      return response()->json($rpro);  
                      //response()->download($ruta); 
                   
            }else{
                return response()->json(["mensaje"=>"Pedido creado con exito","respuesta"=>true]);
            }
                
            
        }else{
            return response()->json(["mensaje"=>"No se ha podido crear el pedido","respuesta"=>false]);
        }
        
        
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
        $pedi=DB::table('pedidos')
               ->join('detalle_pedidos','detalle_pedidos.fk_id_pedido','=','pedidos.id')
                ->join('detalle_inventarios','detalle_inventarios.id','=','detalle_pedidos.fk_id_producto')
                ->join('productos','detalle_inventarios.fk_id_producto','=','productos.id')
                ->where('pedidos.id','=',$id)
                ->get();
        if(count($pedi)>0){
            return response()->json(["mensaje"=>"ok","respuesta"=>true,"datos"=>$pedi]);
        }else{
            return response()->json(["mensaje"=>"No hay pedidos registrados para este proveedor","respuesta"=>false]);
        }
        
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
        DB::table('pedidos')
                ->where('pedidos.fk_id_proveedor','=',$id)
                ->delete();
        return response()->json(["mensaje"=>"Pedido eliminado","respuesta"=>true]);
    }
    
    public function pedido_por_proveedor($prov) {
        $pedi=DB::table('pedidos')
               //->join('detalle_pedidos','detalle_pedidos.fk_id_pedido','=','pedidos.id')
                //->join('detalle_inventarios','detalle_inventarios.id','=','detalle_pedidos.fk_id_producto')
                //->join('productos','detalle_inventarios.fk_id_producto','=','productos.id')
                ->where('pedidos.fk_id_proveedor','=',$prov)
                ->get();
        $mis_pedidos=[];
        $i=0;
        foreach ($pedi as $key => $value) {
            $mis_pedidos[$i]=(array)$value;
            $mis_pedidos[$i]["pedido"]=(array)DB::table('pedidos')
               ->join('detalle_pedidos','detalle_pedidos.fk_id_pedido','=','pedidos.id')
                ->join('detalle_inventarios','detalle_inventarios.id','=','detalle_pedidos.fk_id_producto')
                ->join('productos','detalle_inventarios.fk_id_producto','=','productos.id')
                ->where([['pedidos.id','=',$value->id],['pedidos.estado_pedido',"<>","cancelado"]])
                ->get();
            $i++;
        }
        if(count($pedi)>0){
            return response()->json(["mensaje"=>"ok","respuesta"=>true,"datos"=>$mis_pedidos]);
        }else{
            return response()->json(["mensaje"=>"No hay pedidos registrados para este proveedor","respuesta"=>false]);
        }
    }
    
    
    public function volver_a_generar_pedido(Request $request,$id) {
        $datos= json_decode($request->get("datos"));
        $ped=DB::table('pedidos')
                ->join("detalle_pedidos","pedidos.id","=","detalle_pedidos.fk_id_pedido")
                ->where('pedidos.id',"=",$id)
                ->get();
        $arr_ped=[];
        $i=0;
        
       
        $id_p=DB::table('pedidos')
                ->insertGetId([
                    "fk_id_usuario"=>$datos->datos->fk_id_usuario,
                    "fk_id_proveedor"=>$ped[0]->fk_id_proveedor,
                    "fecha_pedido"=>explode(" ",$datos->hora_cliente)[0],
                    "estado_pedido"=>"pendiente",
                    "created_at"=>$datos->hora_cliente,
                    "updated_at"=>$datos->hora_cliente,
                ]);
        foreach ($ped as $key => $value) {
            $arr_ped[$i]=["fk_id_pedido"=>$id_p,
                           "fk_id_producto"=> $value->fk_id_producto,
                            "cantidad_pedido"=>$value->cantidad_pedido
                        ];
            $i++;
        }
        DB::table("detalle_pedidos")
                ->insert($arr_ped);
        if($id_p!=0){
            return response()->json(["respuesta"=>true,"menssje"=>"pedido creado"]);
        }else{
             return response()->json(["respuesta"=>false,"menssje"=>"No se ha creado el pedido"]);
        }
    }
    /*FUNCION PARA CARGAR PEDIDO AL INVENTARIO*/
    public function asociar_pedido_inventario(Request $request,$id) {
        $datos= json_decode($request->get("datos"));
        $ped=DB::table('pedidos')
                ->join("detalle_pedidos","pedidos.id","=","detalle_pedidos.fk_id_pedido")
                ->join('detalle_inventarios','detalle_pedidos.fk_id_producto','=',"detalle_inventarios.id")
                ->join('productos',"detalle_inventarios.fk_id_producto","=","productos.id")
                ->where('pedidos.id',"=",$id)
                ->select('detalle_inventarios.fk_id_producto',
                        'detalle_inventarios.id',
                        'detalle_pedidos.cantidad_pedido',
                        'productos.unidades_por_caja',
                        'detalle_inventarios.cantidad_existencias',
                        'detalle_inventarios.cantidad_existencias_unidades',
                        'productos.tipo_venta_producto',
                        'pedidos.fk_id_usuario')
                ->get();
        $arr_ped=[];
        $i=0;
        
       
       //
        foreach ($ped as $key => $value) {
            DB::transaction(function() use($value,$datos){
                //var_dump($value);
                //echo "---";
                //var_dump($datos);
                DB::table('detalle_inventarios')
                   ->where("id","=",$value->fk_id_producto)
                   ->increment("cantidad_existencias",$value->cantidad_pedido);
                DB::table('detalle_inventarios')
                   ->where("id","=",$value->fk_id_producto)
                   ->increment("cantidad_existencias_unidades",$value->unidades_por_caja*$value->cantidad_pedido);
               
               //cajas

                if($value->tipo_venta_producto=="Caja"){
                    //unidades
                        
                   DB::table("movimientos_inventario")
                           ->insert(["fk_id_det_inventario"=>$value->id,
                               "habia"=>$value->cantidad_existencias,
                               "tipo"=>"ENTRADA",
                               "descripcion"=>"caja",
                               "cantidad"=>$value->cantidad_pedido,
                               "quedan"=>$value->cantidad_existencias+$value->cantidad_pedido,
                               "observaciones"=>"pedido recibido el dia ".$datos->hora_cliente,
                               "fk_id_usuario"=>$value->fk_id_usuario,
                               "created_at"=>$datos->hora_cliente,
                               "updated_at"=>$datos->hora_cliente,
                               ]);
                }  

                DB::table("movimientos_inventario")
                           ->insert(["fk_id_det_inventario"=>$value->id,
                               "habia"=>$value->cantidad_existencias_unidades,
                               "tipo"=>"ENTRADA",
                               "descripcion"=>"unidad",
                               "cantidad"=>$value->unidades_por_caja*$value->cantidad_pedido,
                               "quedan"=>$value->cantidad_existencias_unidades+$value->unidades_por_caja*$value->cantidad_pedido,
                               "observaciones"=>"pedido recibido el dia ".$datos->hora_cliente,
                               "fk_id_usuario"=>$value->fk_id_usuario,
                               "created_at"=>$datos->hora_cliente,
                               "updated_at"=>$datos->hora_cliente,
                               ]);
            });

           
           
        }
        DB::table('pedidos')
            ->where("id","=",$id)
            ->update(["estado_pedido"=>"entregado"]);
      
        return response()->json(["respuesta"=>true,"mensaje"=>"pedido asociado al inventario"]);
        
    }

    /*public function subir_pedido(Request $request){
           $datos= json_decode($request->get("datos"));     
           $file=$request->file('miArchivo');  
           $destinationPath="../archivos/pedidos/txt/";
           $filename=$file->getClientOriginalName();
           var_dump($filename);
           //toca subir archivo excel
           if($file->move($destinationPath,$filename)){

            $datos=File::get($destinationPath.$filename);
            var_dump($datos); 
            var_dump(explode(" ", $datos)); 
           }else{
             echo " paila de mover el arhvo";
           }
           
           
    }*/
    public function subir_pedido(Request $request){
        //ruta del archivo
        $des="../archivos/pedidos/xls/";
       //adtos de la peticion
        $datos= json_decode($request->get("datos"));             
        //arrivo
        $file=$request->file('miArchivo');  

        $filename=$file->getClientOriginalName();
         $ruta=trim($des).$filename;    
        
          if($file->move($des,$filename)){
                Excel::load($ruta,function($reader)use($datos,$ruta){
                                                       
                    $arr=$reader->toArray();
                    
                    foreach ($arr as $key => $value) {
                        

                        //busacr producto e ingresar numero de unidades sueltas
                        //unidades ingresadas por unidades por blister
                            $pro=DB::table("productos")
                                ->join("detalle_inventarios","detalle_inventarios.fk_id_producto","=","productos.id")
                                ->join("sedes","detalle_inventarios.fk_id_sede","=","sedes.id")
                                ->select("productos.id as id_producto","detalle_inventarios.id as id_inventario","productos.unidades_por_caja","productos.unidades_por_blister","sedes.id")
                                ->where([
                                            [
                                                "detalle_inventarios.id","=",$value["id"]
                                            ],
                                            [
                                                "sedes.codigo_sede","=",$value["codigo_sede"]
                                            ]
                                    ])
                                ->get();
                                if(count($pro)>0){
                                    //var_dump($pro[0]);
                                    DB::table("detalle_inventarios")
                                    ->where("id","=",$pro[0]->id_inventario)
                                    ->increment("cantidad_existencias_unidades",($value["cantidad_solicitada"]*$pro[0]->unidades_por_blister)*$pro[0]->unidades_por_caja);    
                                    


                                    $di=DB::table("detalle_inventarios")
                                        ->join("productos","productos.id","=","detalle_inventarios.fk_id_producto")
                                           ->where("detalle_inventarios.id","=",$pro[0]->id_inventario)
                                       ->get();

                                    DB::table("detalle_inventarios")
                                      ->where("id","=",$pro[0]->id_inventario)
                                      ->update([
                                            "cantidad_existencias"=>floor(($di[0]->cantidad_existencias_unidades/$di[0]->unidades_por_blister)/$di[0]->unidades_por_caja),
                                            "cantidad_existencias_blister"=>floor(($di[0]->cantidad_existencias_unidades/$di[0]->unidades_por_blister)),"estado_inventario"=>"activo"]);



                                }
                              
                    }

                   
                             
      
                });

                return response()->json(["respuesta"=>true,"mensaje"=>"Pedido agregado al inventario"]);               
          }else{
            echo "Archivo ".$filename." no se ha movido :(";
          }     
         
    }
    public function pedido_automatico(){
         
        //consultar correo de los administradores
        //consultar la hora y fecha del sistema
      
        
       //
        
        $anio="2017";

             
       
        $fecha2="martes 30 noviembre 2017 22:20";
        $fecha3="2017-11-30 23:59:59";
       

        $rep=new Reportes();
        //$destinos="edgar.guzman21@gmail.com";
        $sede=DB::table("sedes")->get();
        $i=0;
         $tipo_separacion_archivo_plano=" ";
         $s="";
        foreach ($sede as $key => $value) {
            $s=$value->nombre_sede;
            $destinos=DB::table("notificaciones")
                ->where([["trabajo","=","PedidoDiario"],["fk_id_sede","=",$value->id]])
                ->select("correos")->get();

            $datos=(object)["datos"=>(object)[
                    "tipo"=>"SEDE",
                    
                    "sedes"=>$value->id,
                    
                ],
                "hora_cliente"=>$fecha3,
                
                ];
            //var_dump($datos);
            if(count($destinos)>0){
                $arr["reporte"]=(array)$rep->reporte_bajo_inventario($datos);
                var_dump($arr["reporte"]);
                $arr["sede"]=$value->nombre_sede;    

                    if($arr["reporte"]["respuesta"]){
                        //CREAR ARCHIVO TXT
                         $mi_archivo="pedidos".explode(" ",$fecha3)[0]."_AUTOMATICO.txt";
                         $ruta2_txt="https://api.asopharma.com/archivos/pedidos/txt/".$mi_archivo."_".$value->nombre_sede;
                          //$ruta2_txt="https://apierpfarmacia.mohansoft.com/archivos/pedidos/txt/".$mi_archivo."_".$value->nombre_sede;
                        $ruta_txt="archivos/pedidos/txt/".$mi_archivo."_".$value->nombre_sede;
                            
                       
                            
                            $contenido="";
                            foreach ($arr["reporte"]["datos"] as $key => $value) {
                               //var_dump($value);
                               //echo("-------");
                               $contenido.=trim($value["id"])
                                           .$tipo_separacion_archivo_plano
                                           .trim($value["codigo_producto"])
                                           .$tipo_separacion_archivo_plano
                                           .trim($value["nombre_producto"])
                                           .$tipo_separacion_archivo_plano
                                            .trim($value["minimo_inventario"])
                                            .$tipo_separacion_archivo_plano
                                            .trim($value["codigo_distribuidor"]).PHP_EOL;//CONSTANTE SALTO DE LINEA SEGUN EL S.O
                                            
                                
                            }
                            //echo $datos->datos->tipo_separacion_archivo_plano;
                            
                            File::put($ruta_txt,$contenido);
                            
                            

                        //CREAR ARCHIVO EXCEL
                        
                        $mi_archivo="pedidos".explode(" ",$fecha3)[0]."_AUTOMATICO";
                        $ruta2_xls="https://api.asopharma.com/archivos/pedidos/xls/";
                        //$ruta2_xls="https://apierpfarmacia.mohansoft.com/archivos/pedidos/xls/";
                        $ruta2="archivos/pedidos/xls/";
                      
                        $ruta_xls=$ruta2.$mi_archivo."_". $arr["sede"];
                        $arr_xls=[];
                        $i=0;
                        foreach ($arr["reporte"]["datos"] as $key => $value) {
                            //var_dump($value["codigo_distribuidor"]);
                            $arr_xls[$i]=["id"=>$value["id"],
                                    "codigo_producto"=>$value["codigo_producto"],
                                    
                                    "nombre_producto"=>$value["nombre_producto"],
                                    
                                    "cantidad_solicitada"=>$value["minimo_inventario"],
                                    "codigo_distribuidor"=>$value["codigo_distribuidor"],
                                    ];  
                            $i++;
                        }
                        Excel::create($mi_archivo, function($excel) use($arr_xls){
                                //var_dump($arr_xls);
                                $excel->sheet('productos',function($sheet) use($arr_xls){
                                        
                                     
                                   
                                        $sheet->fromArray($arr_xls);
                                });
                            })->store('xls',$ruta2 );

                      $ruta_xls.=".xls";
                      $ruta2_xls.=$mi_archivo.".xls";


                            $sed= $arr["sede"];
                            Mail::send('email.pedido_diario',["datos_pedido"=>$arr,"fecha"=>$fecha2,"anio"=>$anio,"ruta_txt"=>$ruta2_txt,"ruta_xls"=>$ruta2_xls,"sede"=>$s],function($m)use($destinos,$fecha2,$sed){
                                    var_dump($destinos[0]);
                                   $m->from("erp@asopharma.com","ERP ASOPHARMA")
                                   ->to(explode(",", $destinos[0]->correos))->subject("Reporte pedido automatizado, ".$fecha2." para la sede ".$sed);
                               });    
                        
                        
                    }   
                }
                 
               
           
            
        }
    }
}
