<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use App\Factura;

use App\DetalleFactura;

use App\DetalleInventario;

use App\MovimientosInventario;

use App\DetalleEntradaContable;

use Mail;

use DB;

class FacturaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $fac=new Factura();
        return response()->json($fac->consultar_todos());

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
    public function store(Request $request){
        $datos=json_decode($request->get("datos"));
         $vendedor=DB::table("users")->where("codigo_venta","=",$datos->datos->fk_id_vendedor)->get();
        $fac=new Factura();
        if(count($vendedor)>0){
            $id_v=$vendedor[0]->id;
        }else{
            $id_v=$datos->datos->id_usuario;
        }
            $ff=DB::table('facturas')
                    ->where("numero_factura","=",$datos->datos->numero_factura)
                    ->get();
            if(count($ff)>0){
                $datos->datos->numero_factura."(".count($ff).")";
            }
             $r=$fac->insertar(array(
            "numero_factura"=>$datos->datos->numero_factura,
            "fk_id_vendedor"=>$id_v,
            "fk_id_cliente"=>$datos->datos->fk_id_cliente,
            "fk_id_sede"=>$datos->datos->fk_id_sede,
            "registro_factura"=>$datos->hora_cliente,
            "created_at"=>$datos->hora_cliente,
            "updated_at"=>$datos->hora_cliente, 
            "estado_factura"=>"pendiente",
            "valor_real_factura"=>"0",
            ));
             $dat=DB::table("facturas")
                ->where([
                        ["fk_id_sede","=",$datos->datos->fk_id_sede],
                        ["estado_factura","=","pendiente"],
                        ["fk_id_vendedor","=",$datos->datos->id_usuario]
                    ])
                ->get();
                $arr=array();
                $i=0;
                foreach ($dat as $key => $value) {
                    $arr[$i]=(array)$value;
                    $arr[$i]["productos"]=DB::table("detalle_facturas")

                        ->join("detalle_inventarios","detalle_inventarios.id","=","detalle_facturas.fk_id_producto")                              
                        ->join("productos","productos.id","=","detalle_inventarios.fk_id_producto")
                        ->where("fk_id_factura","=",$value->id)
                         ->select(
                                                    "detalle_facturas.id as id_factura",
                                                    "detalle_facturas.cantidad_producto",
                                                    "detalle_facturas.tipo_venta",
                                                    "detalle_facturas.descuento",
                                                    "detalle_facturas.valor_item",
                                                    "detalle_inventarios.cantidad_existencias",
                                                    "detalle_inventarios.cantidad_existencias_blister",
                                                    "detalle_inventarios.cantidad_existencias_unidades",
                                                    "detalle_inventarios.precio_venta_sede",
                                                    "detalle_inventarios.precio_venta_blister_sede",
                                                    "detalle_inventarios.precio_mayoreo_sede",
                                                    "detalle_inventarios.minimo_inventario_sede",
                                                    "detalle_inventarios.estado_inventario",
                                                    "detalle_inventarios.unidades_reservadas",
                                                    "detalle_inventarios.id as id_producto_inventario",
                                                    "productos.codigo_producto",
                                                    "productos.nombre_producto",
                                                    "productos.tipo_venta_producto",
                                                    "productos.id"

                                                )
                        ->get();
                     //validar existencias minimas y enviar correo en caso de que se llegue al tope
                          

                     $i++;



                }
                         
                return response()->json(["respuesta"=>true,"mensaje"=>"Factura # ".$datos->datos->numero_factura.", se ha registrado","datos"=>$arr]);
    }
    

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        
        //$fac=new Factura();
        //return response()->json($fac->consultar_por_campo(array(array("numero_factura","=",$id)),"OR",array(array("id","=",$id))));
        
        $fac=DB::table("facturas")
                ->where("numero_factura","=",$id)
                ->orwhere("id","=",$id)
                ->get();
        $arr=array();
        $i=0;
        foreach ($fac as $key => $value) {
            $arr[$i]=(array)$value;
                
            $arr[$i]["detalle_factura"]=DB::table("detalle_facturas")
                                        ->join("detalle_inventarios","detalle_facturas.fk_id_producto","=","detalle_inventarios.id")
                                        ->join("productos","detalle_inventarios.fk_id_producto","=","productos.id")
                                        ->where("fk_id_factura","=",$value->id)
                                        ->select("productos.id","productos.codigo_producto",
                                                "detalle_facturas.tipo_venta",
                                                "productos.nombre_producto",
                                                "productos.unidades_por_caja",
                                                "productos.unidades_por_blister",
                                                "detalle_facturas.cantidad_producto",
                                                "detalle_facturas.valor_item",
                                                "detalle_facturas.id as id_detalle")    
                                        ->get();
            $i++;
        }
            return response()->json(["datos"=>$arr,"respuesta"=>true,"mensaje"=>"facturas encontradas"]);
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
        $fac=new Factura();
        return response()->json($fac->editar(array(
            "numero_factura"=>$datos->datos->numero_factura,
            "fk_id_vendedor"=>$datos->datos->fk_id_vendedor,
            "fk_id_cliente"=>$datos->datos->fk_id_cliente,
            "updated_at"=>$datos->hora_cliente, 

            ),array("id","=",$id)));
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
        //buscar items de la factura
         //devolver las unidades 
         // elimnar el detalle factura
     

          $dat=DB::table("facturas")
                ->join("detalle_facturas","detalle_facturas.fk_id_factura","=","facturas.id")
                ->join("detalle_inventarios","detalle_inventarios.id","=","detalle_facturas.fk_id_producto")
                ->join("productos","productos.id","=","detalle_inventarios.fk_id_producto")
                ->where("facturas.id","=",$id)
                ->select("detalle_inventarios.id",
                    "detalle_facturas.cantidad_producto",
                    "detalle_facturas.valor_item",
                    "detalle_facturas.tipo_venta",
                    "facturas.fk_id_vendedor",
                          "productos.unidades_por_caja",
                          "productos.unidades_por_blister",
                          "detalle_facturas.id as id_fac"  )
                ->get();
        
        //Aqui devuelvo las unidades
        
        foreach ($dat as $key => $value) {
            //var_dump($value->id_fac);
            DB::table("detalle_facturas")
                ->where("id","=",$value->id_fac)
                ->delete();
        }        

        //CONSULTAR TICKET PENDIENTES DE LA MISMA SEDE Y
            $c=DB::table("facturas")
              ->where("estado_factura","=","pendiente")
              ->get();
              
            if(count($c)>1){
              $fac=new Factura();
             // $R=$fac->eliminar([["id","=",$id]]);
              
              
                $dat=DB::table("facturas")
                    ->where([
                            ["fk_id_sede","=",$c[0]->fk_id_sede],
                            ["estado_factura","=","pendiente"],
                            ["fk_id_vendedor","=",$c[0]->fk_id_vendedor]
                        ])
                    ->get();
                $arr=array();
                $i=0;
                foreach ($dat as $key => $value) {
                    $arr[$i]=(array)$value;
                    $arr[$i]["productos"]=DB::table("detalle_facturas")

                       ->join("detalle_inventarios","detalle_inventarios.id","=","detalle_facturas.fk_id_producto")
                      
                       ->join("productos","productos.id","=","detalle_inventarios.fk_id_producto")
                       
                        ->where("fk_id_factura","=",$value->id)
                        ->select(
                                                    "detalle_facturas.id as id_factura",
                                                    "detalle_facturas.cantidad_producto",
                                                    "detalle_facturas.tipo_venta",
                                                    "detalle_facturas.descuento",
                                                    "detalle_facturas.valor_item",
                                                    "detalle_inventarios.cantidad_existencias",
                                                    "detalle_inventarios.cantidad_existencias_blister",
                                                    "detalle_inventarios.cantidad_existencias_unidades",
                                                    "detalle_inventarios.precio_venta_sede",
                                                    "detalle_inventarios.precio_venta_blister_sede",
                                                    "detalle_inventarios.precio_mayoreo_sede",
                                                    "detalle_inventarios.minimo_inventario_sede",
                                                    "detalle_inventarios.estado_inventario",
                                                    "detalle_inventarios.unidades_reservadas",
                                                    "detalle_inventarios.id as id_producto_inventario",
                                                    "productos.codigo_producto",
                                                    "productos.nombre_producto",
                                                    "productos.tipo_venta_producto",
                                                    "productos.id",
                                                    "productos.unidades_por_blister",
                                                    "productos.unidades_por_caja"

                                                )
                        ->get();
                     $i++;   
                }
              

              return response()->json(["mensaje"=>"ticket eliminado","respuesta"=>true,"datos"=>$arr]);  
            }
            else{
                 $dat=DB::table("facturas")
                    ->where([
                            ["fk_id_sede","=",$c[0]->fk_id_sede],
                            ["estado_factura","=","pendiente"],
                            ["fk_id_vendedor","=",$c[0]->fk_id_vendedor]
                        ])
                    ->get();
                  $arr=array();
                $i=0;
                  foreach ($dat as $key => $value) {
                    $arr[$i]=(array)$value;
                    $arr[$i]["productos"]=DB::table("detalle_facturas")

                       ->join("detalle_inventarios","detalle_inventarios.id","=","detalle_facturas.fk_id_producto")
                      
                       ->join("productos","productos.id","=","detalle_inventarios.fk_id_producto")
                       
                        ->where("fk_id_factura","=",$value->id)
                        ->select(
                                                    "detalle_facturas.id as id_factura",
                                                    "detalle_facturas.cantidad_producto",
                                                    "detalle_facturas.tipo_venta",
                                                    "detalle_facturas.descuento",
                                                    "detalle_facturas.valor_item",
                                                    "detalle_inventarios.cantidad_existencias",
                                                    "detalle_inventarios.cantidad_existencias_blister",
                                                    "detalle_inventarios.cantidad_existencias_unidades",
                                                    "detalle_inventarios.precio_venta_sede",
                                                    "detalle_inventarios.precio_venta_blister_sede",
                                                    "detalle_inventarios.precio_mayoreo_sede",
                                                    "detalle_inventarios.minimo_inventario_sede",
                                                    "detalle_inventarios.estado_inventario",
                                                    "detalle_inventarios.unidades_reservadas",
                                                    "detalle_inventarios.id as id_producto_inventario",
                                                    "productos.codigo_producto",
                                                    "productos.nombre_producto",
                                                    "productos.tipo_venta_producto",
                                                    "productos.id",
                                                    "productos.unidades_por_blister",
                                                    "productos.unidades_por_caja"

                                                )
                        ->get();
                     $i++;   
                }
              return response()->json(["mensaje"=>"ticket eliminado","respuesta"=>true,"datos"=>$arr]);  
            }  
            
       
        //USUARIO QUE TIENE ESTA FACTURA Y EN CASO DE NO ETNER SINO SOLO UNA CREAR 
        
    }
    
    //funcion para buscar las facturas de una fecha determinada
    public function facturas_del_dia(Request $request) {
        
                $datos=json_decode($request->get("datos"));
                
                $dia=$datos->datos->dia;
                $sede=$datos->datos->sede;
            
                    $fac=DB::table("facturas")
                         ->where([

                                ["fk_id_sede","=",$sede],
                                ["registro_factura",">=",$dia." 00:00:00"],
                                ["registro_factura","<=",$dia." 23:59:59"],

                            ])
                        ->get();   

            if(count($fac)>0){
                return response()->json(["mensaje"=>"facturas encontradas","datos"=>$fac,"respuesta"=>true]); 
            }
            
                return response()->json(["mensaje"=>"facturas NO encontradas","respuesta"=>false]);
             
    }

    public function obtener_tickets_pendientes($id_sede,$id_usuario,$num_factura){
        
            $dat=DB::table("facturas")
                ->where([
                        ["fk_id_sede","=",$id_sede],
                        ["estado_factura","=","pendiente"],
                        ["fk_id_vendedor","=",$id_usuario]
                    ])
                ->get();
              
            if(count($dat)==0){
                $fac=new Factura();
            
                $id_v=$id_usuario;
                $r=$fac->insertar(array(
                    "numero_factura"=>$num_factura,
                    "fk_id_vendedor"=>$id_usuario,
                    "fk_id_cliente"=>"1",
                    "fk_id_sede"=>$id_sede,
                    "estado_factura"=>"pendiente",
                    "valor_real_factura"=>"0",
                ));
                 $dat=DB::table("facturas")
                    ->where([
                            ["fk_id_sede","=",$id_sede],
                            ["estado_factura","=","pendiente"],
                            ["fk_id_vendedor","=",$id_usuario]
                        ])
                    ->get();
                    
                    return response()->json(["mensaje"=>"facturas encontradas","respuesta"=>true,"datos"=>$dat]);

            }else{
                $arr=array();
                    $i=0;

                    foreach ($dat as $key => $value) {
                        $arr[$i]=(array)$value;
                        $arr[$i]["productos"]=DB::table("detalle_facturas")
                                               ->join("detalle_inventarios","detalle_inventarios.id","=","detalle_facturas.fk_id_producto")
                                               ->join("productos","productos.id","=","detalle_inventarios.fk_id_producto")
                                                  ->where([["detalle_facturas.fk_id_factura","=",$value->id]])
                                                  ->select(
                                                    "detalle_facturas.id as id_factura",
                                                    "detalle_facturas.cantidad_producto",
                                                    "detalle_facturas.tipo_venta",
                                                    "detalle_facturas.descuento",
                                                    "detalle_facturas.valor_item",
                                                    "detalle_inventarios.cantidad_existencias",
                                                    "detalle_inventarios.cantidad_existencias_blister",
                                                    "detalle_inventarios.cantidad_existencias_unidades",
                                                    "detalle_inventarios.precio_venta_sede",
                                                    "detalle_inventarios.precio_venta_blister_sede",
                                                    "detalle_inventarios.precio_mayoreo_sede",
                                                    "detalle_inventarios.minimo_inventario_sede",
                                                    "detalle_inventarios.estado_inventario",
                                                    "detalle_inventarios.unidades_reservadas",
                                                    "detalle_inventarios.id as id_producto_inventario",
                                                    "detalle_inventarios.precio_promo_venta",
                                                    "detalle_inventarios.promocion",
                                                    "detalle_inventarios.promo_desde",
                                                    "detalle_inventarios.promo_hasta",                              
                                                    "detalle_inventarios.tipo_venta_promo",
                                                    "productos.codigo_producto",
                                                    "productos.nombre_producto",
                                                    "productos.tipo_venta_producto",
                                                    "productos.id",
                                                    "productos.unidades_por_blister",
                                                    "productos.unidades_por_caja",
                                                    "productos.inventario"      
                                                          



                                                )
                                               ->get(); 

                        $i++;

                    }
                return response()->json(["mensaje"=>"facturas encontradas","respuesta"=>true,"datos"=>$arr]);   
            }    
                
              
        
    }
    public function registro_facturas(Request $request){
        $datos=json_decode($request->get("datos"));
        //actualzizar el estado de la factura 
        // actualziar unidades
        $fact=DB::table("facturas")
           ->where("id","=",$datos->datos->id)
           ->get();
        if($fact[0]->estado_factura!="paga"){
            DB::table("facturas")
                ->where("id","=",$datos->datos->id)
                ->update(["estado_factura"=>"paga",
                          "valor_real_factura"=>(float)$datos->datos->valor_real_factura,
                          "updated_at"=>$datos->hora_cliente,
                          "created_at"=>$datos->hora_cliente,
                          "registro_factura"=>$datos->hora_cliente]);

          $f=DB::table("facturas")
            ->where("id","=",$datos->datos->id)
            ->get();
          
          $id_dt_fac=DB::table("detalle_entrada_contables")
            ->insertGetId(["fk_id_entrada_contable"=>"1",
                      "fk_id_usuario"=>$datos->datos->fk_id_vendedor,
                      "fk_id_sede"=>$datos->datos->fk_id_sede,
                      "valor_entrada"=>(float)$datos->datos->valor_real_factura,
                      "fecha_entrada"=>$datos->hora_cliente,
                      "updated_at"=>$datos->hora_cliente,
                      "created_at"=>$datos->hora_cliente,
              ]);

          DB::table("detalle_entrada_contable_factura")
          ->insert(["fk_id_entrada_contable"=>$id_dt_fac,
                    "fk_id_factura_venta"=>$datos->datos->id,
                    "updated_at"=>$datos->hora_cliente,
                    "created_at"=>$datos->hora_cliente]);  

        

          //aqui descuento las unidades y recalculo las existentes

          foreach ($datos->datos->productos as $key => $value) {
              
              //si tipo de venta es unidad descontar unidades
              //si tipo de venat es blister descontar unidades * numero de unidades por blister
              //si tipo de venta es caja descontar unidades * numero de caja  
              //var_dump($value);
              
             $habia=DB::table("detalle_inventarios")
                         ->join("productos","productos.id","=","detalle_inventarios.fk_id_producto")
                         ->where(
                                "detalle_inventarios.id","=",$value->id_producto_inventario
                            )
                           
                         ->get();   
                 
             $total=0;
             if($value->inventario==1){ 
                switch ($value->tipo_venta) {
                   case 'unidad':
                        
                           $total=(int)$value->cantidad_producto; 
                           DB::table("detalle_inventarios")
                           ->where("id","=",$value->id_producto_inventario)
                           ->decrement("cantidad_existencias_unidades",$total);
                     
                     break;
                   case 'blister':
                           $total=(int)$value->cantidad_producto*(int)$value->unidades_por_blister;
                           DB::table("detalle_inventarios")
                           ->where("id","=",$value->id_producto_inventario)
                           ->decrement("cantidad_existencias_unidades",$total);
                     # code...
                     
                     break;
                   case 'caja':
                         $total=(int)$value->unidades_por_blister*(int)$value->unidades_por_caja;    
                         DB::table("detalle_inventarios")
                           ->where("id","=",$value->id_producto_inventario)
                           ->decrement("cantidad_existencias_unidades",$total);
                        
                     break;

                 } 


                  $pp=DB::table("detalle_inventarios")
                            ->join("productos","productos.id","=","detalle_inventarios.fk_id_producto")
                            ->where([["detalle_inventarios.id","=",$value->id_producto_inventario],["detalle_inventarios.minimo_inventario_sede","<=","detalle_inventarios.cantidad_existencias_unidades"]])
                            ->orwhere([["detalle_inventarios.id","=",$value->id_producto_inventario],["detalle_inventarios.cantidad_existencias_unidades","=",0]])
                            ->get();   
                 
                
                 if(count($pp)>0){
                     
                      if($pp[0]->cantidad_existencias_unidades==0){
                          $men="ALERTA PRODUCTO ".$value->nombre_producto." AGOTADO";
                           DB::table("detalle_inventarios")
                            ->where("id","=",$value->id_producto_inventario)
                            ->update(["estado_inventario"=>"agotado"]);
                      }else{
                          $men="ALERTA PRODUCTO ".$value->nombre_producto." BAJO EN INVENTARIO";
                      }
                      $noti=DB::table("notificaciones")
                          ->where([["fk_id_sede","=",$datos->datos->fk_id_sede],["trabajo","=","BajoInventario"]])
                              ->get();
                      $sede=DB::table("sedes")
                          ->where("id","=",$datos->datos->fk_id_sede)
                              ->get();
                      $destinos= explode(",", $noti[0]->correos);
                      
                      foreach ($destinos as $key => $destino) {
                        Mail::send("email.producto_bajo",["datos"=>$pp[0],"sede"=>$sede[0]],function($msn) use($destino,$men){
                                $msn->from('erp@asopharma.com',"ERP ASOPHARMA");
                                $msn->to($destino);
                                
                                
                                $msn->subject($men);
                        });  
                      }
                        
                      
                 }

                  $quedan=DB::table("detalle_inventarios")
                       ->where("id","=",$value->id_producto_inventario)
                       ->get();    

                   DB::table("detalle_inventarios")
                     ->where("id","=",$value->id_producto_inventario)
                     ->update(["cantidad_existencias_blister"=>floor((int)$quedan[0]->cantidad_existencias_unidades/(int)$value->unidades_por_blister),
                           "cantidad_existencias"=>floor(((int)$quedan[0]->cantidad_existencias_unidades/(int)$value->unidades_por_blister)/$value->unidades_por_caja)]);    

                  //REGISTRO MOVIMIENTOS INVENTARIO
                  
                  DB::table("movimientos_inventario")
                      ->insert(["fk_id_det_inventario"=>$value->id_producto_inventario,
                                "habia"=>$habia[0]->cantidad_existencias_unidades,
                                "tipo"=>"SALIDA",
                                "descripcion"=>$value->tipo_venta,
                                "cantidad"=>$total,
                                "quedan"=>$quedan[0]->cantidad_existencias_unidades,
                                "observaciones"=>"Registro venta factura ".$f[0]->numero_factura,
                                "fk_id_usuario"=>$datos->datos->fk_id_vendedor,
                                "updated_at"=>$datos->hora_cliente,
                                "created_at"=>$datos->hora_cliente  ]);   


                  
              }
              
          }
          

          return response()->json(["mensaje"=>"factura registrada","respuesta"=>true]);                
        }else{
            return response()->json(["mensaje"=>"esta factura ya esta registrada","respuesta"=>false]);                
        }
        

    }    
}
