<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;

use App\Http\Requests;

use Maatwebsite\Excel\Facades\Excel;

use DB;



class ImportarController extends Controller
{
    //
    
   
    public function importar_xls_ftp(Request $request) {
        
        //$des=substr(base_path(),0,-8).trim("archivos/sftp/ ");  
        $des=substr(base_path(),0,-8).trim("ftp/ ");//en el servidor funcion con el '/'  
        
        $datos=json_decode($request->get("datos"));
        $ruta=trim($des).$datos->datos->nombre_archivo;

        if(file_exists($ruta)){
               
        //echo $ruta;
        //var_dump($datos);
        Excel::load($ruta,function($reader)use($datos,$ruta){
                                                   
                $arr=$reader->toArray();
                    
                switch ($datos->datos->tipo_importacion) {
                                   
                                   
                                   case "productos":
                                       
                                        ini_set('max_execution_time', 6000);
                                         //900 seconds = 5 minutes
                                        //linea para impedir error de memoria
                                        ini_set('memory_limit', '-1'); 
                                        
                                                switch($datos->datos->sede){
                                                    case 0:

                                                        $mis_productos=DB::table('productos')
                                                                          ->get();
                                                        $ultimo_id;
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
                                                         //echo count($arr);
                                                         //var_dump($div_l);
                                                        $lote_productos_excel = array_chunk($arr, $div_l);
                                                          //var_dump($lote_productos_excel);
                                                          foreach ($lote_productos_excel as $key => $lpx) {
                                                                  foreach ($lpx as $key => $value) {

                                                                    
                                                                        if(array_key_exists("codigo_coopidrogas",$value) && $value["codigo_coopidrogas"]!=NULL){
                                                                                
                                                                                       $registar=true;    
                                                                                
                                                                                       if($validar_existencia && $value["codigo_coopidrogas"]!=NULL){
                                                                                           
                                                                                                foreach ($mis_productos as $k => $v) {

                                                                                                        if($value["codigo_coopidrogas"]!=NULL){
                                                                                                            $com=$value["codigo_coopidrogas"];

                                                                                                            if($value["codigo_venta_menudeo_o_unidad"]==null){

                                                                                                                $com2=$value["codigo_coopidrogas"];
                                                                                                            
                                                                                                            }else{
                                                                                                                $com2=$value["codigo_venta_menudeo_o_unidad"];    
                                                                                                            }

                                                                                                            if($v->codigo_distribuidor == $com ){


                                                                                                                $existe_en_db=true;   

                                                                                                                $cod_repetidos[$repe]=$value;

                                                                                                                $repe++;

                                                                                                                //break;
                                                                                                            }else{
                                                                                                                $existe_en_db=false;    
                                                                                                            }


                                                                                                        }
                                                                                                }

                                                                                                if(!$existe_en_db && $value["codigo_coopidrogas"]!=NULL){
                                                                                                                //VALIDACION DE LA EXISTENCIA DE LA CATEGORIA
                                                                                                                foreach ($mis_departamentos as $y => $d) {
                                                                                                                    if( $value["departamento"]==""){
                                                                                                                         $value["departamento"]="OTROS";
                                                                                                                    }

                                                                                                                    if(strtoupper($d->nombre_departamento)
                                                                                                                        == strtoupper($value["departamento"])){
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


                                                                                                                    if($registar && $value["codigo_coopidrogas"]!=NULL){


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
                                                                                                else{
                                                                                                       $arr_con_coincidencias_en_bd[$i]=[

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
                                                                                                                            "impuesto"=>$value["valor_impuesto"]
                                                                                                                        ];

                                                                                                                        $i++;


                                                                                                }

                                                                                        }
                                                                                       else{

                                                                                            if(!$existe_en_db && $value["codigo_coopidrogas"]!=NULL){
                                                                                                                //VALIDACION DE LA EXISTENCIA DE LA CATEGORIA
                                                                                                                foreach ($mis_departamentos as $y => $d) {
                                                                                                                    if(strtoupper($d->nombre_departamento)== strtoupper($value["departamento"])){
                                                                                                                        $cat_econtrada=true;
                                                                                                                        $value["departamento"]=$d->id;

                                                                                                                        break;
                                                                                                                    }
                                                                                                                }



                                                                                                                if($cat_econtrada){

                                                                                                                    foreach ($mis_proveedores as $y => $d) {
                                                                                                                            if(strtoupper($d->nombre_proveedor)==strtoupper($value["proveedor"])){

                                                                                                                                  $value["proveedor"]=$d->id;

                                                                                                                                break;
                                                                                                                        }
                                                                                                                    }



                                                                                                                  if($value["codigo_venta_menudeo_o_unidad"]==NULL){
                                                                                                                       $value["codigo_venta_menudeo_o_unidad"]=$value["codigo_coopidrogas"]; 
                                                                                                                    }


                                                                                                                    if($value["descripcion_farmacia"]==NULL){
                                                                                                                       $value["descripcion_farmacia"]=$value["descripcion_farmacia"]; 
                                                                                                                    }


                                                                                                                    $cat_econtrada=false;



                                                                                                                    if($value["laboratorio"]==NULL){
                                                                                                                        $value["laboratorio"]="N/A";
                                                                                                                    }


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
                                                                                                                    //var_dump($value["numero_de_unidades_presentacion"]);
                                                                                                                    //var_dump($value["unidades_por_blister"]);
                                                                                                                    if($value["unidades_por_blister"]==NULL){
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
                                                                                                                                            //var_dump($vs);
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


                                                                                                                    if($registar && $value["codigo_coopidrogas"]!=NULL){


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
                                                                                                                            "impuesto"=>$value["valor_impuesto"]
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
                                                                                            else{


                                                                                                                            $arr_con_coincidencias_en_bd[$i]=[
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
                                                                                                                            "impuesto"=>$value["valor_impuesto"]
                                                                                                                        ];
                                                                                                                        $i++;


                                                                                                        }
                                                                                       }




                                                                            }   




                                                                }

                                                          }
                                                            //(var_dump($arr_sin_coincidencias_en_bd);
                                                            if(count($arr_sin_coincidencias_en_bd)>0){


                                                                    //var_dump($arr_sin_coincidencias_en_bd[0]);
                                                                    $limitStatements = DB::selectOne(
                                                                            DB::raw("SELECT @@max_prepared_stmt_count AS count")
                                                                        )->count;
                                                                    $div=count($arr_sin_coincidencias_en_bd)/2;

                                                                    $lote_productos = array_chunk($arr_sin_coincidencias_en_bd, ceil($div));
                                                                    $s="";
                                                                    foreach ($lote_productos as $key => $l_p) {


                                                                     // DB::transaction(function()use($l_p,$s){
                                                                            //var_dump($l_p);
                                                                            //echo "=".count($l_p)."============";
                                                                                try {
                                                                                      DB::table('productos')
                                                                                             ->insert($l_p);

                                                                                    $error=false;
                                                                                } catch (\Illuminate\Database\QueryException $e) {
                                                                                    if($e->getCode() === '23000') {
                                                                                       $error=true;
                                                                                       //echo ":(";
                                                                                       $msn_error=$e->getMessage();
                                                                                    }
                                                                                }    

                                                                        //});          

                                                                    } 

                                                                    $arr_dt_inv=[];
                                                                    $r=0;

                                                                   if(!$error){
                                                                            //var_dump($error);

                                                                             foreach ($arr_sin_coincidencias_en_bd as $key => $value) {

                                                                                    foreach ($mis_sedes as $key => $s) {
                                                                                        //var_dump($s);
                                                                                     $arr_dt_inv[$r]=[
                                                                                                "fk_id_producto"=>$value["id"],
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
                                                                                                "estado_inventario"=>"activo"
                                                                                                "created_at"=>$datos->datos->hora_cliente,
                                                                                                "updated_at"=>$datos->datos->hora_cliente,

                                                                                                ];
                                                                                        $r++;
                                                                                }
                                                                            }

                                                                                        $lotes = array_chunk($arr_dt_inv, ceil(count($arr_dt_inv)/4));
                                                                                        //var_dump($lotes);
                                                                                        foreach ($lotes as $lote) {
                                                                                            DB::table("detalle_inventarios")
                                                                                                    ->insert($lote); 
                                                                                            try {   
                                                                                                //var_dump($lote);
                                                                                                //echo '==========';


                                                                                            } catch (\Illuminate\Database\QueryException $e) {
                                                                                                        if($e->getCode() === '23000') {
                                                                                                           $s= "dupplicado";
                                                                                                        }else{
                                                                                                            $e->getMessage();
                                                                                                        }
                                                                                            }    

                                                                                        }


                                                                                        if(file_exists("archivos/exportacion/excel/productos_importados_sin_categoria_".explode(" ", $datos->hora_cliente)[0]).".xls"){

                                                                                        $nom_arc_sc="productos_importados_sin_categoria_".explode(" ", $datos->hora_cliente)[0]."_".explode(":",explode(" ", $datos->hora_cliente)[1])[2];
                                                                                        }else{
                                                                                             "productos_importados_sin_categoria_".explode(" ", $datos->hora_cliente)[0];   
                                                                                        } 

                                                                                        Excel::create($nom_arc_sc, function($excel) use($arr_sin_cate){
                                                                                                // use($datos->datos->nombre_reporte)   
                                                                                                $excel->sheet('sin_categoria',function($sheet) use($arr_sin_cate){
                                                                                                        //var_dump($id);
                                                                                                        /*$datos=Producto::where('nombre_producto','LIKE','A%')->limit('10')->get();*/

                                                                                                        //var_dump($reporte["datos"]);

                                                                                                        $sheet->fromArray($arr_sin_cate);
                                                                                                });
                                                                                            })->store('xls', substr(base_path(),0,-8)."archivos/exportacion/excel");

                                                                                        if(file_exists("archivos/exportacion/excel/productos_importados_repetidos_".explode(" ", $datos->hora_cliente)[0]).".xls"){

                                                                                            $nom_arc="productos_importados_repetidos_".explode(" ", $datos->hora_cliente)[0]."_".explode(":",explode(" ", $datos->hora_cliente)[1])[2];
                                                                                        }else{
                                                                                             $nom_arc="productos_importados_repetidos_".explode(" ", $datos->hora_cliente)[0];   
                                                                                        }      
                                                                                        Excel::create($nom_arc, function($excel) use($arr_con_coincidencias_en_bd){
                                                                                                // use($datos->datos->nombre_reporte)   
                                                                                                $excel->sheet('repetidos',function($sheet) use($arr_con_coincidencias_en_bd){
                                                                                                        //var_dump($id);
                                                                                                        /*$datos=Producto::where('nombre_producto','LIKE','A%')
                                                                                                                                                ->limit('10')
                                                                                                                                                ->get();*/

                                                                                                        //var_dump($reporte["datos"]);

                                                                                                        $sheet->fromArray($arr_con_coincidencias_en_bd);
                                                                                                });
                                                                                            })->store('xls', substr(base_path(),0,-8)."archivos/exportacion/excel");


                                                                                        echo json_encode(["respuesta"=>true,"mensaje"=>"productos registrados ","repetidos"=>"archivos/exportacion/excel/".$nom_arc.".xls","sin_categoria"=>"archivos/exportacion/excel/".$nom_arc_sc.".xls"]);
                                                                   }else{
                                                                         echo json_encode(["respuesta"=>false,"mensaje"=>"Ha ocurrido un error","error"=>$msn_error]);
                                                                   }



                                                                         //ARCHIVOS DE ERRORES     

                                                                       //return response()->json(["respuesta"=>true,"mensaje"=>"productos registrados ","repetidos"=>$cod_repetidos,"sin_categoria"=>$arr_sin_cate]);




                                                           }
                                                            else{


                                                                 if(file_exists("archivos/exportacion/excel/productos_importados_sin_categoria_".explode(" ", $datos->hora_cliente)[0]).".xls"){

                                                                        $nom_arc_SC="productos_importados_sin_categoria_".explode(" ", $datos->hora_cliente)[0]."_".explode(":",explode(" ", $datos->hora_cliente)[1])[2];
                                                                    }else{
                                                                         $nom_arc="productos_importados_sin_categoria_".explode(" ", $datos->hora_cliente)[0];   
                                                                    } 
                                                                    Excel::create($nom_arc_SC, function($excel) use($arr_sin_cate){
                                                                            // use($datos->datos->nombre_reporte)   
                                                                            $excel->sheet('sin_categoria',function($sheet) use($arr_sin_cate){
                                                                                    //var_dump($id);
                                                                                    /*$datos=Producto::where('nombre_producto','LIKE','A%')
                                                                                                                            ->limit('10')
                                                                                                                            ->get();*/

                                                                                    //var_dump($reporte["datos"]);

                                                                                    $sheet->fromArray($arr_sin_cate);
                                                                            });
                                                                        })->store('xls', substr(base_path(),0,-8)."archivos/exportacion/excel");

                                                                     if(file_exists("archivos/exportacion/excel/productos_importados_repetidos_".explode(" ", $datos->hora_cliente)[0]).".xls"){

                                                                        $nom_arc="productos_importados_repetidos_".explode(" ", $datos->hora_cliente)[0]."_".explode(":",explode(" ", $datos->hora_cliente)[1])[2];
                                                                    }else{
                                                                         $nom_arc="productos_importados_repetidos_".explode(" ", $datos->hora_cliente)[0];   
                                                                    }      
                                                                        Excel::create("productos_importados_repetidos_".explode(" ", $datos->hora_cliente)[0], function($excel) use($arr_con_coincidencias_en_bd){
                                                                            // use($datos->datos->nombre_reporte)   
                                                                            $excel->sheet('repetidos',function($sheet) use($arr_con_coincidencias_en_bd){
                                                                                    //var_dump($id);
                                                                                    /*$datos=Producto::where('nombre_producto','LIKE','A%')
                                                                                                                            ->limit('10')
                                                                                                                            ->get();*/

                                                                                    //var_dump($reporte["datos"]);

                                                                                    $sheet->fromArray($arr_con_coincidencias_en_bd);
                                                                            });
                                                                        })->store('xls', substr(base_path(),0,-8)."archivos/exportacion/excel");
                                                                        echo json_encode(["respuesta"=>true,"mensaje"=>"Parece que no se ha registrado ningun producto","repetidos"=>"archivos/exportacion/excel/".$nom_arc,"sin_categoria"=>"archivos/exportacion/excel/".$nom_arc_SC]);
                                                                       //return response()->json(["respuesta"=>true,"mensaje"=>"productos registrados ","repetidos"=>$cod_repetidos,"sin_categoria"=>$arr_sin_cate]);

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
                                                                                ->where("codigo_producto","=",$value["codigo_producto"])
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
                                                                                            

                                                                                            /*
                                                                                                     if($dt[0]->tipo_venta_producto=="PorUnidad"){
                                                                                                 DB::table('movimientos_inventario')
                                                                                                    ->insertGetId(["fk_id_det_inventario"=>$dts[0]->id,
                                                                                                    "habia"=>$dts[0]->cantidad_existencias,
                                                                                                    "tipo"=>"ENTRADA",
                                                                                                    "descripcion"=>"unidad",    
                                                                                                    "fk_id_usuario"=>$datos->datos->id_usuario,
                                                                                                    "cantidad"=>$value["inventario"],
                                                                                                    "quedan"=>$value["inventario"]+$dts[0]->cantidad_existencias,
                                                                                                    "observaciones"=>"entrada importacon de productos ",
                                                                                                    "created_at"=>$datos->datos->hora_cliente,
                                                                                                    "updated_at"=>$datos->datos->hora_cliente        ]);  

                                                                                            }
                                                                                            else if($dts[0]->tipo_venta_producto=="Caja"){
                                                                                                 DB::table('movimientos_inventario')
                                                                                                    ->insertGetId(["fk_id_det_inventario"=>$dts[0]->id,
                                                                                                    "habia"=>$dts[0]->cantidad_existencias,
                                                                                                    "tipo"=>"ENTRADA",
                                                                                                    "descripcion"=>"caja",    
                                                                                                    "fk_id_usuario"=>$datos->datos->id_usuario,
                                                                                                    "cantidad"=>$value["inventario"],
                                                                                                    "quedan"=>$value["inventario"]+$dts[0]->cantidad_existencias,
                                                                                                    "observaciones"=>"entrada importacon de productos ",
                                                                                                    "created_at"=>$datos->datos->hora_cliente,
                                                                                                    "updated_at"=>$datos->datos->hora_cliente        ]); 

                                                                                                DB::table('movimientos_inventario')
                                                                                                    ->insertGetId(["fk_id_det_inventario"=>$dts[0]->id,
                                                                                                    "habia"=>$dts[0]->cantidad_existencias_unidades,
                                                                                                    "tipo"=>"ENTRADA",
                                                                                                    "descripcion"=>"unidad",    
                                                                                                    "fk_id_usuario"=>$datos->datos->id_usuario,
                                                                                                    "cantidad"=>$value["inventario"]*$dts[0]->unidades_por_caja+$value["sueltas"],                              
                                                                                                    "quedan"=>$value["inventario"]*$dts[0]->unidades_por_caja+$value["sueltas"]+$dts[0]->cantidad_existencias,
                                                                                                    "observaciones"=>"entrada importacon de productos ",
                                                                                                    "created_at"=>$datos->datos->hora_cliente,
                                                                                                    "updated_at"=>$datos->datos->hora_cliente        ]); 
                                                                                            }
                                                                                            else{

                                                                                                 DB::table('movimientos_inventario')
                                                                                                    ->insertGetId(["fk_id_det_inventario"=>$dts[0]->id,
                                                                                                    "habia"=>$dts[0]->cantidad_existencias,
                                                                                                    "tipo"=>"ENTRADA",
                                                                                                    "descripcion"=>"caja",    
                                                                                                    "fk_id_usuario"=>$datos->datos->id_usuario,
                                                                                                    "cantidad"=>$value["inventario"],
                                                                                                    "quedan"=>$value["inventario"]+$dts[0]->cantidad_existencias,
                                                                                                    "observaciones"=>"entrada importacon de productos ",
                                                                                                    "created_at"=>$datos->datos->hora_cliente,
                                                                                                    "updated_at"=>$datos->datos->hora_cliente        ]); 

                                                                                                DB::table('movimientos_inventario')
                                                                                                    ->insertGetId(["fk_id_det_inventario"=>$dts[0]->id,
                                                                                                    "habia"=>$dts[0]->cantidad_existencias_blister,
                                                                                                    "tipo"=>"ENTRADA",
                                                                                                    "descripcion"=>"blister",    
                                                                                                    "fk_id_usuario"=>$datos->datos->id_usuario,
                                                                                                    "cantidad"=>$uni_blister,                              
                                                                                                    "quedan"=>$uni_blister+$dts[0]->cantidad_existencias_blister,
                                                                                                    "observaciones"=>"entrada importacon de productos ",
                                                                                                    "created_at"=>$datos->datos->hora_cliente,
                                                                                                    "updated_at"=>$datos->datos->hora_cliente        ]); 
                                                                                                DB::table('movimientos_inventario')
                                                                                                    ->insertGetId(["fk_id_det_inventario"=>$dts[0]->id,
                                                                                                    "habia"=>$dts[0]->cantidad_existencias_unidades,
                                                                                                    "tipo"=>"ENTRADA",
                                                                                                    "descripcion"=>"unidad",    
                                                                                                    "fk_id_usuario"=>$datos->datos->id_usuario,
                                                                                                    "cantidad"=>$uni_blister*$dts[0]->unidades_por_blister+$value["sueltas"],                              
                                                                                                    "quedan"=>$uni_blister+$dts[0]->cantidad_existencias_blister+$value["sueltas"]+$dts[0]->cantidad_existencias_unidades,
                                                                                                    "observaciones"=>"entrada importacon de productos ",
                                                                                                    "created_at"=>$datos->datos->hora_cliente,
                                                                                                    "updated_at"=>$datos->datos->hora_cliente        ]); 
                                                                                            }                   
    
                                                                                            */
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

                                                                                            
                                                                                            /*
                                                                                            if($dt[0]->tipo_venta_producto=="PorUnidad"){
                                                                                                 DB::table('movimientos_inventario')
                                                                                                    ->insertGetId(["fk_id_det_inventario"=>$dts[0]->id,
                                                                                                     "habia"=>"0",
                                                                                                    "tipo"=>"ENTRADA",
                                                                                                    "descripcion"=>"unidad",    
                                                                                                    "fk_id_usuario"=>$datos->datos->id_usuario,
                                                                                                    "cantidad"=>$value["inventario"],
                                                                                                    "quedan"=>$value["inventario"]+$dts[0]->cantidad_existencias,
                                                                                                     "observaciones"=>"entrada inicial de importacon de productos",
                                                                                                    "created_at"=>$datos->datos->hora_cliente,
                                                                                                    "updated_at"=>$datos->datos->hora_cliente        ]);  

                                                                                            }
                                                                                            else if($dt[0]->tipo_venta_producto=="Caja"){
                                                                                                 DB::table('movimientos_inventario')
                                                                                                    ->insertGetId(["fk_id_det_inventario"=>$dts[0]->id,
                                                                                                    "habia"=>"0",
                                                                                                    "tipo"=>"ENTRADA",
                                                                                                    "descripcion"=>"caja",    
                                                                                                    "fk_id_usuario"=>$datos->datos->id_usuario,
                                                                                                    "cantidad"=>$value["inventario"],
                                                                                                    "quedan"=>$value["inventario"]+$dts[0]->cantidad_existencias,
                                                                                                      "observaciones"=>"entrada inicial de importacon de productos",
                                                                                                    "created_at"=>$datos->datos->hora_cliente,
                                                                                                    "updated_at"=>$datos->datos->hora_cliente        ]); 

                                                                                                DB::table('movimientos_inventario')
                                                                                                    ->insertGetId(["fk_id_det_inventario"=>$dts[0]->id,
                                                                                                     "habia"=>"0",
                                                                                                    "tipo"=>"ENTRADA",
                                                                                                    "descripcion"=>"unidad",    
                                                                                                    "fk_id_usuario"=>$datos->datos->id_usuario,
                                                                                                    "cantidad"=>$value["inventario"]*$dts[0]->unidades_por_caja+$value["sueltas"],                              
                                                                                                    "quedan"=>$value["inventario"]*$dts[0]->unidades_por_caja+$value["sueltas"]+$dts[0]->cantidad_existencias,
                                                                                                     "observaciones"=>"entrada inicial de importacon de productos",
                                                                                                    "created_at"=>$datos->datos->hora_cliente,
                                                                                                    "updated_at"=>$datos->datos->hora_cliente        ]); 
                                                                                            }
                                                                                            else{
                                                                                                  DB::table('movimientos_inventario')
                                                                                                    ->insertGetId(["fk_id_det_inventario"=>$dts[0]->id,
                                                                                                    "habia"=>$dts[0]->cantidad_existencias,
                                                                                                    "tipo"=>"ENTRADA",
                                                                                                    "descripcion"=>"caja",    
                                                                                                    "fk_id_usuario"=>$datos->datos->id_usuario,
                                                                                                    "cantidad"=>$value["inventario"],
                                                                                                    "quedan"=>$value["inventario"]+$dts[0]->cantidad_existencias,
                                                                                                    "observaciones"=>"entrada importacon de productos inicial",
                                                                                                    "created_at"=>$datos->datos->hora_cliente,
                                                                                                    "updated_at"=>$datos->datos->hora_cliente        ]); 

                                                                                                DB::table('movimientos_inventario')
                                                                                                    ->insertGetId(["fk_id_det_inventario"=>$dts[0]->id,
                                                                                                    "habia"=>$dts[0]->cantidad_existencias_blister,
                                                                                                    "tipo"=>"ENTRADA",
                                                                                                    "descripcion"=>"blister",    
                                                                                                    "fk_id_usuario"=>$datos->datos->id_usuario,
                                                                                                    "cantidad"=>$uni_blister,                              
                                                                                                    "quedan"=>$uni_blister+$dts[0]->cantidad_existencias_blister,
                                                                                                    "observaciones"=>"entrada importacon de productos inicial",
                                                                                                    "created_at"=>$datos->datos->hora_cliente,
                                                                                                    "updated_at"=>$datos->datos->hora_cliente        ]); 
                                                                                                DB::table('movimientos_inventario')
                                                                                                    ->insertGetId(["fk_id_det_inventario"=>$dts[0]->id,
                                                                                                    "habia"=>"0",
                                                                                                    "tipo"=>"ENTRADA",
                                                                                                    "descripcion"=>"unidad",    
                                                                                                    "fk_id_usuario"=>$datos->datos->id_usuario,
                                                                                                    "cantidad"=>$uni_blister*$dts[0]->unidades_por_blister+$value["sueltas"],                              
                                                                                                    "quedan"=>$uni_blister*$dts[0]->unidades_por_blister+$value["sueltas"]+$dts[0]->cantidad_existencias_unidades,
                                                                                                    "observaciones"=>"entrada importacon de productos inicial",
                                                                                                    "created_at"=>$datos->datos->hora_cliente,
                                                                                                    "updated_at"=>$datos->datos->hora_cliente        ]); 
                                                                                            }
                                                                                            */
                                                                                         }

                                                                                     }
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

                                        ini_set('max_execution_time', 6000); //900 seconds = 5 minutes
                                        //linea para impedir error de memoria
                                        ini_set('memory_limit', '-1'); 
                                        $div_l=ceil(count($arr)/2);
                                        $lote_productos_excel = array_chunk($arr, $div_l);
                                        $editar=[];
                                        $i=0;
                                        $repetidos=[];
                                         foreach ($lote_productos_excel as $key => $lpx) {
                                                   
                                                    foreach ($lpx as $key => $value) {

                                                          
                                                        if(array_key_exists("codigo_coopidrogas",$value) && $value["codigo_coopidrogas"]!==NULL){

                                                            if(array_key_exists("nuevo_codigo", $value) && $value["nuevo_codigo"]!==NULL){

                                                               
                                                                $d=DB::table("productos")
                                                                    ->where("codigo_producto","=",$value["nuevo_codigo"])
                                                                    ->get();
                                                                if(count($d)==0){
                                                                            $editar[$i]["codigo_distribuidor"]=$value["nuevo_codigo"]; 
                                                                            $editar[$i]["codigo_producto"]=$value["nuevo_codigo"]; 
                                                                            
                                                                            if(array_key_exists("nombre_producto",$value) && $value["nombre_producto"]!==NULL){
                                                                                       $editar[$i]["nombre_producto"]=$value["nombre_producto"]; 
                                                                            }
                                                                            
                                                                            if(array_key_exists("descripcion_producto",$value) && $value["descripcion_producto"]!==NULL){
                                                                                   $editar[$i]["nombre_producto"]=$value["descripcion_producto"]; 
                                                                                   $editar[$i]["nombre_producto_venta"]=$value["descripcion_producto"];
                                                                            }


                                                                            if(array_key_exists("laboratorio",$value) && $value["laboratorio"]!=NULL){
                                                                                   $editar[$i]["laboratorio"]=$value["laboratorio"]; 
                                                                            }


                                                                            if(array_key_exists("tipo_venta",$value) && $value["tipo_venta"]!==NULL){
                                                                                   $editar[$i]["tipo_venta_producto"]=$value["tipo_venta"]; 
                                                                            }


                                                                            if(array_key_exists("numero_de_unidades_presentacion",$value) && $value["numero_de_unidades_presentacion"]!==NULL){

                                                                                   /*$editar[$i]["unidades_por_caja"]=$value["numero_de_unidades_presentacion"]; */

                                                                            }


                                                                            if(array_key_exists("unidades_por_blister",$value) && $value["unidades_por_blister"]!==NULL){

                                                                                   /*$editar[$i]["unidades_por_blister"]=$value["unidades_por_blister"];*/ 
                                                                            }


                                                                            if(array_key_exists("tipo_presentacion",$value) && $value["tipo_presentacion"]!==NULL){
                                                                                   $editar[$i]["tipo_presentacion"]=$value["tipo_presentacion"]; 
                                                                            }

                                                                             
                                                                            if(array_key_exists("precio_costo",$value) && $value["precio_costo"]!==NULL && $value["precio_costo"]!=="0"){
                                                                                   //var_dump($value["precio_costo"]);
                                                                                   $editar[$i]["precio_compra"]=$value["precio_costo"]; 
                                                                            }
                                                                            //var_dump($value["precio_costo_blister"]);
                                                                            if(array_key_exists("precio_costo_blister",$value) && $value["precio_costo_blister"]!==NULL && $value["precio_costo_blister"]!=="#DIV/0!"){

                                                                                   $editar[$i]["precio_compra_blister"]=$value["precio_costo_blister"]; 
                                                                            }


                                                                            if(array_key_exists("precio_costo_unidad_blister",$value) && $value["precio_costo_unidad_blister"]!==NULL && $value["precio_costo_unidad_blister"]!=="#DIV/0!"){
                                                                                   $editar[$i]["precio_compra_unidad"]=$value["precio_costo_unidad_blister"]; 
                                                                            }


                                                                            if(array_key_exists("precio_venta",$value) && $value["precio_venta"]!==NULL && $value["precio_venta"]!=="#DIV/0!"){
                                                                                   $editar[$i]["precio_venta"]=$value["precio_venta"]; 
                                                                            }


                                                                            if(array_key_exists("precio_venta_blister",$value) && $value["precio_venta_blister"]!==NULL && $value["precio_venta_blister"]!=="#DIV/0!"){
                                                                                   $editar[$i]["precio_venta_blister"]=$value["precio_venta_blister"]; 
                                                                            }


                                                                            if(array_key_exists("precio_venta_unidad_blister",$value) && $value["precio_venta_unidad_blister"]!==NULL && $value["precio_venta_unidad_blister"]!=="#DIV/0!"){
                                                                                   $editar[$i]["precio_mayoreo"]=$value["precio_venta_unidad_blister"]; 
                                                                            }


                                                                            if(array_key_exists("porcentaje_ganancia",$value) && $value["porcentaje_ganancia"]!==NULL  && $value["porcentaje_ganancia"]!=="0"){
                                                                                   $editar[$i]["porcentaje_ganancia"]=$value["porcentaje_ganancia"]; 
                                                                            }


                                                                            if(array_key_exists("porcentaje_ganancia_blister",$value) && $value["porcentaje_ganancia_blister"]!==NULL  && $value["porcentaje_ganancia_blister"]!=="0"){
                                                                                   $editar[$i]["porcentaje_ganancia_blister"]=$value["porcentaje_ganancia_blister"]; 
                                                                            }


                                                                            if(array_key_exists("porcentaje_ganancia_unidad_blister",$value) && $value["porcentaje_ganancia_unidad_blister"]!==NULL  && $value["porcentaje_ganancia_unidad_blister"]!=="0"){
                                                                                   $editar[$i]["porcentaje_ganancia_unidad"]=$value["porcentaje_ganancia_unidad_blister"]; 
                                                                            }


                                                                            if(array_key_exists("minimo_inventario",$value) && $value["minimo_inventario"]!==NULL){
                                                                                   $editar[$i]["minimo_inventario"]=$value["minimo_inventario"]; 
                                                                            }


                                                                            if(array_key_exists("maximo_inventario",$value) && $value["maximo_inventario"]!==NULL){
                                                                                   $editar[$i]["maximo_inventario"]=$value["maximo_inventario"]; 
                                                                            }


                                                                            if(array_key_exists("grupo",$value) && $value["grupo"]!==NULL){
                                                                                   $editar[$i]["grupo"]=$value["grupo"]; 
                                                                            }


                                                                            if(array_key_exists("sub_grupo",$value) && $value["sub_grupo"]!==NULL){
                                                                                   $editar[$i]["sub_grupo"]=$value["sub_grupo"]; 
                                                                            }


                                                                            if(array_key_exists("impuesto",$value) && $value["impuesto"]!==NULL){
                                                                                   $editar[$i]["impuesto"]=$value["impuesto"]; 
                                                                            }
                                                                     DB::table('productos')
                                                                        ->where('codigo_distribuidor',"LIKE", $value["codigo_coopidrogas"])
                                                                        //->orwhere('codigo_producto', $value["codigo_coopidrogas"])
                                                                        ->update($editar[$i]);          
                                                                     //var_dump($editar[$i]);
                                                                     //echo "=========";              
                                                                       $i++;  
                                                                }else{
                                                                    //var_dump($value);
                                                                    //echo "==============";
                                                                    $repetidos[$i]=$value;    
                                                                    $i++; 
                                                                }

                                                                    
                                                                    
                                                                

                                                            }

                                                            
                                                            
                                                                                       
                                                        } 


                                                    }

                                                  

                                         }

                                         if(count($repetidos)>0){
                                            Excel::create("codigos_repetidos", function($excel) use($repetidos){
                                                                                                // use($datos->datos->nombre_reporte)   
                                                                                                $excel->sheet('codigos_repetidos',function($sheet) use($repetidos){
                                                                                                        //var_dump($id);
                                                                                                        /*$datos=Producto::where('nombre_producto','LIKE','A%')->limit('10')->get();*/

                                                                                                        //var_dump($reporte["datos"]);

                                                                                                        $sheet->fromArray($repetidos);
                                                                                                });
                                                                                            })->store('xls', substr(base_path(),0,-8)."archivos/exportacion/excel");
   
                                         }

                                         


                                         echo json_encode(["respuesta"=>true,"mensaje"=>"productos editados","no_existen"=>"archivos/exportacion/excel/codigos_repetidos"]);
                                        break;  
                                    
                                   case "ajustar_inventario_sede":

                                        if($datos->datos->sede!="0" && $datos->datos->sede!="--"){
                                            ini_set('max_execution_time', 6000); //900 seconds = 5 minutes
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

                                                            if(count($arr)>0){
                                                                   foreach ($arr as $key => $value) {

                                                                           
                                                                            $dt=DB::table('productos')
                                                                                ->where("codigo_producto","=",$value["codigo_producto"])
                                                                                ->orwhere("codigo_distribuidor","=",$value["codigo_producto"])
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
                                                                                $act_precios=[];
                                                                                   if($value["precio_venta"]!=NULL){

                                                                                            if((int)$dts[0]->precio_venta==0){

                                                                                                $precio=1;

                                                                                            }else{

                                                                                                $precio=(int)$dts[0]->precio_venta;

                                                                                            }

                                                                                            $act_precios["precio_venta_sede"]=$value["precio_venta"];
                                                                                            $DIF=(int)$value["precio_venta"]-$dts[0]->precio_compra;
                                                                                            $act_precios["porcentaje_ganancia_sede"]=round((($DIF)*100)/$precio,2);
                                                                                           

                                                                                    }   

                                                                                    if($value["precio_venta_blister"]!=NULL){
                                                                                            if((int)$dts[0]->precio_venta_blister==0){
                                                                                                $precio_b=1;
                                                                                            }else{
                                                                                                $precio_b=(int)$dts[0]->precio_venta_blister;
                                                                                            }
                                                                                             $act_precios["precio_venta_blister_sede"]=$value["precio_venta_blister"];
                                                                                             $DIF=(int)$value["precio_venta_blister"]-$dts[0]->precio_compra_blister;
                                                                                             $act_precios["porcentaje_ganancia_blister_sede"]=round((($DIF)*100)/$precio_b,2);
                                                                                    }

                                                                                    if($value["precio_venta_unidad_blister"]!=NULL){
                                                                                            if((int)$dts[0]->precio_mayoreo_sede==0){
                                                                                                $precio_u=1;
                                                                                            
                                                                                            }else{
                                                                                                $precio_u=(int)$dts[0]->precio_mayoreo_sede;
                                                                                            } 

                                                                                           $act_precios["precio_mayoreo_sede"]=$value["precio_venta_unidad_blister"];
                                                                                           $DIF=(int)$value["precio_venta_unidad_blister"]-$dts[0]->precio_compra_unidad;
                                                                                           $act_precios["porcentaje_ganancia_sede_unidad"]=round((($DIF)*100)/$precio_u,2);
                                                                                    }
                                                                                    

                                                                                         //ACTUALIZAR CANTIDAD DE PRODUCTOS 
                                                                                         //UNIDADES BLISTER UNIDAD
                                                                                    if(count($act_precios)>0){
                                                                                            DB::table("detalle_inventarios")
                                                                                                  ->where("id","=",$dts[0]->id)  
                                                                                                  ->update($act_precios);
                                                                                    }
                                                                                        $act=[];
                                                                                         $ia=0;


                                                                                         if($value["unidades_por_caja"]!=null){

                                                                                              $act["unidades_por_caja"]=$value["unidades_por_caja"];

                                                                                              $act["precio_compra_blister"]=round($dts[0]->precio_compra/$value["unidades_por_caja"],2);
                                                                                              
                                                                                         }

                                                                                         if($value["unidades_por_blister"]!=null){

                                                                                            $act["unidades_por_blister"]=$value["unidades_por_blister"]; 

                                                                                            $act["precio_compra_unidad"]=round(($dts[0]->precio_compra/$value["unidades_por_caja"])/$value["unidades_por_blister"],2); 
                                                                                         }


                                                                                         if(array_key_exists("precio_venta_sede",$act_precios)){

                                                                                            $act["precio_venta"]=$act_precios["precio_venta_sede"];

                                                                                            $diff=(int)$act_precios["precio_venta_sede"]-$dts[0]->precio_compra;    


                                                                                            $act["porcentaje_ganancia"]=round(($diff*100)/$act_precios["precio_venta_sede"],2);

                                                                                         }

                                                                                         if(array_key_exists("precio_venta_blister_sede",$act_precios)){
                                                                                            
                                                                                            $act["precio_venta_blister"]=$act_precios["precio_venta_blister_sede"];

                                                                                            $diff=(int)$act_precios["precio_venta_blister_sede"]-$dts[0]->precio_compra_blister;    


                                                                                            $act["porcentaje_ganancia_blister"]=round(($diff*100)/$value["precio_venta_blister"],2);

                                                                                         }

                                                                                         if(array_key_exists("precio_mayoreo_sede",$act_precios)){
                                                                                             
                                                                                             $act["precio_mayoreo"]=$act_precios["precio_mayoreo_sede"];

                                                                                             $diff=(int)$value["precio_venta_unidad_blister"]-$dts[0]->precio_compra_unidad;    


                                                                                             $act["porcentaje_ganancia_unidad"]=round(($diff*100)/$value["precio_venta_unidad_blister"],2);
                                                                                         }

                                                                                         if(count($act)>0){
                                                                                          
                                                                                            DB::table("productos")
                                                                                                ->where("id","=",$dts[0]->fk_id_producto)
                                                                                                ->update($act);
                                                                                         }

                                                                                     //VALIDO SI EXISTEN EN LA SEDE AL COMPROBAR SI HAY O
                                                                                     // NO REGISTROS EN LA CONSULTA   

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
                                                            
                                                            Excel::create("NoExisten", function($excel) use($arr_no_existe){

                                                                         $excel->sheet('no_existen',function($sheet) use($arr_no_existe){
                                                                                      $sheet->fromArray($arr_no_existe);
                                                                                });
                                                            })->store('xls', substr(base_path(),0,-8)."archivos/exportacion/excel");   


                                                             echo json_encode(["mensaje"=>"Inventario ajustado ","respuesta"=>true,"no_existen"=>"archivos/exportacion/excel/NoExisten"]);

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
   
}
