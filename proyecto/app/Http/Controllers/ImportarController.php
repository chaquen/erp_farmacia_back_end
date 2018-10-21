<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;

use App\Http\Requests;

use Maatwebsite\Excel\Facades\Excel;

use DateTimeZone; 

use Jenssegers\Date\Date;


use DB;




class ImportarController extends Controller
{
    //
    
   
    public function importar_xls_ftp(Request $request) {
        
        //$des=substr(base_path(),0,-8).trim("archivos/sftp/ ");  
        $des=substr(base_path(),0,-8).trim("ftp/ ");//en el servidor funcion con el '/'  
        
        $datos=json_decode($request->get("datos"));
        $ruta=trim($des).$datos->datos->nombre_archivo;
        //echo $ruta; 
        if(file_exists($ruta)){
               
            Excel::load($ruta,function($reader)use($datos,$ruta){
                                                   
                $arr=$reader->toArray();
                //var_dump($arr);
                switch ($datos->datos->tipo_importacion) {
                       case "productos":
                                       
                                       
                                        ini_set('max_execution_time', 6000);
                                         //900 seconds = 5 minutes
                                        //linea para impedir error de memoria
                                        ini_set('memory_limit', '-1'); 
                                        
                                        switch($datos->datos->sede){
                                                    case 0:

                                                        $ultimo_id;

                                                        $mis_productos=DB::table('productos')
                                                                          ->get();
                                                        
                                                        $mis_departamentos=DB::table('departamentos')
                                                                            ->get();
                                                        $mis_proveedores=DB::table('proveedors')
                                                                            ->get();
                                                        $mis_sedes=DB::table("sedes")
                                                                    ->get();
                                                         //var_dump($mis_productos[0]);


                                                        //VARIABLES LOCALES
                                                        $existe_en_db=false;  //EL REGISTRO EXISTE EN LA BASE DE DATOS  
                                                        $arr_sin_coincidencias_en_bd=[];//REGISTROS PARA INSERTAR
                                                        $cat_econtrada=false;//PRODUCTO PERTENECE A CATEGORIA 
                                                        $cod_repetidos=[];//CODIGOS REPETIDOS EN ARCHIVO
                                                        $arr_con_coincidencias_en_bd=[];//REGISTRS QUEE STAN EN LA BASE DE DATOS
                                                        $arr_sin_cate=[];//ARRAY QUE CONTIENE PRODUCTOS SIN CATEGORIA                                          
                                                        $i=0;
                                                        $repe=0;
                                                        $sin_cate=0;
                                                        $validar_existencia=false;//EXISTE EN BASE DE DATOS
                                                        //echo "<>".count($mis_productos)."<>";
                                                          if(count($mis_productos)==0){

                                                                $validar_existencia=false;
                                                                  $existe_en_db=false;
                                                                  $ultimo_id=1;
                                                          }else{
                                                               $ultimo_id=DB::table("productos")->orderby("id","DESC")->limit("1")->get()[0]->id+1;
                                                               $validar_existencia=true;
                                                          }  
                                                        //echo "%".$ultimo_id."%";
                                                        
                                                          
                                                        $div_l=ceil(count($arr)/4);
                                                         
                                                         
                                                        $lote_productos_excel = array_chunk($arr, $div_l);
                                                        //var_dump(count($lote_productos_excel));
                                                        $lpx=array();
                                                        $qq=0;
                                                        $rr=0;
                                                        foreach ($lote_productos_excel as $key => $l) {
                                                               //var_dump($l); 
                                                               //var_dump(count($l)); 
                                                               //echo "=======================";
                                                              
                                                               foreach($l as $k => $v){
                                                                //echo $v["codigo_coopidrogas"]."</br>";
                                                                   if(array_key_exists("codigo_coopidrogas",$v) && $v["codigo_coopidrogas"]!=NULL){
                                                                       //var_dump($v);
                                                                       //echo "=======";
                                                                         $com=$v["codigo_coopidrogas"];
                                                                        if($v["codigo_venta_menudeo_o_unidad"]==null){

                                                                            $com2=$v["codigo_coopidrogas"];

                                                                        }else{
                                                                            $com2=$v["codigo_venta_menudeo_o_unidad"];    
                                                                        }


                                                                         
                                                                        //echo $com."\n";
                                                                        //echo $com2."\n";
                                                                        $pr=DB::table("productos")
                                                                                  ->where("codigo_producto","LIKE",$com)
                                                                                  ->orwhere("codigo_distribuidor","LIKE",$com2)
                                                                                  ->get();    
                                                                             
                                                                       
                                                                        if(count($pr)==0){
                                                                          //echo $v["codigo_coopidrogas"]."</br>";
                                                                          $lpx[$qq]=$v;  
                                                                          $pr=[];
                                                                          $qq++;
                                                                        }else{
                                                                           $arr_con_coincidencias_en_bd[$rr]=$v;
                                                                           $pr=[];
                                                                           $rr++;
                                                                        }
                                                                       
                                                                       
                                                                       
                                                                       
                                                                   }
                                                               }     

                                                        }

                                                            
                                                        foreach ($lpx as $key => $value) {
                                                                //var_dump($value["codigo_venta_menudeo_o_unidad"]);
                                                                //echo "</br>";
                                                                $registar=true;    
                                                               
                                                                 //VALIDACION DE LA EXISTENCIA DE LA CATEGORIA
                                                                 foreach ($mis_departamentos as $y => $d) {
                                                                         if( $value["departamento"]==""){
                                                                              $value["departamento"]="OTROS";
                                                                         }

                                                                         if(trim(strtoupper($d->nombre_departamento))
                                                                             == trim(strtoupper($value["departamento"]))){
                                                                             $cat_econtrada=true;
                                                                             $value["departamento"]=$d->id;
                                                                             
                                                                             break;
                                                                         }
                                                                     }

                                                                 if($cat_econtrada==false){
                                                                     $val=DB::table("departamentos")->where("nombre_departamento","LIKE","OTROS")->select("departamentos.id")->get();
                                                                     //var_dump($val[0]);
                                                                     $value["departamento"]=$val[0]->id;
                                                                         $cat_econtrada=true;
                                                                 }

                                                                 if($cat_econtrada){
                                                                     $pro_encontrado=false;
                                                                     foreach ($mis_proveedores as $y => $d) {
                                                                             if(strtoupper($d->nombre_proveedor)==strtoupper($value["proveedor"])){

                                                                                   $value["proveedor"]=$d->id;
                                                                                   $pro_encontrado=true;

                                                                                 break;
                                                                         }
                                                                     }   

                                                                     if(!$pro_encontrado){
                                                                         $val=DB::table("proveedors")->where("nombre_proveedor","LIKE","otro")->select("proveedors.id")->get();
                                                                         //var_dump($val);
                                                                          $value["proveedor"]=$val[0]->id;

                                                                     }



                                                                   if($value["codigo_venta_menudeo_o_unidad"]==NULL){
                                                                        $value["codigo_venta_menudeo_o_unidad"]=$value["codigo_coopidrogas"]; 
                                                                     }


                                                                     if($value["descripcion_farmacia"]==NULL){
                                                                        $value["descripcion_farmacia"]=$value["descripcion_producto"]; 
                                                                     }


                                                                     $cat_econtrada=false;



                                                                     if($value["laboratorio"]==NULL){
                                                                         $value["laboratorio"]="N/A";
                                                                     }

                                                                     //M => MENUDEO
                                                                     //U => UNIDAD
                                                                     //MB => MENUDEO/BLISTER   
                                                                     if($value["tipo_venta"]=="MENUDEO"){

                                                                         $value["tipo_venta"]="Caja";
                                                                     }else if($value["tipo_venta"]=="UNIDAD"){

                                                                         $value["tipo_venta"]="PorUnidad";

                                                                     }else if($value["tipo_venta"]=="MENUDEO/BLISTER"){

                                                                         $value["tipo_venta"]="CajaBlister";

                                                                     }else{

                                                                         $value["tipo_venta"]="PorUnidad";

                                                                     }

                                                                     if($value["numero_de_unidades_presentacion"]==NULL){
                                                                        $value["numero_de_unidades_presentacion"]=1; 
                                                                     }

                                                                     if($value["unidades_por_blister"]!=NULL){
                                                                         $value["unidades_por_blister"]=1;
                                                                     }

                                                                     if($value["precio_costo_unidad_blister"]==NULL){
                                                                        $registar=false;
                                                                     }
                                                                     if($value["tipo_presentacion"]==NULL){
                                                                         $value["tipo_presentacion"]="UN";
                                                                     }


                                                                     if($value["precio_costo"]==NULL){
                                                                         $registar=false;
                                                                     }else{


                                                                            $vs=explode("$",$value["precio_costo"]);
                                                                                                 //var_dump($vs);

                                                                             if(count($vs)==1){
                                                                                 $value["precio_costo"]=$vs[0];    
                                                                             }else{
                                                                                 $value["precio_costo"]=$vs[1];    
                                                                             }


                                                                     }

                                                                     if($value["precio_costo_impuesto"]==NULL){
                                                                         $registar=false;
                                                                     }else{


                                                                            $vs=explode("$",$value["precio_costo_impuesto"]);
                                                                                                 //var_dump($vs);

                                                                             if(count($vs)==1){
                                                                                 $value["precio_costo_impuesto"]=$vs[0];    
                                                                             }else{
                                                                                 $value["precio_costo_impuesto"]=$vs[1];    
                                                                             }


                                                                     }

                                                                     if($value["precio_costo_blister_impuesto"]==NULL){
                                                                         $registar=false;
                                                                     }else{


                                                                            $vs=explode("$",$value["precio_costo_blister_impuesto"]);
                                                                                                 //var_dump($vs);

                                                                             if(count($vs)==1){
                                                                                 $value["precio_costo_blister_impuesto"]=$vs[0];    
                                                                             }else{
                                                                                 $value["precio_costo_blister_impuesto"]=$vs[1];    
                                                                             }


                                                                     }
                                                                     if($value["precio_costo_blister"]==NULL){
                                                                         $registar=false;
                                                                     }else{


                                                                            $vs=explode("$",$value["precio_costo_blister"]);
                                                                                                 //var_dump($vs);

                                                                             if(count($vs)==1){
                                                                                 $value["precio_costo_blister"]=$vs[0];    
                                                                             }else{
                                                                                 $value["precio_costo_blister"]=$vs[1];    
                                                                             }


                                                                     }

                                                                     if($value["precio_costo_unidad_blister_impuesto"]==NULL){
                                                                         $registar=false;
                                                                     }else{


                                                                            $vs=explode("$",$value["precio_costo_unidad_blister_impuesto"]);
                                                                                                 //var_dump($vs);

                                                                             if(count($vs)==1){
                                                                                 $value["precio_costo_unidad_blister_impuesto"]=$vs[0];    
                                                                             }else{
                                                                                 $value["precio_costo_unidad_blister_impuesto"]=$vs[1];    
                                                                             }


                                                                     }

                                                                     if($value["precio_costo_unidad_blister"]==NULL){
                                                                         $registar=false;
                                                                     }else{


                                                                            $vs=explode("$",$value["precio_costo_unidad_blister"]);
                                                                                                 //var_dump($vs);

                                                                             if(count($vs)==1){
                                                                                 $value["precio_costo_unidad_blister"]=$vs[0];    
                                                                             }else{
                                                                                 $value["precio_costo_unidad_blister"]=$vs[1];    
                                                                             }


                                                                     }


                                                                     if($value["valor_impuesto"]==NULL){
                                                                         $value["valor_impuesto"]=0;
                                                                     }
                                                                     

                                                                     if($value["porcentaje_ganancia"]==NULL){
                                                                         $value["porcentaje_ganancia"]=0;    
                                                                     }

                                                                     if($value["porcentaje_ganancia_blister"]==NULL){
                                                                         $value["porcentaje_ganancia_blister"]=0;    
                                                                     }

                                                                      if($value["porcentaje_ganancia_unidad_blister"]==NULL){
                                                                         $value["porcentaje_ganancia_unidad_blister"]=0;    
                                                                     }

                                                                     if($value["precio_venta"]==NULL){               
                                                                        $registar=false;
                                                                     }else{
                                                                         $vs=explode("$",$value["precio_venta"]);
                                                                                             //var_dump($vs);
                                                                         if(count($vs)>=1){
                                                                             if(count($vs)==1){
                                                                                 $value["precio_venta"]=$vs[0];    
                                                                             }else{
                                                                                 $value["precio_venta"]=$vs[1];    
                                                                             }

                                                                         }
                                                                     }


                                                                     if($value["precio_venta_blister"]==NULL){               
                                                                        $registar=false;
                                                                     }else{
                                                                         $vs=explode("$",$value["precio_venta_blister"]);

                                                                         if(count($vs)>=1){
                                                                             if(count($vs)==1){
                                                                                 $value["precio_venta_blister"]=$vs[0];    
                                                                             }else{
                                                                                 $value["precio_venta_blister"]=$vs[1];    
                                                                             }

                                                                         }
                                                                     }

                                                                     if($value["precio_venta_unidad_blister"]==NULL){               
                                                                        $registar=false;
                                                                     }else{
                                                                         $vs=explode("$",$value["precio_venta_unidad_blister"]);
                                                                                             //var_dump($vs);
                                                                         if(count($vs)>=1){
                                                                             if(count($vs)==1){
                                                                                 $value["precio_venta_unidad_blister"]=$vs[0];    
                                                                             }else{
                                                                                 $value["precio_venta_unidad_blister"]=$vs[1];    
                                                                             }

                                                                         }
                                                                     }

                                                                     if($value["minimo_inventario"]==NULL){
                                                                         $value["minimo_inventario"]=0;
                                                                     }

                                                                     if($value["maximo_inventario"]==NULL){
                                                                         $value["maximo_inventario"]=0;
                                                                     }

                                                                     // validacion para el formato de precio_venta_menudeo
                                                                     //var_dump($registradosar);
                                                                     //var_dump($value["codigo_coopidrogas"]);
                                                                     if($registar && $value["codigo_coopidrogas"]!=NULL){
                                                                          //var_dump($value["codigo_coopidrogas"]);
                                                                          //echo "</  br>";  

                                                                          $arr_sin_coincidencias_en_bd[$i]=[
                                                                             "id"=>$ultimo_id++,
                                                                             "codigo_producto"=>$value["codigo_venta_menudeo_o_unidad"],
                                                                             "codigo_distribuidor"=>$value["codigo_coopidrogas"],
                                                                             "nombre_producto"=>$value["descripcion_farmacia"],
                                                                             "nombre_producto_venta"=>$value["descripcion_farmacia"],
                                                                             "descripcion_producto"=>$value["descripcion_farmacia"],
                                                                             "tipo_venta_producto"=>$value["tipo_venta"],
                                                                             "tipo_presentacion"=>$value["tipo_presentacion"],
                                                                             "unidades_por_caja"=>$value["numero_de_unidades_presentacion"],
                                                                             "unidades_por_blister"=>$value["unidades_por_blister"],
                                                                             "precio_compra"=>$value["precio_costo"],
                                                                             "precio_compra_blister"=>$value["precio_costo_blister"],
                                                                             "precio_compra_unidad"=>$value["precio_costo_unidad_blister"],
                                                                             "precio_compra_impuesto"=>$value["precio_costo_impuesto"],
                                                                             "precio_compra_blister_impuesto"=>$value["precio_costo_blister_impuesto"],
                                                                             "precio_compra_unidad_impuesto"=>$value["precio_costo_unidad_blister_impuesto"],
                                                                             "porcentaje_ganancia"=>$value["porcentaje_ganancia"],
                                                                             "porcentaje_ganancia_blister"=>$value["porcentaje_ganancia_blister"],
                                                                             "porcentaje_ganancia_unidad"=>$value["porcentaje_ganancia_unidad_blister"],
                                                                             "precio_venta"=>$value["precio_venta"],
                                                                             "precio_venta_blister"=>$value["precio_venta_blister"],
                                                                             "precio_mayoreo"=>$value["precio_venta_unidad_blister"],
                                                                             "minimo_inventario"=>$value["minimo_inventario"],
                                                                             "maximo_inventario"=>$value["maximo_inventario"],
                                                                             "fk_id_departamento"=>$value["departamento"],
                                                                             "fk_id_proveedor"=>$value["proveedor"],
                                                                             "grupo"=>$value["grupo"],
                                                                             "sub_grupo"=>$value["sub_grupo"],
                                                                             "created_at"=>$datos->datos->hora_cliente,
                                                                             "updated_at"=>$datos->datos->hora_cliente,
                                                                             "laboratorio"=>$value["laboratorio"],
                                                                             "impuesto"=>$value["valor_impuesto"],
                                                                             "estado_producto"=>0
                                                                         ];
                                                                         $i++;
                                                                         $registar=true;

                                                                     }else{

                                                                         //aqui los que no tiene precio costo y venta
                                                                     }




                                                                 }
                                                                 else{
                                                                     if($value["codigo_coopidrogas"]!=NULL){
                                                                         $arr_sin_cate[$sin_cate]=$value;
                                                                         $sin_cate++;
                                                                     }

                                                                 }
                                                                     
                                                        }
                                                            
                                                            
                                                        if(count($arr_sin_coincidencias_en_bd)>0){

                                                                    $limitStatements = DB::selectOne(
                                                                            DB::raw("SELECT @@max_prepared_stmt_count AS count")
                                                                        )->count;
                                                                    $div=count($arr_sin_coincidencias_en_bd)/2;

                                                                    $lote_productos = array_chunk($arr_sin_coincidencias_en_bd, ceil($div));
                                                                    $s="";
                                                                    foreach ($lote_productos as $key => $l_p) {
                                                                        foreach ($l_p as $key => $value) {
                                                                            try {
                                                                                //var_dump($value);
                                                                                //echo "\n"; 
                                                                                  $id=DB::table('productos')
                                                                                         ->insertGetId($value);
                                                                                  foreach ($mis_sedes as $key => $s) {
                                                                              
                                                                                    DB::table("detalle_inventarios")
                                                                                        ->insert([
                                                                                                "fk_id_producto"=>$id,
                                                                                                "fk_id_sede"=>$s->id,
                                                                                                "fecha_caducidad"=>"0000-00-00",
                                                                                                "cantidad_existencias"=>0,
                                                                                                "cantidad_existencias_unidades"=>0,
                                                                                                "cantidad_devueltas"=>0,
                                                                                                "porcentaje_ganancia_sede"=>$value["porcentaje_ganancia"],
                                                                                                "porcentaje_ganancia_blister_sede"=>$value["porcentaje_ganancia_blister"],
                                                                                                "porcentaje_ganancia_sede_unidad"=>$value["porcentaje_ganancia_unidad"],
                                                                                                "precio_venta_sede"=>$value["precio_venta"],
                                                                                                "precio_venta_blister_sede"=>$value["precio_venta_blister"],
                                                                                                "precio_mayoreo_sede"=>$value["precio_mayoreo"],
                                                                                                "minimo_inventario_sede"=>$value["minimo_inventario"],
                                                                                                "estado_inventario"=>"activo",
                                                                                                "created_at"=>$datos->datos->hora_cliente,
                                                                                                "updated_at"=>$datos->datos->hora_cliente,

                                                                                                ]);


                                                                                    }       
                                                                                $error=false;
                                                                            } catch (\Illuminate\Database\QueryException $e) {
                                                                                if($e->getCode() === '23000') {
                                                                                   $error=true;
                                                                                   //echo ":(";
                                                                                   $msn_error=$e->getMessage();
                                                                                }
                                                                            } 
                                                                        }
                                                                    } 

                                                                    $arr_dt_inv=[];
                                                                    $r=0;

                                                                   if(!$error){

                                                                        if(file_exists("archivos/exportacion/excel/productos_importados_repetidos_".explode(" ", $datos->hora_cliente)[0]).".xls"){

                                                                            $nom_arc="productos_importados_repetidos_".explode(" ", $datos->hora_cliente)[0]."_".explode(":",explode(" ", $datos->hora_cliente)[1])[2];
                                                                        }else{
                                                                             $nom_arc="productos_importados_repetidos_".explode(" ", $datos->hora_cliente)[0];   
                                                                        }      
                                                                        Excel::create($nom_arc, function($excel) use($arr_con_coincidencias_en_bd){
                                                                               
                                                                                $excel->sheet('repetidos',function($sheet) use($arr_con_coincidencias_en_bd){
                                                                                       

                                                                                        $sheet->fromArray($arr_con_coincidencias_en_bd);
                                                                                });
                                                                            })->store('xls', substr(base_path(),0,-8)."archivos/exportacion/excel");


                                                                        echo json_encode(["respuesta"=>true,"mensaje"=>"productos registrados ",
                                                                            "repetidos"=>"archivos/exportacion/excel/".$nom_arc.".xls"]);
                                                                   }else{
                                                                         echo json_encode(["respuesta"=>false,"mensaje"=>"Ha ocurrido un error \n{".$msn_error."}","error"=>$msn_error]);
                                                                   }
                                                        }else{

                                                                    if(file_exists("archivos/exportacion/excel/productos_importados_repetidos_".explode(" ", $datos->hora_cliente)[0]).".xls"){

                                                                        $nom_arc="productos_importados_repetidos_".explode(" ", $datos->hora_cliente)[0]."_".explode(":",explode(" ", $datos->hora_cliente)[1])[2];
                                                                    }else{
                                                                         $nom_arc="productos_importados_repetidos_".explode(" ", $datos->hora_cliente)[0];   
                                                                    }      
                                                                        Excel::create("productos_importados_repetidos_".explode(" ", $datos->hora_cliente)[0], function($excel) use($arr_con_coincidencias_en_bd){
                                                                            
                                                                            $excel->sheet('repetidos',function($sheet) use($arr_con_coincidencias_en_bd){
                                                                                   
                                                                                    $sheet->fromArray($arr_con_coincidencias_en_bd);
                                                                            });
                                                                        })->store('xls', substr(base_path(),0,-8)."archivos/exportacion/excel");
                                                                    echo json_encode(["respuesta"=>true,"mensaje"=>"Parece que no se ha registrado ningun producto",
                                                                                    "repetidos"=>"archivos/exportacion/excel/".$nom_arc.".xls"]);
                                                                      

                                                        }
                                                        break;
                                                    default :
                                                            // ingreso inventario a sede
                                                            $prO=DB::table('productos')
                                                                ->where("estado_producto","=","1")
                                                                ->get();



                                                            $arr_ins=[];
                                                            $i=0;
                                                            $esta=false;
                                                            $new_arr=[];
                                                            $arr_no_existe=[];
                                                            $ne=0;

                                                             if(count($arr)>0){
                                                                   foreach ($arr as $key => $value) {

                                                                           
                                                                            $dt=DB::table('productos')
                                                                                ->where("codigo_producto","=",$value["codigo_coopidrogas"])
                                                                                ->get(); 

                                                                            if(count($dt)>0){
                                                                                 //BUSCAR EN SEDE
                                                                                 $dts=DB::table("detalle_inventarios")
                                                                                         ->join("productos","productos.id","=","detalle_inventarios.fk_id_producto")
                                                                                        ->where([
                                                                                            ["productos.codigo_producto","=",$dt[0]->codigo_producto],
                                                                                            ["detalle_inventarios.fk_id_producto","=",$dt[0]->id],
                                                                                            ["detalle_inventarios.fk_id_sede","=",$datos->datos->sede]
                                                                                            ])
                                                                                        ->orwhere([
                                                                                            ["productos.codigo_distribuidor","=",$dt[0]->codigo_producto],
                                                                                            ["detalle_inventarios.fk_id_producto","=",$dt[0]->id],
                                                                                            ["detalle_inventarios.fk_id_sede","=",$datos->datos->sede]
                                                                                            ])
                                                                                         ->select("detalle_inventarios.id",
                                                                                                'detalle_inventarios.fk_id_producto',
                                                                                                 "productos.unidades_por_caja",
                                                                                                 "productos.unidades_por_blister",
                                                                                                 "detalle_inventarios.cantidad_existencias",
                                                                                                 "detalle_inventarios.cantidad_existencias_unidades",
                                                                                                 "detalle_inventarios.cantidad_existencias_blister",
                                                                                                 "productos.precio_venta",
                                                                                                 "productos.precio_venta_blister",
                                                                                                 "productos.precio_mayoreo",
                                                                                                 "productos.tipo_venta_producto")
                                                                                        ->get();

                                                                                        //VALIDAR PRECIO
                                                                                    if($dts[0]->tipo_venta_producto=="Caja" 
                                                                                            && $value["precio_venta_unidad_blister"]==null ){

                                                                                        $value["precio_venta"]=$value["precio_venta"];
                                                                                        $value["precio_venta_blister"]=$value["precio_venta"];
                                                                                        $value["precio_venta_unidad_blister"]= $value["precio_venta"];

                                                                                    }


                                                                                    if($dts[0]->tipo_venta_producto=="CajaBlister" 
                                                                                            && $value["precio_venta_unidad_blister"]== NULL 
                                                                                            && $value["precio_venta_blister"] == NULL){

                                                                                        $value["precio_venta"]=$value["precio_venta"];
                                                                                        $value["precio_venta_blister"]=$value["precio_venta"];
                                                                                        $value["precio_venta_unidad_blister"]=$value["precio_venta"];
                                                                                    }


                                                                                    if($dts[0]->tipo_venta_producto=="PorUnidad" 
                                                                                            && $value["precio_venta_unidad_blister"] == NULL){

                                                                                        $value["precio_venta"]=$value["precio_venta"];
                                                                                        $value["precio_venta_blister"]=$value["precio_venta"];
                                                                                        $value["precio_venta_unidad_blister"]=$value["precio_venta"];

                                                                                    }       
                                                                                    
                                                                                    //FIN BLOQUE VALIDAR PRECIOS
                                                                                    if($value["precio_venta"]==NULL){

                                                                                            $value["precio_venta"]=$dts[0]->precio_venta;


                                                                                     } 


                                                                                     if($value["precio_venta_blister"]==NULL){
                                                                                        
                                                                                            $value["precio_venta_blister"]=$dts[0]->precio_venta_blister;


                                                                                     }


                                                                                     if($value["precio_venta_unidad_blister"]==NULL){
                                                                                        $value["precio_venta_unidad_blister"]=$dts[0]->precio_mayoreo; 


                                                                                     }


                                                                                     //VALIDO SI EXISTEN EN LA SEDE AL COMPROBAR SI HAY O
                                                                                     // NO REGISTROS EN LA CONSULTA   
                                                                                     if(count($dts)==0){
                                                                                         
                                                                                        


                                                                                         
                                                                                         
                                                                                         $id_dt=DB::table('detalle_inventarios')
                                                                                             ->insertGetId(["fk_id_producto"=>$dts[0]->id,
                                                                                             "fk_id_sede"=>$datos->datos->sede,
                                                                                             "fecha_caducidad"=>"0000-00-00 00:00:00",
                                                                                             "cantidad_existencias"=>$value["inventario"],
                                                                                             "cantidad_existencias_blister"=>$uni_blister,   
                                                                                             "cantidad_existencias_unidades"=>$uni_sueltas,
                                                                                             "minimo_inventario_sede"=>0,
                                                                                              "precio_venta_sede"=>$value["precio_venta"],
                                                                                               "precio_venta_blister_sede"=>$value["precio_venta_blister"],  
                                                                                             "precio_mayoreo_sede"=>$value["precio_venta_unidad_blister"],   
                                                                                             "created_at"=>$datos->datos->hora_cliente,
                                                                                             "updated_at"=>$datos->datos->hora_cliente]);
                                                                                              
                                                                                              if($value["total"]!=null){
                                                                                                    DB::table("detalle_inventarios")
                                                                                                         ->where("id","=",$id_dt)
                                                                                                         ->increment("cantidad_existencias_unidades",(int)$value["total"]);
                                                                                             
                                                                                                    $queda=DB::table("detalle_inventarios")
                                                                                                                 ->join("productos","productos.id","=","detalle_inventarios.fk_id_producto")   
                                                                                                                 ->where("detalle_inventarios.id","=",$id_dt)
                                                                                                                 ->select("detalle_inventarios.id",
                                                                                                                            "productos.unidades_por_blister",
                                                                                                                            "productos.unidades_por_caja","detalle_inventarios.cantidad_existencias_unidades")
                                                                                                                 ->get();         
                                                                                                    DB::table("detalle_inventarios")
                                                                                                                 ->where("id","=",$id_dt)
                                                                                                                 ->update(["cantidad_existencias_blister"=>floor($queda[0]->cantidad_existencias_unidades/$queda[0]->unidades_por_blister),
                                                                                                                            "cantidad_existencias"=>floor(($queda[0]->cantidad_existencias_unidades/$queda[0]->unidades_por_blister)/$queda[0]->unidades_por_caja),
                                                                                                                            "estado_inventario"=>"activo",
                                                                                                                            "estado_producto_sede"=>"1"
                                                                                                                 ]);
                                                                                                       DB::table('movimientos_inventario')
                                                                                                         ->insertGetId(["fk_id_det_inventario"=>$id_dt,
                                                                                                         "habia"=>"0",
                                                                                                         "fk_id_usuario"=>$datos->datos->id_usuario,    
                                                                                                         "tipo"=>"ENTRADA",
                                                                                                         "cantidad"=>$value["inventario"],
                                                                                                         "quedan"=>$value["inventario"],
                                                                                                         "created_at"=>$datos->datos->hora_cliente,
                                                                                                         "updated_at"=>$datos->datos->hora_cliente,    
                                                                                                         "observaciones"=>"entrada inicial de importacion de productos"]);
                                                                                                       
                                                                                                }     
                                                                                         
                                                                                      }
                                                                                     else{
                                                                                        //producto ya existe actualizo existencias en sede
                                                                                        $act=[];
                                                                                       
                                                                                         if($value["unidades_por_caja"]!=null){
                                                                                              $act["unidades_por_caja"]=$value["unidades_por_caja"];
                                                                                              
                                                                                         }

                                                                                         if($value["unidades_por_blister"]!=null){
                                                                                            $act["unidades_por_blister"]=$value["unidades_por_blister"];  
                                                                                         }

                                                                                         if(count($act)>0){
                                                                                          
                                                                                            DB::table("productos")
                                                                                                ->where("id","=",$dts[0]->fk_id_producto)
                                                                                                ->update($act);
                                                                                         }
                                                                                         if($value["total"]!=null){
                                                                                                DB::table("detalle_inventarios")
                                                                                                 ->where("id","=",$dts[0]->id)
                                                                                                 ->increment("cantidad_existencias_unidades",$value["total"]); 
                                                                                                $queda=DB::table("detalle_inventarios")
                                                                                                             ->join("productos","productos.id","=","detalle_inventarios.fk_id_producto")   
                                                                                                             ->where("detalle_inventarios.id","=",$dts[0]->id)
                                                                                                             ->select("detalle_inventarios.id",
                                                                                                                        "productos.unidades_por_blister",
                                                                                                                        "productos.unidades_por_caja","detalle_inventarios.cantidad_existencias_unidades")
                                                                                                             ->get();         
                                                                                                DB::table("detalle_inventarios")
                                                                                                             ->where("id","=",$dts[0]->id)
                                                                                                             ->update(["cantidad_existencias_blister"=>floor($queda[0]->cantidad_existencias_unidades/$queda[0]->unidades_por_blister),
                                                                                                                        "cantidad_existencias"=>floor(($queda[0]->cantidad_existencias_unidades/$queda[0]->unidades_por_blister)/$queda[0]->unidades_por_caja),
                                                                                                                        "estado_inventario"=>"activo",
                                                                                                                        "estado_producto_sede"=>"1"
                                                                                                             ]);
                                                                                                 
                                                                                                
                                                                                         }
                                                                                        

                                                                                        //FIN BLOQUE DE ACTUALIZACIONES 
                                                                                    //VALIDAR PRECIO
                                                                                    if($dts[0]->tipo_venta_producto=="Caja" 
                                                                                            && $value["precio_venta_unidad_blister"]==null ){

                                                                                        $value["precio_venta"]=$value["precio_venta"];
                                                                                        $value["precio_venta_blister"]=$value["precio_venta"];
                                                                                        $value["precio_venta_unidad_blister"]= $value["precio_venta"];

                                                                                    }


                                                                                    if($dts[0]->tipo_venta_producto=="CajaBlister" 
                                                                                            && $value["precio_venta_unidad_blister"]== NULL 
                                                                                            && $value["precio_venta_blister"] == NULL){

                                                                                        $value["precio_venta"]=$value["precio_venta"];
                                                                                        $value["precio_venta_blister"]=$value["precio_venta"];
                                                                                        $value["precio_venta_unidad_blister"]=$value["precio_venta"];
                                                                                    }


                                                                                    if($dts[0]->tipo_venta_producto=="PorUnidad" 
                                                                                            && $value["precio_venta_unidad_blister"] == NULL){

                                                                                        $value["precio_venta"]=$value["precio_venta"];
                                                                                            $value["precio_venta_blister"]=$value["precio_venta"];
                                                                                        $value["precio_venta_unidad_blister"]=$value["precio_venta"];

                                                                                    }       
                                                                                    
                                                                                    //BLOQUE VALIDAR PRECIO
                                                                                    if($value["precio_venta"]==NULL){

                                                                                            $value["precio_venta"]=$dts[0]->precio_venta;

                                                                                            if($dts[0]->tipo_venta_producto=="Caja"){
                                                                                                $value["precio_venta"]=$dts[0]->precio_venta;
                                                                                            }

                                                                                            if($dts[0]->tipo_venta_producto=="CajaBlister"){
                                                                                                $value["precio_venta"]=$dts[0]->precio_venta;
                                                                                            }

                                                                                            if($dts[0]->tipo_venta_producto=="PorUnidad"){   
                                                                                                $value["precio_venta"]=$dts[0]->precio_venta;             
                                                                                            }

                                                                                    }   

                                                                                    if($value["precio_venta_blister"]==NULL){
                                                                                             $value["precio_venta_blister"]=$dts[0]->precio_venta_blister;

                                                                                             if($dts[0]->tipo_venta_producto=="Caja"){
                                                                                                $value["precio_venta_blister"]=$dts[0]->precio_venta;
                                                                                            }

                                                                                            if($dts[0]->tipo_venta_producto=="CajaBlister"){
                                                                                                $value["precio_venta_blister"]=$dts[0]->precio_venta_blister;
                                                                                            }

                                                                                            if($dts[0]->tipo_venta_producto=="PorUnidad"){   
                                                                                                $value["precio_venta_blister"]=$dts[0]->precio_venta;             
                                                                                            }
                                                                                         }

                                                                                         if($value["precio_venta_unidad_blister"]==NULL){

                                                                                            $value["precio_venta_unidad_blister"]=$dts[0]->precio_mayoreo;

                                                                                            if($dts[0]->tipo_venta_producto=="Caja"){
                                                                                                $value["precio_venta_unidad_blister"]=$dts[0]->precio_venta;
                                                                                            }

                                                                                            if($dts[0]->tipo_venta_producto=="CajaBlister"){
                                                                                                $value["precio_venta_unidad_blister"]=$dts[0]->precio_venta;
                                                                                            }

                                                                                            if($dts[0]->tipo_venta_producto=="PorUnidad"){   
                                                                                                $value["precio_venta_unidad_blister"]=$dts[0]->precio_venta;             
                                                                                            } 
                                                                                         }
                                                                                         //ACTUALIZAR CANTIDAD DE PRODUCTOS 
                                                                                         //UNIDADES BLISTER UNIDAD
                                                                                         




                                                                                         //ACTUALIZO LOS PRECIOS DEL PRODUCTO EN LA SEDE
                                                                                         DB::table("detalle_inventarios")
                                                                                                  ->where("id","=",$dts[0]->id)  
                                                                                                  ->update([
                                                                                                            "precio_venta_sede"=>$value["precio_venta"],
                                                                                                            "precio_venta_blister_sede"=>$value["precio_venta_blister"],
                                                                                                            "precio_mayoreo_sede"=>$value["precio_venta_unidad_blister"]
                                                                                                        ]);


                                                                                         if(count($dts)>0){
                                                                                                 //existen entradas
                                                                                            if($value["total"]!=null){
                                                                                                DB::table('movimientos_inventario')
                                                                                                    ->insertGetId(["fk_id_det_inventario"=>$dts[0]->id,
                                                                                                    "habia"=>$dts[0]->cantidad_existencias_unidades,
                                                                                                    "tipo"=>"ENTRADA",
                                                                                                    "descripcion"=>"unidad",    
                                                                                                    "fk_id_usuario"=>$datos->datos->id_usuario,
                                                                                                    "cantidad"=>$value["total"],                              
                                                                                                    "quedan"=>$queda[0]->cantidad_existencias_unidades,
                                                                                                    "observaciones"=>"entrada importacion de productos ",
                                                                                                    "created_at"=>$datos->datos->hora_cliente,
                                                                                                    "updated_at"=>$datos->datos->hora_cliente        ]); 
                                                                                            }
                                                                                            

                                                                                           
                                                                                         }
                                                                                         else{
                                                                                             if($value["total"]!=null){
                                                                                                DB::table('movimientos_inventario')
                                                                                                 ->insertGetId(["fk_id_det_inventario"=>$dts[0]->id,
                                                                                                 "fk_id_usuario"=>$datos->datos->id_usuario,
                                                                                                  "habia"=>"0",
                                                                                                 "tipo"=>"ENTRADA",
                                                                                                 "cantidad"=>$value["total"],
                                                                                                 "quedan"=>$value["total"],
                                                                                                 "observaciones"=>"entrada inicial de importacion de productos",
                                                                                                 "created_at"=>$datos->datos->hora_cliente,
                                                                                                 "updated_at"=>$datos->datos->hora_cliente       ]);  
                                                                                             }

                                                                                            
                                                                                           
                                                                                         }

                                                                                     }
                                                                            }
                                                                            else{
                                                                                //CODIGO NO EXISTE
                                                                                $arr_no_existe[$ne]=
                                                                                                    [
                                                                                                     "codigo_producto"=>$value["codigo_coopidrogas"],
                                                                                                    
                                                                                                     "precio_venta"=>$value["precio_venta"],
                                                                                                     "precio_venta_blister"=>$value["precio_venta_blister"],
                                                                                                     "precio_venta_unidad_blister"=>$value["precio_venta_unidad_blister"]
                                                                                                    ];

                                                                                $ne++;
                                                                            }



                                                                    }
                                                            }       
                                                                Excel::create("NoExisten", function($excel) use($arr_no_existe){

                                                                                $excel->sheet('no_existen',function($sheet) use($arr_no_existe){
                                                                                      $sheet->fromArray($arr_no_existe);
                                                                                });
                                                                            })->store('xls', substr(base_path(),0,-8)."archivos/exportacion/excel");   


                                                            echo json_encode(["mensaje"=>"productos importados","respuesta"=>true,"no_existen"=>"archivos/exportacion/excel/NoExisten"]);
                                                        break;
                                        }
                                        
                                           
                             break;
                                            
                       case "editar_producto":

                                        ini_set('max_execution_time', 60000); //900 seconds = 5 minutes
                                        //linea para impedir error de memoria
                                        ini_set('memory_limit', '-1');
                                        if(count($arr)<100){
                                            $div_l=ceil(count($arr)/10);
                                        }else{
                                            $div_l=ceil(count($arr)/100);
                                        }
                                        //echo $div_l;
                                        $lote_productos_excel = array_chunk($arr, $div_l);
                                        $editar=[];
                                        $editar_dt=[];
                                        $i=0;
                                        $repetidos=[];
                                        $lpx=[];
                                        $iii=0;
                                        foreach ($lote_productos_excel as $key => $l) {
                                            //var_dump($l);
                                            //echo "==========\n";
                                            foreach ($l as $k => $v) {
                                                if(array_key_exists("codigo_coopidrogas",$v) && $v["codigo_coopidrogas"]!==NULL){
                                                    //var_dump($v);
                                                    //echo "==========\n";
                                                
                                                    $d=DB::table("productos")
                                                        ->where("codigo_producto","LIKE",$v["codigo_coopidrogas"])
                                                        ->orwhere("codigo_distribuidor","LIKE",$v["codigo_coopidrogas"])
                                                        ->get();
                                                    
                                                    if(count($d)>0){
                                                        //var_dump($v);
                                                        //echo $v["codigo_coopidrogas"]."==========\n";
                                                        $lpx[$iii]=$v;
                                                        $iii++;
                                                    }
                                                    
                                                    
                                                    
                                                    
                                                }
                                            }
                                        }
                                        //echo $iii;
                                        //echo count($lpx);
                                        if(count($lpx)<100){
                                            $lote_para_editar = array_chunk($lpx, ceil(count($lpx)/10)); 
                                        }else if(count($lpx)<1000){
                                            $lote_para_editar = array_chunk($lpx, ceil(count($lpx)/100)); 
                                        }else if(count($lpx)<2000){
                                            $lote_para_editar = array_chunk($lpx, ceil(count($lpx)/1000)); 
                                        } else {
                                             $lote_para_editar = array_chunk($lpx, ceil(count($lpx)/2000)); 
                                        }
                                                  
                                         //var_dump($lote_para_editar);
                                        foreach ($lote_para_editar as $k => $val) {
                                            //var_dump($val);
                                            //echo "==========\n";
                                           foreach ($val as $key => $value) {
                                                        $codigo_a_editar_1=$value["codigo_coopidrogas"];

                                                        if(array_key_exists("nuevo_codigo_distribuidor",$value) && $value["nuevo_codigo_distribuidor"]!==NULL){
                                                                  $editar["codigo_distribuidor"]=$value["nuevo_codigo_distribuidor"]; 
                                                        }    

                                                        if(array_key_exists("nuevo_codigo_venta",$value) && $value["nuevo_codigo_venta"]!==NULL){
                                                                  $editar["codigo_producto"]=$value["nuevo_codigo_venta"]; 
                                                        }   



                                                        if(array_key_exists("nombre_producto",$value) && $value["nombre_producto"]!==NULL){
                                                                   $editar["nombre_producto"]=$value["nombre_producto"]; 
                                                        }

                                                        if(array_key_exists("descripcion_farmacia",$value) && $value["descripcion_farmacia"]!==NULL){
                                                               $editar["nombre_producto"]=$value["descripcion_producto"]; 
                                                               $editar["nombre_producto_venta"]=$value["descripcion_producto"];
                                                        }


                                                        if(array_key_exists("laboratorio",$value) && $value["laboratorio"]!=NULL){
                                                               $editar["laboratorio"]=$value["laboratorio"]; 
                                                        }


                                                        if(array_key_exists("tipo_venta",$value) && $value["tipo_venta"]!==NULL){
                                                               $editar["tipo_venta_producto"]=$value["tipo_venta"]; 
                                                        }


                                                        if(array_key_exists("numero_de_unidades_presentacion",$value) && $value["numero_de_unidades_presentacion"]!==NULL){

                                                               $editar["unidades_por_caja"]=$value["numero_de_unidades_presentacion"]; 



                                                        }


                                                        if(array_key_exists("unidades_por_blister",$value) && $value["unidades_por_blister"]!==NULL){

                                                               $editar["unidades_por_blister"]=$value["unidades_por_blister"];
                                                        }


                                                        if(array_key_exists("tipo_presentacion",$value) && $value["tipo_presentacion"]!==NULL){
                                                               $editar["tipo_presentacion"]=$value["tipo_presentacion"]; 
                                                        }


                                                        if(array_key_exists("precio_costo",$value) && $value["precio_costo"]!==NULL && $value["precio_costo"]!=="0"){
                                                               //var_dump($value["precio_costo"]);
                                                               $editar["precio_compra"]=$value["precio_costo"]; 
                                                        }
                                                        //var_dump($value["precio_costo_blister"]);
                                                        if(array_key_exists("precio_costo_blister",$value) && $value["precio_costo_blister"]!==NULL && $value["precio_costo_blister"]!=="#DIV/0!"){

                                                               $editar["precio_compra_blister"]=$value["precio_costo_blister"]; 
                                                        }


                                                        if(array_key_exists("precio_costo_unidad_blister",$value) && $value["precio_costo_unidad_blister"]!==NULL && $value["precio_costo_unidad_blister"]!=="#DIV/0!"){
                                                               $editar["precio_compra_unidad"]=$value["precio_costo_unidad_blister"]; 
                                                        }


                                                        if(array_key_exists("precio_venta",$value) && $value["precio_venta"]!==NULL && $value["precio_venta"]!=="#DIV/0!"){
                                                               $editar["precio_venta"]=$value["precio_venta"]; 
                                                               $editar_dt["precio_venta_sede"]=$value["precio_venta"]; 
                                                        }


                                                        if(array_key_exists("precio_venta_blister",$value) && $value["precio_venta_blister"]!==NULL && $value["precio_venta_blister"]!=="#DIV/0!"){
                                                               $editar["precio_venta_blister"]=$value["precio_venta_blister"]; 
                                                               $editar_dt["precio_venta_blister_sede"]=$value["precio_venta_blister"]; 
                                                        }


                                                        if(array_key_exists("precio_venta_unidad_blister",$value) && $value["precio_venta_unidad_blister"]!==NULL && $value["precio_venta_unidad_blister"]!=="#DIV/0!"){
                                                               $editar["precio_mayoreo"]=$value["precio_venta_unidad_blister"]; 
                                                               $editar_dt["precio_mayoreo_sede"]=$value["precio_venta_unidad_blister"]; 
                                                        }


                                                        if(array_key_exists("porcentaje_ganancia",$value) && $value["porcentaje_ganancia"]!==NULL  && $value["porcentaje_ganancia"]!=="0"){
                                                               $editar["porcentaje_ganancia"]=$value["porcentaje_ganancia"]; 
                                                               $editar_dt["porcentaje_ganancia"]=$value["porcentaje_ganancia"]; 
                                                        }


                                                        if(array_key_exists("porcentaje_ganancia_blister",$value) && $value["porcentaje_ganancia_blister"]!==NULL  && $value["porcentaje_ganancia_blister"]!=="0"){
                                                               $editar["porcentaje_ganancia_blister"]=$value["porcentaje_ganancia_blister"]; 
                                                               $editar_dt["porcentaje_ganancia_blister_sede"]=$value["porcentaje_ganancia_blister"]; 
                                                        }


                                                        if(array_key_exists("porcentaje_ganancia_unidad_blister",$value) && $value["porcentaje_ganancia_unidad_blister"]!==NULL  && $value["porcentaje_ganancia_unidad_blister"]!=="0"){
                                                               $editar["porcentaje_ganancia_unidad"]=$value["porcentaje_ganancia_unidad_blister"]; 
                                                               $editar_dt["porcentaje_ganancia_sede_unidad"]=$value["porcentaje_ganancia"]; 
                                                        }


                                                        if(array_key_exists("minimo_inventario",$value) && $value["minimo_inventario"]!==NULL){
                                                               $editar["minimo_inventario"]=$value["minimo_inventario"]; 
                                                               $editar_dt["minimo_inventario_sede"]=$value["minimo_inventario"]; 
                                                        }


                                                        if(array_key_exists("maximo_inventario",$value) && $value["maximo_inventario"]!==NULL){
                                                               $editar["maximo_inventario"]=$value["maximo_inventario"]; 
                                                        }


                                                        if(array_key_exists("grupo",$value) && $value["grupo"]!==NULL){
                                                               $editar["grupo"]=$value["grupo"]; 
                                                        }


                                                        if(array_key_exists("sub_grupo",$value) && $value["sub_grupo"]!==NULL){
                                                               $editar["sub_grupo"]=$value["sub_grupo"]; 
                                                        }


                                                        if(array_key_exists("impuesto",$value) && $value["impuesto"]!==NULL){
                                                               $editar["impuesto"]=$value["impuesto"]; 
                                                        }
                                                        //var_dump($editar);
                                                        //echo "==\n";
                                                        DB::table('productos')
                                                            ->where('codigo_distribuidor',"LIKE", $codigo_a_editar_1)
                                                            ->orwhere('codigo_producto', "LIKE",$codigo_a_editar_1)
                                                            ->update($editar);          

                                                        $editar=array();
                                                        if(count($editar_dt)>0){
                                                              
                                                              $pd=DB::table('productos')
                                                                ->where('codigo_distribuidor',"LIKE", $codigo_a_editar_1)
                                                                ->orwhere('codigo_producto', "LIKE",$codigo_a_editar_1)
                                                                ->get();
                                                              
                                                              DB::table('detalle_inventarios')
                                                                ->where('fk_id_producto',"=", $pd[0]->id)                                                                    
                                                                ->update($editar_dt);   
                                                               $editar_dt=array();
                                                        }  
                                                          $i++;  

                                            }
                                        }

                                                  

                                        

                                         if(count($repetidos)>0){
                                            Excel::create("codigos_repetidos", function($excel) use($repetidos){
                                                                                                // use($datos->datos->nombre_reporte)   
                                                                                                $excel->sheet('codigos_repetidos',function($sheet) use($repetidos){
                                                                                                       
                                                                                                        $sheet->fromArray($repetidos);
                                                                                                });
                                                                                            })->store('xls', substr(base_path(),0,-8)."archivos/exportacion/excel");
                                             echo json_encode(["respuesta"=>true,"mensaje"=>"productos editados","no_existen"=>"archivos/exportacion/excel/codigos_repetidos.xls"]);
                                         }else{
                                             
                                             echo json_encode(["respuesta"=>true,"mensaje"=>"productos editados"]);
                                         }

                                         

                                         
                                         
                                         
                                        break;                                      

                       case "ajustar_inventario_sede":

                                        if($datos->datos->sede!="0" && $datos->datos->sede!="--"){
                                            ini_set('max_execution_time', 60000); //900 seconds = 5 minutes
                                            //linea para impedir error de memoria
                                            ini_set('memory_limit', '-1'); 
                                        
                                                $prO=DB::table('productos')
                                                                ->where("estado_producto","=","1")
                                                                ->get();



                                                            $arr_ins=[];
                                                            $i=0;
                                                            $esta=false;
                                                            $new_arr=[];
                                                            $arr_no_existe=[];
                                                            $ne=0;
                                                            if(count($arr)<100){
                                                                $div_l=ceil(count($arr)/10);
                                                            }else{
                                                                $div_l=ceil(count($arr)/100);
                                                            }
                                                            //echo $div_l;
                                                            $lote_productos_excel = array_chunk($arr, $div_l);
                                                            if(count($arr)>0){
                                                                   foreach ($lote_productos_excel as $key => $valor) {
                                                                        foreach ($valor as $key => $value) {

                                                                            if(array_key_exists("codigo_producto",$value) && $value["codigo_producto"]!=NULL){
                                                                                 $dt=DB::table('productos')
                                                                                    ->where("codigo_producto","=",$value["codigo_producto"])
                                                                                    ->orwhere("codigo_distribuidor","=",$value["codigo_producto"])
                                                                                    ->get(); 

                                                                                if(count($dt)>0){
                                                                                     //BUSCAR EN SEDE
                                                                                     $dts=DB::table("detalle_inventarios")
                                                                                             ->join("productos","productos.id","=","detalle_inventarios.fk_id_producto")
                                                                                            ->where([
                                                                                                ["detalle_inventarios.fk_id_producto","=",$dt[0]->id],
                                                                                                ["detalle_inventarios.fk_id_sede","=",$datos->datos->sede]
                                                                                                ])
                                                                                             ->select("detalle_inventarios.id",
                                                                                                    'detalle_inventarios.fk_id_producto',
                                                                                                     "productos.unidades_por_caja",
                                                                                                     "productos.unidades_por_blister",
                                                                                                     "detalle_inventarios.cantidad_existencias",
                                                                                                     "detalle_inventarios.cantidad_existencias_unidades",
                                                                                                     "detalle_inventarios.cantidad_existencias_blister",
                                                                                                     "detalle_inventarios.precio_venta_sede",
                                                                                                     "detalle_inventarios.precio_venta_blister_sede",
                                                                                                     "detalle_inventarios.precio_mayoreo_sede",
                                                                                                     "productos.precio_compra",
                                                                                                     "productos.precio_compra_blister",
                                                                                                     "productos.precio_compra_unidad",
                                                                                                     "productos.precio_venta",
                                                                                                     "productos.precio_venta_blister",
                                                                                                     "productos.precio_mayoreo",
                                                                                                     "productos.tipo_venta_producto")
                                                                                            ->get();
                                                                                        
                                                                                        //DATOS A ACTUALIZAR    
                                                                                       if(count($dts)>0){
                                                                                            $act_detalle=[];
                                                                                             $act_producto=[];
                                                                                             $ia=0;

                                                                                            //valido precios venta
                                                                                            if($value["precio_venta"]!=NULL){

                                                                                                     if((int)$dts[0]->precio_venta==0){

                                                                                                         $precio=1;

                                                                                                     }else{

                                                                                                         $precio=(int)$dts[0]->precio_venta;

                                                                                                     }

                                                                                                     $act_detalle["precio_venta_sede"]=$value["precio_venta"];
                                                                                                     $DIF=(int)$value["precio_venta"]-$dts[0]->precio_compra;
                                                                                                     $act_detalle["porcentaje_ganancia_sede"]=round((($DIF)*100)/$precio,2);


                                                                                             }   

                                                                                             if($value["precio_venta_blister"]!=NULL){
                                                                                                     if((int)$dts[0]->precio_venta_blister==0){
                                                                                                         $precio_b=1;
                                                                                                     }else{
                                                                                                         $precio_b=(int)$dts[0]->precio_venta_blister;
                                                                                                     }
                                                                                                      $act_detalle["precio_venta_blister_sede"]=$value["precio_venta_blister"];
                                                                                                      $DIF=(int)$value["precio_venta_blister"]-$dts[0]->precio_compra_blister;
                                                                                                      $act_detalle["porcentaje_ganancia_blister_sede"]=round((($DIF)*100)/$precio_b,2);
                                                                                             }

                                                                                             if($value["precio_venta_unidad_blister"]!=NULL){
                                                                                                     if((int)$dts[0]->precio_mayoreo_sede==0){
                                                                                                         $precio_u=1;

                                                                                                     }else{
                                                                                                         $precio_u=(int)$dts[0]->precio_mayoreo_sede;
                                                                                                     } 

                                                                                                    $act_detalle["precio_mayoreo_sede"]=$value["precio_venta_unidad_blister"];
                                                                                                    $DIF=(int)$value["precio_venta_unidad_blister"]-$dts[0]->precio_compra_unidad;
                                                                                                    $act_detalle["porcentaje_ganancia_sede_unidad"]=round((($DIF)*100)/$precio_u,2);
                                                                                             }


                                                                                                  //ACTUALIZAR precio DE PRODUCTOS 
                                                                                             //valido precios venta    
                                                                                             if(count($act_detalle)>0){
                                                                                                     DB::table("detalle_inventarios")
                                                                                                           ->where("id","=",$dts[0]->id)  
                                                                                                           ->update($act_detalle);
                                                                                             }
                                                                                                

                                                                                              if($value["unidades_por_caja"]!=null){

                                                                                                       $act_producto["unidades_por_caja"]=$value["unidades_por_caja"];

                                                                                                       $act_producto["precio_compra_blister"]=round($dts[0]->precio_compra/$value["unidades_por_caja"],2);

                                                                                              }

                                                                                              if($value["unidades_por_blister"]!=null){

                                                                                                 $act_producto["unidades_por_blister"]=$value["unidades_por_blister"]; 

                                                                                                 $act_producto["precio_compra_unidad"]=round(($dts[0]->precio_compra/$value["unidades_por_caja"])/$value["unidades_por_blister"],2); 
                                                                                              }


                                                                                                  if(array_key_exists("precio_venta_sede",$act_detalle)){

                                                                                                     $act_producto["precio_venta"]=$act_detalle["precio_venta_sede"];

                                                                                                     $diff=(int)$act_detalle["precio_venta_sede"]-$dts[0]->precio_compra;    


                                                                                                     $act_producto["porcentaje_ganancia"]=round(($diff*100)/$act_detalle["precio_venta_sede"],2);

                                                                                                  }

                                                                                                  if(array_key_exists("precio_venta_blister_sede",$act_detalle)){

                                                                                                     $act_producto["precio_venta_blister"]=$act_detalle["precio_venta_blister_sede"];

                                                                                                     $diff=(int)$act_detalle["precio_venta_blister_sede"]-$dts[0]->precio_compra_blister;    


                                                                                                     $act_producto["porcentaje_ganancia_blister"]=round(($diff*100)/$value["precio_venta_blister"],2);

                                                                                                  }

                                                                                                  if(array_key_exists("precio_mayoreo_sede",$act_detalle)){

                                                                                                      $act_producto["precio_mayoreo"]=$act_detalle["precio_mayoreo_sede"];

                                                                                                      $diff=(int)$value["precio_venta_unidad_blister"]-$dts[0]->precio_compra_unidad;    


                                                                                                      $act_producto["porcentaje_ganancia_unidad"]=round(($diff*100)/$value["precio_venta_unidad_blister"],2);
                                                                                                  }

                                                                                                  if(count($act_producto)>0){
                                                                                                        
                                                                                                     DB::table("productos")
                                                                                                         ->where("id","=",$dts[0]->fk_id_producto)
                                                                                                         ->update($act_producto);
                                                                                                  }

                                                                                                 //VALIDO SI EXISTEN EN LA SEDE AL COMPROBAR SI HAY O
                                                                                                 // NO REGISTROS EN LA CONSULTA   

                                                                                                   $dts=DB::table("detalle_inventarios")
                                                                                                      ->join("productos","productos.id","=","detalle_inventarios.fk_id_producto")
                                                                                                     ->where([
                                                                                                        ["detalle_inventarios.fk_id_producto","=",$dt[0]->id],
                                                                                                        ["detalle_inventarios.fk_id_sede","=",$datos->datos->sede]
                                                                                                         ])
                                                                                                      ->select("detalle_inventarios.id",
                                                                                                         'detalle_inventarios.fk_id_producto',
                                                                                                          "productos.unidades_por_caja",
                                                                                                          "productos.unidades_por_blister",
                                                                                                          "detalle_inventarios.cantidad_existencias",
                                                                                                          "detalle_inventarios.cantidad_existencias_unidades",
                                                                                                          "detalle_inventarios.cantidad_existencias_blister",
                                                                                                          "detalle_inventarios.precio_venta_sede",
                                                                                                          "detalle_inventarios.precio_venta_blister_sede",
                                                                                                          "detalle_inventarios.precio_mayoreo_sede",
                                                                                                          "productos.precio_compra",
                                                                                                          "productos.precio_compra_blister",
                                                                                                          "productos.precio_compra_unidad",
                                                                                                          "productos.precio_venta",
                                                                                                          "productos.precio_venta_blister",
                                                                                                          "productos.precio_mayoreo",
                                                                                                          "productos.tipo_venta_producto")
                                                                                                 ->get();     

                                                                                             //BLOQUE DE ACTUALIZACION DE CANTIDADES 
                                                                                                 if(count($dts)==0){

                                                                                                      $cantidades=[];
                                                                                                        if($value["unidades_por_caja"]!=null && $value["unidades_por_blister"]!=null){
                                                                                                                 $cantidades["cantidad_existencias"]=floor($value["total"]/$value["unidades_por_blister"])/$value["unidades_por_caja"];  
                                                                                                            }else{
                                                                                                                 $cantidades["cantidad_existencias"]=floor($value["total"]/$dts[0]->unidades_por_blister)/$dts[0]->unidades_por_caja;  
                                                                                                            }     

                                                                                                           if($value["unidades_por_blister"]!=null){
                                                                                                             $cantidades["cantidad_existencias_blister"]=floor((int)$value["total"]/(int)$value["unidades_por_blister"]); 
                                                                                                           }else{
                                                                                                                $cantidades["cantidad_existencias_blister"]=floor((int)$value["total"]/(int)$dts[0]->unidades_por_blister);  
                                                                                                           }


                                                                                                           if($value["total"]==null){
                                                                                                             $value["total"]=0;
                                                                                                           } 
                                                                                                           $cantidades["cantidad_existencias_unidades"]=$value["total"];
                                                                                                           $cantidades["estado_inventario"]="activo";


                                                                                                      $id_dt=DB::table('detalle_inventarios')
                                                                                                          ->insertGetId(["fk_id_producto"=>$dts[0]->id,
                                                                                                          "fk_id_sede"=>$datos->datos->sede,
                                                                                                          "fecha_caducidad"=>"0000-00-00 00:00:00",
                                                                                                          "cantidad_existencias"=>$cantidades["cantidad_existencias"],
                                                                                                          "cantidad_existencias_blister"=>$cantidades["cantidad_existencias_blister"],   
                                                                                                          "cantidad_existencias_unidades"=>$cantidades["cantidad_existencias_unidades"],
                                                                                                           "estado_inventario"=>$cantidades["estado_inventario"],   
                                                                                                          "minimo_inventario_sede"=>0,
                                                                                                          "precio_venta_sede"=>$value["precio_venta"],
                                                                                                          "precio_venta_blister_sede"=>$value["precio_venta_blister"],  
                                                                                                          "precio_mayoreo_sede"=>$value["precio_venta_unidad_blister"],   
                                                                                                          "created_at"=>$datos->datos->hora_cliente,
                                                                                                          "updated_at"=>$datos->datos->hora_cliente]);






                                                                                                         if($cantidades["cantidad_existencias_unidades"]!=null){

                                                                                                             DB::table('movimientos_inventario')
                                                                                                                  ->insertGetId(["fk_id_det_inventario"=>$id_dt,
                                                                                                                  "habia"=>"0",
                                                                                                                  "fk_id_usuario"=>$datos->datos->id_usuario,    
                                                                                                                  "tipo"=>"AJUSTE",
                                                                                                                  "cantidad"=>$cantidades["cantidad_existencias_unidades"],
                                                                                                                  "quedan"=>$cantidades["cantidad_existencias_unidades"],
                                                                                                                  "created_at"=>$datos->datos->hora_cliente,
                                                                                                                  "updated_at"=>$datos->datos->hora_cliente,    
                                                                                                                  "observaciones"=>"AJUSTE DE INVENTARIO"]);

                                                                                                                  DB::table('detalle_inventarios')
                                                                                                                       ->where("id","=",$id_dt)
                                                                                                                       ->update(["estado_producto_sede"=>"1","estado_inventario"=>"activo"]);

                                                                                                         } 



                                                                                                 }
                                                                                                  else{
                                                                                                     //producto ya existe actualizo existencias en sede


                                                                                                      $dts=DB::table("detalle_inventarios")
                                                                                                      ->join("productos","productos.id","=","detalle_inventarios.fk_id_producto")
                                                                                                     ->where([
                                                                                                         ["productos.codigo_producto","=",$dt[0]->codigo_producto],
                                                                                                         ["detalle_inventarios.fk_id_producto","=",$dt[0]->id],
                                                                                                         ["detalle_inventarios.fk_id_sede","=",$datos->datos->sede]
                                                                                                         ])
                                                                                                      ->select("detalle_inventarios.id",
                                                                                                             'detalle_inventarios.fk_id_producto',
                                                                                                              "productos.unidades_por_caja",
                                                                                                              "productos.unidades_por_blister",
                                                                                                              "detalle_inventarios.cantidad_existencias",
                                                                                                              "detalle_inventarios.cantidad_existencias_unidades",
                                                                                                              "detalle_inventarios.cantidad_existencias_blister",
                                                                                                              "detalle_inventarios.precio_venta_sede",
                                                                                                              "detalle_inventarios.precio_venta_blister_sede",
                                                                                                              "detalle_inventarios.precio_mayoreo_sede",
                                                                                                               "productos.precio_compra",
                                                                                                              "productos.precio_compra_blister",
                                                                                                              "productos.precio_compra_unidad",
                                                                                                              "productos.precio_venta",
                                                                                                              "productos.precio_venta_blister",
                                                                                                              "productos.precio_mayoreo",
                                                                                                              "productos.tipo_venta_producto")
                                                                                                     ->get();        
                                                                                                     $act_dat=[];
                                                                                                 if($value["precio_venta"]!=NULL){


                                                                                                         $precio=$value["precio_venta"];   
                                                                                                         $act_dat["precio_venta_sede"]=$value["precio_venta"];
                                                                                                         $DIF=(int)$value["precio_venta"]-$dts[0]->precio_compra;        
                                                                                                         $act_dat["porcentaje_ganancia_sede"]=round((($DIF)*100)/$precio,2);


                                                                                                 }   

                                                                                                 if($value["precio_venta_blister"]!=NULL){

                                                                                                          $precio_b=$value["precio_venta_blister"];
                                                                                                          $act_dat["precio_venta_blister_sede"]=$value["precio_venta_blister"];
                                                                                                          $DIF=(int)$value["precio_venta_blister"]-$dts[0]->precio_compra_blister;
                                                                                                          $act_dat["porcentaje_ganancia_blister_sede"]=round((($DIF)*100)/$precio_b,2);
                                                                                                 }

                                                                                                 if($value["precio_venta_unidad_blister"]!=NULL){

                                                                                                        $precio_u=(int)$value["precio_venta_unidad_blister"];
                                                                                                        $act_dat["precio_mayoreo_sede"]=$value["precio_venta_unidad_blister"];
                                                                                                        $DIF=(int)$value["precio_venta_unidad_blister"]-$dts[0]->precio_compra_unidad;
                                                                                                        $act_dat["porcentaje_ganancia_sede_unidad"]=round((($DIF)*100)/$precio_u,2);
                                                                                                 }
                                                                                                 //ACTUALIZAR CANTIDAD DE PRODUCTOS 
                                                                                                 //UNIDADES BLISTER UNIDAD
                                                                                                     if($value["unidades_por_blister"]!=null && $value["unidades_por_caja"]!=null){
                                                                                                         $act_dat["cantidad_existencias"]=floor(($value["total"]/$value["unidades_por_blister"])/$value["unidades_por_caja"]);
                                                                                                     }else{
                                                                                                         $act_dat["cantidad_existencias"]=floor(($value["total"]/$dts[0]->unidades_por_blister)/$dts[0]->unidades_por_caja);
                                                                                                     }


                                                                                                     if($value["unidades_por_blister"]!=null){
                                                                                                         $act_dat["cantidad_existencias_blister"]=floor($value["total"]/$value["unidades_por_blister"]);

                                                                                                     }else{
                                                                                                         $act_dat["cantidad_existencias_blister"]=floor($value["total"]/$dts[0]->unidades_por_blister);

                                                                                                     }

                                                                                                     $act_dat["cantidad_existencias_unidades"]=$value["total"];

                                                                                                     $act_dat["estado_producto_sede"]="1";

                                                                                                     $act_dat["estado_inventario"]="activo";

                                                                                                 if(count($act_dat)>0){
                                                                                                         //var_dump($act_dat);
                                                                                                         //echo "=======";
                                                                                                         DB::table("detalle_inventarios")
                                                                                                               ->where("id","=",$dts[0]->id)  
                                                                                                               ->update($act_dat);

                                                                                                               if($act_dat["cantidad_existencias_unidades"]!=null){
                                                                                                                  DB::table('movimientos_inventario')
                                                                                                                  ->insertGetId(["fk_id_det_inventario"=>$dts[0]->id,
                                                                                                                  "habia"=>"0",
                                                                                                                  "fk_id_usuario"=>$datos->datos->id_usuario,    
                                                                                                                  "tipo"=>"AJUSTE",
                                                                                                                  "cantidad"=>$act_dat["cantidad_existencias_unidades"],
                                                                                                                  "quedan"=>$act_dat["cantidad_existencias_unidades"],
                                                                                                                  "created_at"=>$datos->datos->hora_cliente,
                                                                                                                  "updated_at"=>$datos->datos->hora_cliente,    
                                                                                                                  "observaciones"=>"AJUSTE DE INVENTARIO UNIDADES TOTALES"]);    
                                                                                                               }
                                                                                                 }


                                                                                                     $act=[];
                                                                                                     if($value["unidades_por_caja"]!=null){

                                                                                                           $act["precio_compra_blister"]=round($dts[0]->precio_compra/$value["unidades_por_caja"],2);
                                                                                                           $act["unidades_por_caja"]=$value["unidades_por_caja"];

                                                                                                     }

                                                                                                     if($value["unidades_por_blister"]!=null){
                                                                                                         $act["unidades_por_blister"]=$value["unidades_por_blister"]; 

                                                                                                         $act["precio_compra_unidad"]=round(($dts[0]->precio_compra/$value["unidades_por_caja"])/$value["unidades_por_blister"],2); 
                                                                                                     }


                                                                                                     if(array_key_exists("precio_venta_sede",$act_dat)!=NULL){
                                                                                                         $act["precio_venta"]=$act_dat["precio_venta_sede"];

                                                                                                         $diff=(int)$value["precio_venta"]-$dts[0]->precio_compra;    


                                                                                                         $act["porcentaje_ganancia"]=round(($diff*100)/$value["precio_venta"],2);
                                                                                                      }

                                                                                                      if(array_key_exists("precio_venta_blister_sede",$act_dat)){

                                                                                                         $act["precio_venta_blister"]=$act_dat["precio_venta_blister_sede"];

                                                                                                         $diff=(int)$value["precio_venta_blister"]-$dts[0]->precio_compra_blister;    


                                                                                                         $act["porcentaje_ganancia_blister"]=round(($diff*100)/$value["precio_venta_blister"],2);

                                                                                                      }

                                                                                                      if(array_key_exists("precio_mayoreo_sede",$act_dat)){

                                                                                                          $act["precio_mayoreo"]=$act_dat["precio_mayoreo_sede"];

                                                                                                          $diff=(int)$value["precio_venta_unidad_blister"]-$dts[0]->precio_compra_unidad;    


                                                                                                          $act["porcentaje_ganancia_unidad"]=round(($diff*100)/$value["precio_venta_unidad_blister"],2);
                                                                                                      }



                                                                                                     if(count($act)>0){

                                                                                                         DB::table("productos")
                                                                                                             ->where("id","=",$dts[0]->fk_id_producto)
                                                                                                             ->update($act);
                                                                                                      }





                                                                                                  }
                                                                                       }
                                                                                         //fin insertar movimientos
                                                                                }
                                                                                else{
                                                                                    //CODIGO NO EXISTE
                                                                                    $arr_no_existe[$ne]=
                                                                                                        [
                                                                                                         "codigo_producto"=>$value["codigo_producto"],
                                                                                                        
                                                                                                         "precio_venta"=>$value["precio_venta"],
                                                                                                         "precio_venta_blister"=>$value["precio_venta_blister"],
                                                                                                         "precio_venta_unidad_blister"=>$value["precio_venta_unidad_blister"],
                                                                                                         "total"=>$value["total"]
                                                                                                        ];

                                                                                    $ne++;
                                                                                }
                                                                            }

                                                                       }

                                                                   }
                                                                   
                                                            }       
                                                            
                                                            Excel::create("NoExisten", function($excel) use($arr_no_existe){

                                                                         $excel->sheet('no_existen',function($sheet) use($arr_no_existe){
                                                                                      $sheet->fromArray($arr_no_existe);
                                                                                });
                                                            })->store('xls', substr(base_path(),0,-8)."archivos/exportacion/excel");   


                                                            echo json_encode(["mensaje"=>"Inventario ajustado ","respuesta"=>true,"no_existen"=>"archivos/exportacion/excel/NoExisten.xls"]);

                                        }else{
                                            echo json_encode(["respuesta"=>false,"mensaje"=>"Por favor selecciona una sede para reajustar el inventario"]);  
                                        }

                                          

                                        break;                     
                    
                       default:
                            echo json_encode(["respuesta"=>false,"mensaje"=>"Selecciona una opcion"]);
                            break;            
                                   
                                   
                                   
                                            
                }

                          
                             
      
        });               
            
        }
        else{
            echo "Archivo ".$datos->datos->nombre_archivo." no existe";
        }  
    }
    public function importar_xls_ftp_get($sede,$id_usuario,$nombre_archivo,$tipo_importacion) {
        
        //$des=substr(base_path(),0,-8).trim("archivos/sftp/ ");  
        $des=substr(base_path(),0,-8).trim("ftp/ ");//en el servidor funcion con el '/'  
        $fecha_formato=Date::now(new DateTimeZone('America/Bogota'));
        $hora_cliente=$fecha_formato->format("Y-m-d H:i:s");
        
        
        $ruta=trim($des).$nombre_archivo;
        //echo $ruta; 
        if(file_exists($ruta)){
               
            Excel::load($ruta,function($reader)use($sede,$id_usuario,$tipo_importacion,$hora_cliente,$ruta){
                                                   
                $arr=$reader->toArray();
                //var_dump($arr);
                switch ($tipo_importacion) {
                      case "ajustar_inventario_sede":

                                        if($sede!="0" && $sede!="--"){
                                            ini_set('max_execution_time', 600000); //900 seconds = 5 minutes
                                            //linea para impedir error de memoria
                                            ini_set('memory_limit', '-1'); 
                                        
                                                $prO=DB::table('productos')
                                                                ->where("estado_producto","=","1")
                                                                ->get();



                                                            $arr_ins=[];
                                                            $i=0;
                                                            $esta=false;
                                                            $new_arr=[];
                                                            $arr_no_existe=[];
                                                            $ne=0;
                                                            if(count($arr)<100){
                                                                $div_l=ceil(count($arr)/10);
                                                            }else{
                                                                $div_l=ceil(count($arr)/100);
                                                            }
                                                            //echo $div_l;
                                                            $lote_productos_excel = array_chunk($arr, $div_l);
                                                            if(count($arr)>0){
                                                                   foreach ($lote_productos_excel as $key => $valor) {
                                                                        foreach ($valor as $key => $value) {

                                                                            if(array_key_exists("codigo_producto",$value) && $value["codigo_producto"]!=NULL){
                                                                                 $dt=DB::table('productos')
                                                                                    ->where("codigo_producto","=",$value["codigo_producto"])
                                                                                    ->orwhere("codigo_distribuidor","=",$value["codigo_producto"])
                                                                                    ->get(); 

                                                                                if(count($dt)>0){
                                                                                     //BUSCAR EN SEDE
                                                                                     $dts=DB::table("detalle_inventarios")
                                                                                             ->join("productos","productos.id","=","detalle_inventarios.fk_id_producto")
                                                                                            ->where([
                                                                                                ["detalle_inventarios.fk_id_producto","=",$dt[0]->id],
                                                                                                ["detalle_inventarios.fk_id_sede","=",$sede]
                                                                                                ])
                                                                                             ->select("detalle_inventarios.id",
                                                                                                    'detalle_inventarios.fk_id_producto',
                                                                                                     "productos.unidades_por_caja",
                                                                                                     "productos.unidades_por_blister",
                                                                                                     "detalle_inventarios.cantidad_existencias",
                                                                                                     "detalle_inventarios.cantidad_existencias_unidades",
                                                                                                     "detalle_inventarios.cantidad_existencias_blister",
                                                                                                     "detalle_inventarios.precio_venta_sede",
                                                                                                     "detalle_inventarios.precio_venta_blister_sede",
                                                                                                     "detalle_inventarios.precio_mayoreo_sede",
                                                                                                     "productos.precio_compra",
                                                                                                     "productos.precio_compra_blister",
                                                                                                     "productos.precio_compra_unidad",
                                                                                                     "productos.precio_venta",
                                                                                                     "productos.precio_venta_blister",
                                                                                                     "productos.precio_mayoreo",
                                                                                                     "productos.tipo_venta_producto")
                                                                                            ->get();
                                                                                        
                                                                                        //DATOS A ACTUALIZAR    
                                                                                       if(count($dts)>0){
                                                                                            $act_detalle=[];
                                                                                             $act_producto=[];
                                                                                             $ia=0;

                                                                                            //valido precios venta
                                                                                            if($value["precio_venta"]!=NULL){

                                                                                                     if((int)$dts[0]->precio_venta==0){

                                                                                                         $precio=1;

                                                                                                     }else{

                                                                                                         $precio=(int)$dts[0]->precio_venta;

                                                                                                     }

                                                                                                     $act_detalle["precio_venta_sede"]=$value["precio_venta"];
                                                                                                     $DIF=(int)$value["precio_venta"]-$dts[0]->precio_compra;
                                                                                                     $act_detalle["porcentaje_ganancia_sede"]=round((($DIF)*100)/$precio,2);


                                                                                             }   

                                                                                             if($value["precio_venta_blister"]!=NULL){
                                                                                                     if((int)$dts[0]->precio_venta_blister==0){
                                                                                                         $precio_b=1;
                                                                                                     }else{
                                                                                                         $precio_b=(int)$dts[0]->precio_venta_blister;
                                                                                                     }
                                                                                                      $act_detalle["precio_venta_blister_sede"]=$value["precio_venta_blister"];
                                                                                                      $DIF=(int)$value["precio_venta_blister"]-$dts[0]->precio_compra_blister;
                                                                                                      $act_detalle["porcentaje_ganancia_blister_sede"]=round((($DIF)*100)/$precio_b,2);
                                                                                             }

                                                                                             if($value["precio_venta_unidad_blister"]!=NULL){
                                                                                                     if((int)$dts[0]->precio_mayoreo_sede==0){
                                                                                                         $precio_u=1;

                                                                                                     }else{
                                                                                                         $precio_u=(int)$dts[0]->precio_mayoreo_sede;
                                                                                                     } 

                                                                                                    $act_detalle["precio_mayoreo_sede"]=$value["precio_venta_unidad_blister"];
                                                                                                    $DIF=(int)$value["precio_venta_unidad_blister"]-$dts[0]->precio_compra_unidad;
                                                                                                    $act_detalle["porcentaje_ganancia_sede_unidad"]=round((($DIF)*100)/$precio_u,2);
                                                                                             }


                                                                                                  //ACTUALIZAR precio DE PRODUCTOS 
                                                                                             //valido precios venta    
                                                                                             if(count($act_detalle)>0){
                                                                                                     DB::table("detalle_inventarios")
                                                                                                           ->where("id","=",$dts[0]->id)  
                                                                                                           ->update($act_detalle);
                                                                                             }
                                                                                                

                                                                                              if($value["unidades_por_caja"]!=null){

                                                                                                       $act_producto["unidades_por_caja"]=$value["unidades_por_caja"];

                                                                                                       $act_producto["precio_compra_blister"]=round($dts[0]->precio_compra/$value["unidades_por_caja"],2);

                                                                                              }

                                                                                              if($value["unidades_por_blister"]!=null){

                                                                                                 $act_producto["unidades_por_blister"]=$value["unidades_por_blister"]; 

                                                                                                 $act_producto["precio_compra_unidad"]=round(($dts[0]->precio_compra/$value["unidades_por_caja"])/$value["unidades_por_blister"],2); 
                                                                                              }


                                                                                                  if(array_key_exists("precio_venta_sede",$act_detalle)){

                                                                                                     $act_producto["precio_venta"]=$act_detalle["precio_venta_sede"];

                                                                                                     $diff=(int)$act_detalle["precio_venta_sede"]-$dts[0]->precio_compra;    


                                                                                                     $act_producto["porcentaje_ganancia"]=round(($diff*100)/$act_detalle["precio_venta_sede"],2);

                                                                                                  }

                                                                                                  if(array_key_exists("precio_venta_blister_sede",$act_detalle)){

                                                                                                     $act_producto["precio_venta_blister"]=$act_detalle["precio_venta_blister_sede"];

                                                                                                     $diff=(int)$act_detalle["precio_venta_blister_sede"]-$dts[0]->precio_compra_blister;    


                                                                                                     $act_producto["porcentaje_ganancia_blister"]=round(($diff*100)/$value["precio_venta_blister"],2);

                                                                                                  }

                                                                                                  if(array_key_exists("precio_mayoreo_sede",$act_detalle)){

                                                                                                      $act_producto["precio_mayoreo"]=$act_detalle["precio_mayoreo_sede"];

                                                                                                      $diff=(int)$value["precio_venta_unidad_blister"]-$dts[0]->precio_compra_unidad;    


                                                                                                      $act_producto["porcentaje_ganancia_unidad"]=round(($diff*100)/$value["precio_venta_unidad_blister"],2);
                                                                                                  }

                                                                                                  if(count($act_producto)>0){
                                                                                                        
                                                                                                     DB::table("productos")
                                                                                                         ->where("id","=",$dts[0]->fk_id_producto)
                                                                                                         ->update($act_producto);
                                                                                                  }

                                                                                                 //VALIDO SI EXISTEN EN LA SEDE AL COMPROBAR SI HAY O
                                                                                                 // NO REGISTROS EN LA CONSULTA   

                                                                                                   $dts=DB::table("detalle_inventarios")
                                                                                                      ->join("productos","productos.id","=","detalle_inventarios.fk_id_producto")
                                                                                                     ->where([
                                                                                                        ["detalle_inventarios.fk_id_producto","=",$dt[0]->id],
                                                                                                        ["detalle_inventarios.fk_id_sede","=",$sede]
                                                                                                         ])
                                                                                                      ->select("detalle_inventarios.id",
                                                                                                         'detalle_inventarios.fk_id_producto',
                                                                                                          "productos.unidades_por_caja",
                                                                                                          "productos.unidades_por_blister",
                                                                                                          "detalle_inventarios.cantidad_existencias",
                                                                                                          "detalle_inventarios.cantidad_existencias_unidades",
                                                                                                          "detalle_inventarios.cantidad_existencias_blister",
                                                                                                          "detalle_inventarios.precio_venta_sede",
                                                                                                          "detalle_inventarios.precio_venta_blister_sede",
                                                                                                          "detalle_inventarios.precio_mayoreo_sede",
                                                                                                          "productos.precio_compra",
                                                                                                          "productos.precio_compra_blister",
                                                                                                          "productos.precio_compra_unidad",
                                                                                                          "productos.precio_venta",
                                                                                                          "productos.precio_venta_blister",
                                                                                                          "productos.precio_mayoreo",
                                                                                                          "productos.tipo_venta_producto")
                                                                                                 ->get();     

                                                                                             //BLOQUE DE ACTUALIZACION DE CANTIDADES 
                                                                                                 if(count($dts)==0){

                                                                                                      $cantidades=[];
                                                                                                        if($value["unidades_por_caja"]!=null && $value["unidades_por_blister"]!=null){
                                                                                                                 $cantidades["cantidad_existencias"]=floor($value["total"]/$value["unidades_por_blister"])/$value["unidades_por_caja"];  
                                                                                                            }else{
                                                                                                                 $cantidades["cantidad_existencias"]=floor($value["total"]/$dts[0]->unidades_por_blister)/$dts[0]->unidades_por_caja;  
                                                                                                            }     

                                                                                                           if($value["unidades_por_blister"]!=null){
                                                                                                             $cantidades["cantidad_existencias_blister"]=floor((int)$value["total"]/(int)$value["unidades_por_blister"]); 
                                                                                                           }else{
                                                                                                                $cantidades["cantidad_existencias_blister"]=floor((int)$value["total"]/(int)$dts[0]->unidades_por_blister);  
                                                                                                           }


                                                                                                           if($value["total"]==null){
                                                                                                             $value["total"]=0;
                                                                                                           } 
                                                                                                           $cantidades["cantidad_existencias_unidades"]=$value["total"];
                                                                                                           $cantidades["estado_inventario"]="activo";


                                                                                                      $id_dt=DB::table('detalle_inventarios')
                                                                                                          ->insertGetId(["fk_id_producto"=>$dts[0]->id,
                                                                                                          "fk_id_sede"=>$sede,
                                                                                                          "fecha_caducidad"=>"0000-00-00 00:00:00",
                                                                                                          "cantidad_existencias"=>$cantidades["cantidad_existencias"],
                                                                                                          "cantidad_existencias_blister"=>$cantidades["cantidad_existencias_blister"],   
                                                                                                          "cantidad_existencias_unidades"=>$cantidades["cantidad_existencias_unidades"],
                                                                                                           "estado_inventario"=>$cantidades["estado_inventario"],   
                                                                                                          "minimo_inventario_sede"=>0,
                                                                                                          "precio_venta_sede"=>$value["precio_venta"],
                                                                                                          "precio_venta_blister_sede"=>$value["precio_venta_blister"],  
                                                                                                          "precio_mayoreo_sede"=>$value["precio_venta_unidad_blister"],   
                                                                                                          "created_at"=>$hora_cliente,
                                                                                                          "updated_at"=>$hora_cliente]);






                                                                                                         if($cantidades["cantidad_existencias_unidades"]!=null){

                                                                                                             DB::table('movimientos_inventario')
                                                                                                                  ->insertGetId(["fk_id_det_inventario"=>$id_dt,
                                                                                                                  "habia"=>"0",
                                                                                                                  "fk_id_usuario"=>$id_usuario,    
                                                                                                                  "tipo"=>"AJUSTE",
                                                                                                                  "cantidad"=>$cantidades["cantidad_existencias_unidades"],
                                                                                                                  "quedan"=>$cantidades["cantidad_existencias_unidades"],
                                                                                                                  "created_at"=>$hora_cliente,
                                                                                                                  "updated_at"=>$hora_cliente,    
                                                                                                                  "observaciones"=>"AJUSTE DE INVENTARIO"]);

                                                                                                                  DB::table('detalle_inventarios')
                                                                                                                       ->where("id","=",$id_dt)
                                                                                                                       ->update(["estado_producto_sede"=>"1","estado_inventario"=>"activo"]);

                                                                                                         } 



                                                                                                 }
                                                                                                  else{
                                                                                                     //producto ya existe actualizo existencias en sede


                                                                                                      $dts=DB::table("detalle_inventarios")
                                                                                                      ->join("productos","productos.id","=","detalle_inventarios.fk_id_producto")
                                                                                                     ->where([
                                                                                                         ["productos.codigo_producto","=",$dt[0]->codigo_producto],
                                                                                                         ["detalle_inventarios.fk_id_producto","=",$dt[0]->id],
                                                                                                         ["detalle_inventarios.fk_id_sede","=",$sede]
                                                                                                         ])
                                                                                                      ->select("detalle_inventarios.id",
                                                                                                             'detalle_inventarios.fk_id_producto',
                                                                                                              "productos.unidades_por_caja",
                                                                                                              "productos.unidades_por_blister",
                                                                                                              "detalle_inventarios.cantidad_existencias",
                                                                                                              "detalle_inventarios.cantidad_existencias_unidades",
                                                                                                              "detalle_inventarios.cantidad_existencias_blister",
                                                                                                              "detalle_inventarios.precio_venta_sede",
                                                                                                              "detalle_inventarios.precio_venta_blister_sede",
                                                                                                              "detalle_inventarios.precio_mayoreo_sede",
                                                                                                               "productos.precio_compra",
                                                                                                              "productos.precio_compra_blister",
                                                                                                              "productos.precio_compra_unidad",
                                                                                                              "productos.precio_venta",
                                                                                                              "productos.precio_venta_blister",
                                                                                                              "productos.precio_mayoreo",
                                                                                                              "productos.tipo_venta_producto")
                                                                                                     ->get();        
                                                                                                     $act_dat=[];
                                                                                                 if($value["precio_venta"]!=NULL){


                                                                                                         $precio=$value["precio_venta"];   
                                                                                                         $act_dat["precio_venta_sede"]=$value["precio_venta"];
                                                                                                         $DIF=(int)$value["precio_venta"]-$dts[0]->precio_compra;        
                                                                                                         $act_dat["porcentaje_ganancia_sede"]=round((($DIF)*100)/$precio,2);


                                                                                                 }   

                                                                                                 if($value["precio_venta_blister"]!=NULL){

                                                                                                          $precio_b=$value["precio_venta_blister"];
                                                                                                          $act_dat["precio_venta_blister_sede"]=$value["precio_venta_blister"];
                                                                                                          $DIF=(int)$value["precio_venta_blister"]-$dts[0]->precio_compra_blister;
                                                                                                          $act_dat["porcentaje_ganancia_blister_sede"]=round((($DIF)*100)/$precio_b,2);
                                                                                                 }

                                                                                                 if($value["precio_venta_unidad_blister"]!=NULL){

                                                                                                        $precio_u=(int)$value["precio_venta_unidad_blister"];
                                                                                                        $act_dat["precio_mayoreo_sede"]=$value["precio_venta_unidad_blister"];
                                                                                                        $DIF=(int)$value["precio_venta_unidad_blister"]-$dts[0]->precio_compra_unidad;
                                                                                                        $act_dat["porcentaje_ganancia_sede_unidad"]=round((($DIF)*100)/$precio_u,2);
                                                                                                 }
                                                                                                 //ACTUALIZAR CANTIDAD DE PRODUCTOS 
                                                                                                 //UNIDADES BLISTER UNIDAD
                                                                                                     if($value["unidades_por_blister"]!=null && $value["unidades_por_caja"]!=null){
                                                                                                         $act_dat["cantidad_existencias"]=floor(($value["total"]/$value["unidades_por_blister"])/$value["unidades_por_caja"]);
                                                                                                     }else{
                                                                                                         $act_dat["cantidad_existencias"]=floor(($value["total"]/$dts[0]->unidades_por_blister)/$dts[0]->unidades_por_caja);
                                                                                                     }


                                                                                                     if($value["unidades_por_blister"]!=null){
                                                                                                         $act_dat["cantidad_existencias_blister"]=floor($value["total"]/$value["unidades_por_blister"]);

                                                                                                     }else{
                                                                                                         $act_dat["cantidad_existencias_blister"]=floor($value["total"]/$dts[0]->unidades_por_blister);

                                                                                                     }

                                                                                                     $act_dat["cantidad_existencias_unidades"]=$value["total"];

                                                                                                     $act_dat["estado_producto_sede"]="1";

                                                                                                     $act_dat["estado_inventario"]="activo";

                                                                                                 if(count($act_dat)>0){
                                                                                                         //var_dump($act_dat);
                                                                                                         //echo "=======";
                                                                                                         DB::table("detalle_inventarios")
                                                                                                               ->where("id","=",$dts[0]->id)  
                                                                                                               ->update($act_dat);

                                                                                                               if($act_dat["cantidad_existencias_unidades"]!=null){
                                                                                                                  DB::table('movimientos_inventario')
                                                                                                                  ->insertGetId(["fk_id_det_inventario"=>$dts[0]->id,
                                                                                                                  "habia"=>"0",
                                                                                                                  "fk_id_usuario"=>$id_usuario,    
                                                                                                                  "tipo"=>"AJUSTE",
                                                                                                                  "cantidad"=>$act_dat["cantidad_existencias_unidades"],
                                                                                                                  "quedan"=>$act_dat["cantidad_existencias_unidades"],
                                                                                                                  "created_at"=>$hora_cliente,
                                                                                                                  "updated_at"=>$hora_cliente,    
                                                                                                                  "observaciones"=>"AJUSTE DE INVENTARIO UNIDADES TOTALES"]);    
                                                                                                               }
                                                                                                 }


                                                                                                     $act=[];
                                                                                                     if($value["unidades_por_caja"]!=null){

                                                                                                           $act["precio_compra_blister"]=round($dts[0]->precio_compra/$value["unidades_por_caja"],2);
                                                                                                           $act["unidades_por_caja"]=$value["unidades_por_caja"];

                                                                                                     }

                                                                                                     if($value["unidades_por_blister"]!=null){
                                                                                                         $act["unidades_por_blister"]=$value["unidades_por_blister"]; 

                                                                                                         $act["precio_compra_unidad"]=round(($dts[0]->precio_compra/$value["unidades_por_caja"])/$value["unidades_por_blister"],2); 
                                                                                                     }


                                                                                                     if(array_key_exists("precio_venta_sede",$act_dat)!=NULL){
                                                                                                         $act["precio_venta"]=$act_dat["precio_venta_sede"];

                                                                                                         $diff=(int)$value["precio_venta"]-$dts[0]->precio_compra;    


                                                                                                         $act["porcentaje_ganancia"]=round(($diff*100)/$value["precio_venta"],2);
                                                                                                      }

                                                                                                      if(array_key_exists("precio_venta_blister_sede",$act_dat)){

                                                                                                         $act["precio_venta_blister"]=$act_dat["precio_venta_blister_sede"];

                                                                                                         $diff=(int)$value["precio_venta_blister"]-$dts[0]->precio_compra_blister;    


                                                                                                         $act["porcentaje_ganancia_blister"]=round(($diff*100)/$value["precio_venta_blister"],2);

                                                                                                      }

                                                                                                      if(array_key_exists("precio_mayoreo_sede",$act_dat)){

                                                                                                          $act["precio_mayoreo"]=$act_dat["precio_mayoreo_sede"];

                                                                                                          $diff=(int)$value["precio_venta_unidad_blister"]-$dts[0]->precio_compra_unidad;    


                                                                                                          $act["porcentaje_ganancia_unidad"]=round(($diff*100)/$value["precio_venta_unidad_blister"],2);
                                                                                                      }



                                                                                                     if(count($act)>0){

                                                                                                         DB::table("productos")
                                                                                                             ->where("id","=",$dts[0]->fk_id_producto)
                                                                                                             ->update($act);
                                                                                                      }





                                                                                                  }
                                                                                       }
                                                                                         //fin insertar movimientos
                                                                                }
                                                                                else if($value["codigo_producto"]!=NULL){
                                                                                    //CODIGO NO EXISTE
                                                                                    $arr_no_existe[$ne]=
                                                                                                        [
                                                                                                         "codigo_producto"=>$value["codigo_producto"],
                                                                                                        
                                                                                                         "precio_venta"=>$value["precio_venta"],
                                                                                                         "precio_venta_blister"=>$value["precio_venta_blister"],
                                                                                                         "precio_venta_unidad_blister"=>$value["precio_venta_unidad_blister"],
                                                                                                         "total"=>$value["total"]
                                                                                                        ];

                                                                                    $ne++;
                                                                                }
                                                                            }

                                                                       }

                                                                   }
                                                                   
                                                            }       
                                                            
                                                            Excel::create("NoExisten", function($excel) use($arr_no_existe){

                                                                         $excel->sheet('no_existen',function($sheet) use($arr_no_existe){
                                                                                      $sheet->fromArray($arr_no_existe);
                                                                                });
                                                            })->store('xls', substr(base_path(),0,-8)."archivos/exportacion/excel");   


                                                            echo json_encode(["mensaje"=>"Inventario ajustado ","respuesta"=>true,"no_existen"=>"archivos/exportacion/excel/NoExisten.xls"]);

                                        }else{
                                            echo json_encode(["respuesta"=>false,"mensaje"=>"Por favor selecciona una sede para reajustar el inventario"]);  
                                        }

                                          

                                        break;                     
                    
                       default:
                            echo json_encode(["respuesta"=>false,"mensaje"=>"Selecciona una opcion"]);
                            break;                                                        
                }
            });               
            
        }
        else{
            echo "Archivo ".$nombre_archivo." no existe";
        }  
    }
   
}
