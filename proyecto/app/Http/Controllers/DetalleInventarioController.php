<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use App\DetalleInventario;

use App\MovimientosInventario;

use DB;


class DetalleInventarioController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $di=new DetalleInventario();
        return response()->json($di->consultar_todos());
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
        $di=new DetalleInventario();
        $datos=json_decode($request->get("datos"));
        $pr=DB::table('productos')
                ->where("id","=",$datos->datos->fk_id_producto)
                ->get();
                
        return response()->json($di->insertar([
            "fk_id_producto"=>$datos->datos->fk_id_producto,
            "fk_id_sede"=>$datos->datos->fk_id_sede,
            "fecha_caducidad"=>$datos->datos->fecha_caducidad,
            "cantidad_existencias"=>$datos->datos->cantidad_existencias,
            "cantidad_existencias_unidades"=>$datos->datos->cantidad_existencias*$pr[0]->unidades_por_caja,
             "cantidad_devueltas"=>$datos->datos->cantidad_devueltas, 
             "created_at"=>$datos->hora_cliente,
            "updated_at"=>$datos->hora_cliente,   
            "fk_id_usuario"=>$datos->datos->fk_id_usuario,

            ]));
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
        $di=new DetalleInventario();
        return response()->json($di->consultar_por_campos([["id","=",$id]],"AND",[]));
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
        $di=new DetalleInventario();
        $datos=json_decode($request->get("datos"));
        $rr=$di->editar([
                "updated_at"=>$datos->hora_cliente,   
                ],[["id","=",$id]]);
        if($rr["respuesta"]==true){
            
            if($datos->datos->tipo_entrada_inventario=="caja"){
                
                //selecciono el producto
                        $pro=DB::table('detalle_inventarios')
                        ->join('productos','productos.id','=','detalle_inventarios.fk_id_producto')
                        ->where("detalle_inventarios.id","=",$id)
                        ->select("productos.id",
                                "detalle_inventarios.cantidad_existencias",
                                "detalle_inventarios.cantidad_existencias_blister",
                                "detalle_inventarios.cantidad_existencias_unidades",
                                "productos.tipo_venta_producto",
                                "productos.unidades_por_caja",
                                "productos.unidades_por_blister")        
                        ->get();
                
                   
                 if(count($pro)>0){
                     if($pro[0]->tipo_venta_producto=="Caja"){

                             

                             DB::table('detalle_inventarios')
                                ->where("detalle_inventarios.id","=",$id)
                                ->increment('cantidad_existencias_unidades',((int)$datos->datos->cantidad_existencias*(int)$pro[0]->unidades_por_blister)*$pro[0]->unidades_por_caja);

                            $quedan=DB::table("detalle_inventarios")
                                                             ->join("productos","productos.id","=","detalle_inventarios.fk_id_producto")
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
                                                                                    "productos.precio_venta",
                                                                                    "productos.precio_venta_blister",
                                                                                    "productos.precio_mayoreo",
                                                                                    "productos.tipo_venta_producto")
                                                                                                             
                                                                    ->where("detalle_inventarios.id","=",$id)
                                                                    ->get();     
                                                                    
                            DB::table("detalle_inventarios")
                                ->where( "id","=",$id)
                                ->update(["cantidad_existencias_blister"=>floor($quedan[0]->cantidad_existencias_unidades/$quedan[0]->unidades_por_blister),
                                          "cantidad_existencias"=>floor($quedan[0]->cantidad_existencias_unidades/$quedan[0]->unidades_por_blister)/$quedan[0]->unidades_por_caja]);
                                                                                          
                          
                            DB::table('detalle_inventarios')
                             ->where("id","=",$id)
                             ->update(["estado_producto_sede"=>"1","estado_inventario"=>"activo"]);                                                            

                            DB::table('productos')
                             ->where("id","=",$id)
                             ->update(["estado_producto"=>"1"]);                                                
                     }
                     else if($pro[0]->tipo_venta_producto=="PorUnidad"){
                        DB::table('productos')
                         ->where("id","=",$pro[0]->id)
                         ->update(["estado_producto"=>"1"]);   

                        DB::table('detalle_inventarios')
                         ->where("id","=",$id)
                         ->update(["estado_producto_sede"=>"1","estado_inventario"=>"activo"]);

                         DB::table('detalle_inventarios')
                             ->where("detalle_inventarios.id","=",$id)
                             ->increment('cantidad_existencias',(int)$datos->datos->cantidad_existencias);

                        DB::table('detalle_inventarios')
                            ->where("detalle_inventarios.id","=",$id)
                            ->increment('cantidad_existencias_blister',(int)$datos->datos->cantidad_existencias); 
                        
                        DB::table('detalle_inventarios')
                            ->where("detalle_inventarios.id","=",$id)
                            ->increment('cantidad_existencias_unidades',(int)$datos->datos->cantidad_existencias); 
                
                     }
                     else if($pro[0]->tipo_venta_producto=="CajaBlister"){
                            DB::table('detalle_inventarios')
                                ->where("detalle_inventarios.id","=",$id)
                                ->increment('cantidad_existencias_unidades',((int)$datos->datos->cantidad_existencias*(int)$pro[0]->unidades_por_blister)*$pro[0]->unidades_por_caja);
                              $quedan=DB::table("detalle_inventarios")
                                                             ->join("productos","productos.id","=","detalle_inventarios.fk_id_producto")
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
                                                                                    "productos.precio_venta",
                                                                                    "productos.precio_venta_blister",
                                                                                    "productos.precio_mayoreo",
                                                                                    "productos.tipo_venta_producto")
                                                                                                             
                                                                    ->where("detalle_inventarios.id","=",$id)
                                                                    ->get();     
                                                                    
                             DB::table("detalle_inventarios")
                                ->where( "id","=",$id)
                                ->update([
                                            "cantidad_existencias_blister"=>floor($quedan[0]->cantidad_existencias_unidades/$quedan[0]->unidades_por_blister),
                                           "cantidad_existencias"=>floor(
                                                                ($quedan[0]->cantidad_existencias_unidades/$quedan[0]->unidades_por_blister)
                                                            /$quedan[0]->unidades_por_caja)
                                        ]);
                                                                                          
                          
                            DB::table('detalle_inventarios')
                             ->where("id","=",$id)
                             ->update(["estado_producto_sede"=>"1","estado_inventario"=>"activo"]);

                            DB::table('productos')
                             ->where("id","=",$id)
                             ->update(["estado_producto"=>"1"]);                         
                     }
                     
                
                     
                    $mo=new MovimientosInventario();
                    $mo->insertar([
                        "fk_id_det_inventario"=>$id,
                        "habia"=>$pro[0]->cantidad_existencias,
                        "fk_id_usuario"=>$datos->datos->fk_id_usuario,
                        "tipo"=>$datos->datos->tipo_entrada,
                        "descripcion"=>$datos->datos->tipo_entrada_inventario,
                        "cantidad"=>(int)$datos->datos->cantidad_existencias,
                        "quedan"=>(int)$pro[0]->cantidad_existencias+(int)$datos->datos->cantidad_existencias,
                         "created_at"=>$datos->hora_cliente,
                        "updated_at"=>$datos->hora_cliente,   
                        "observaciones"=>"REGISTRO GENERADO AGREGAR ENTRADA INVENTARIO"

                        ]);
                    return response()->json($rr);   
                 }
                 else{
                    
                     return response()->json(["respuesta"=>false,"mensaje"=>"No se pudo registrar el inventario parece que este producto no existe"]);   
                 }   
                
            }
            else if($datos->datos->tipo_entrada_inventario=="unidad"){
                
                
                
                $pro=DB::table('detalle_inventarios')
                        ->join('productos',"productos.id","=","detalle_inventarios.fk_id_producto")
                        ->where("detalle_inventarios.id","=",$id)
                        ->select("productos.id",
                                 "productos.tipo_venta_producto",
                                 "detalle_inventarios.cantidad_existencias_unidades",
                                 "detalle_inventarios.cantidad_existencias",
                                "productos.unidades_por_caja",
                                "productos.unidades_por_blister")
                        ->get();
                
                if($pro[0]->tipo_venta_producto=="Caja"){
                    DB::table('detalle_inventarios')                
                    ->where("id","=",$id)
                    ->increment('cantidad_existencias_unidades',$datos->datos->cantidad_existencias);
                         $quedan=DB::table("detalle_inventarios")
                                                             ->join("productos","productos.id","=","detalle_inventarios.fk_id_producto")
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
                                                                                    "productos.precio_venta",
                                                                                    "productos.precio_venta_blister",
                                                                                    "productos.precio_mayoreo",
                                                                                    "productos.tipo_venta_producto")
                                                                                                             
                                                                    ->where("detalle_inventarios.id","=",$id)
                                                                    ->get();     
                                                                    
                             DB::table("detalle_inventarios")
                                ->where( "id","=",$id)
                                ->update(["cantidad_existencias_blister"=>floor($quedan[0]->cantidad_existencias_unidades/$quedan[0]->unidades_por_blister),
                                          "cantidad_existencias"=>floor($quedan[0]->cantidad_existencias_unidades/$quedan[0]->unidades_por_blister)/$quedan[0]->unidades_por_caja]);
                            
                             $mo=new MovimientosInventario();
                            $mo->insertar([
                                "fk_id_det_inventario"=>$id,
                                "habia"=>$pro[0]->cantidad_existencias_unidades,
                                "fk_id_usuario"=>$datos->datos->fk_id_usuario,
                                "tipo"=>$datos->datos->tipo_entrada,
                                "descripcion"=>$datos->datos->tipo_entrada_inventario,
                                "cantidad"=>(int)$datos->datos->cantidad_existencias,
                                "quedan"=>(int)$quedan[0]->cantidad_existencias_unidades,
                                 "created_at"=>$datos->hora_cliente,
                                "updated_at"=>$datos->hora_cliente,   
                                "observaciones"=>"REGISTRO GENERADO AGREGAR ENTRADA INVENTARIO"

                                ]);
                            
                            DB::table('productos')
                         ->where("id","=",$pro[0]->id)
                         ->update(["estado_producto"=>"1"]);   

                                       
                            DB::table('detalle_inventarios')
                                ->where("id","=",$id)
                                ->update(["estado_producto_sede"=>"1","estado_inventario"=>"activo"]);
                            return response()->json($rr);        
                           
                  
                            
                         
                 
                 
                }
                if($pro[0]->tipo_venta_producto=="CajaBlister"){
                    
                     DB::table('detalle_inventarios')                
                    ->where("id","=",$id)
                    ->increment('cantidad_existencias_unidades',(int)$datos->datos->cantidad_existencias);

                    
                     $quedan=DB::table("detalle_inventarios")
                                                             ->join("productos","productos.id","=","detalle_inventarios.fk_id_producto")
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
                                                                                    "productos.precio_venta",
                                                                                    "productos.precio_venta_blister",
                                                                                    "productos.precio_mayoreo",
                                                                                    "productos.tipo_venta_producto")
                                                                                                             
                                                                    ->where("detalle_inventarios.id","=",$id)
                                                                    ->get();     
                                                                    
                             DB::table("detalle_inventarios")
                                ->where( "id","=",$id)
                                ->update(["cantidad_existencias_blister"=>floor($quedan[0]->cantidad_existencias_unidades/$quedan[0]->unidades_por_blister),
                                          "cantidad_existencias"=>floor($quedan[0]->cantidad_existencias_unidades/$quedan[0]->unidades_por_blister)/$quedan[0]->unidades_por_caja]);

                             $mo=new MovimientosInventario();
                            $mo->insertar([
                                "fk_id_det_inventario"=>$id,
                                "habia"=>$pro[0]->cantidad_existencias_unidades,
                                "fk_id_usuario"=>$datos->datos->fk_id_usuario,
                                "tipo"=>$datos->datos->tipo_entrada,
                                "descripcion"=>$datos->datos->tipo_entrada_inventario,
                                "cantidad"=>(int)$datos->datos->cantidad_existencias,
                                "quedan"=>(int)$quedan[0]->cantidad_existencias_unidades,
                                 "created_at"=>$datos->hora_cliente,
                                "updated_at"=>$datos->hora_cliente,   
                                "observaciones"=>"REGISTRO GENERADO AGREGAR ENTRADA INVENTARIO"

                                ]);

                            DB::table('productos')
                         ->where("id","=",$pro[0]->id)
                         ->update(["estado_producto"=>"1"]);   


                            DB::table('detalle_inventarios')
                                ->where("id","=",$id)
                                ->update(["estado_producto_sede"=>"1","estado_inventario"=>"activo"]); 
                            return response()->json($rr);        
                    
                }
                else if($pro[0]->tipo_venta_producto=="PorUnidad"){
                    DB::table('detalle_inventarios')                
                    ->where("id","=",$id)
                    ->increment('cantidad_existencias',(int)$datos->datos->cantidad_existencias);
                    
                    DB::table('detalle_inventarios')                
                    ->where("id","=",$id)
                    ->increment('cantidad_existencias_blister',(int)$datos->datos->cantidad_existencias);
                    
                    DB::table('detalle_inventarios')                
                    ->where("id","=",$id)
                    ->increment('cantidad_existencias_unidades',(int)$datos->datos->cantidad_existencias);
                    
                    
                    

                                $pro2=DB::table('detalle_inventarios')
                                   ->join('productos',"productos.id","=","detalle_inventarios.fk_id_producto")
                                   ->where("detalle_inventarios.id","=",$id)
                                   ->select("productos.id",
                                            "detalle_inventarios.cantidad_existencias_unidades",
                                            "detalle_inventarios.cantidad_existencias")
                                   ->get();

                                   DB::table('productos')
                                     ->where("id","=",$pro[0]->id)
                                     ->update(["estado_producto"=>"1"]);   

                                  DB::table('detalle_inventarios')
                                     ->where("id","=",$id)
                                     ->update(["estado_producto_sede"=>"1","estado_inventario"=>"activo"]);
                            $mo=new MovimientosInventario();
                            $mo->insertar([
                                "fk_id_det_inventario"=>$id,
                                "habia"=>$pro[0]->cantidad_existencias_unidades,
                                "fk_id_usuario"=>$datos->datos->fk_id_usuario,
                                "tipo"=>$datos->datos->tipo_entrada,
                                "descripcion"=>$datos->datos->tipo_entrada_inventario,
                                "cantidad"=>(int)$datos->datos->cantidad_existencias,
                                "quedan"=>(int)$pro2[0]->cantidad_existencias_unidades,
                                 "created_at"=>$datos->hora_cliente,
                                "updated_at"=>$datos->hora_cliente,   
                                "observaciones"=>"REGISTRO GENERADO AGREGAR ENTRADA INVENTARIO"

                                ]);
                            return response()->json($rr);   
                         
                    
                }
                
            }else if($datos->datos->tipo_entrada_inventario=="blister"){
                 $pro=DB::table('detalle_inventarios')
                        ->join('productos','productos.id','=','detalle_inventarios.fk_id_producto')
                        ->where("detalle_inventarios.id","=",$id)
                        ->select("productos.id",
                                "detalle_inventarios.cantidad_existencias",
                                "detalle_inventarios.cantidad_existencias_blister",
                                "detalle_inventarios.cantidad_existencias_unidades",
                                "productos.tipo_venta_producto",
                                "productos.unidades_por_caja",
                                "productos.unidades_por_blister")        
                        ->get();
                
                 DB::table('detalle_inventarios')                
                    ->where("id","=",$id)
                    ->increment('cantidad_existencias_unidades',(int)$datos->datos->cantidad_existencias*$pro[0]->unidades_por_blister);
                    
                    
                             $quedan=DB::table("detalle_inventarios")
                                                             ->join("productos","productos.id","=","detalle_inventarios.fk_id_producto")
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
                                                                                    "productos.precio_venta",
                                                                                    "productos.precio_venta_blister",
                                                                                    "productos.precio_mayoreo",
                                                                                    "productos.tipo_venta_producto")
                                                                                                             
                                                                    ->where("detalle_inventarios.id","=",$id)
                                                                    ->get();     
                                                                    
                             DB::table("detalle_inventarios")
                                ->where( "id","=",$id)
                                ->update(["cantidad_existencias_blister"=>floor($quedan[0]->cantidad_existencias_unidades/$quedan[0]->unidades_por_blister),
                                          "cantidad_existencias"=>floor($quedan[0]->cantidad_existencias_unidades/$quedan[0]->unidades_por_blister)/$quedan[0]->unidades_por_caja]);


                             $mo=new MovimientosInventario();
                            $mo->insertar([
                                "fk_id_det_inventario"=>$id,
                                "habia"=>$pro[0]->cantidad_existencias_unidades,
                                "fk_id_usuario"=>$datos->datos->fk_id_usuario,
                                "tipo"=>$datos->datos->tipo_entrada,
                                "descripcion"=>$datos->datos->tipo_entrada_inventario,
                                "cantidad"=>(int)$datos->datos->cantidad_existencias,
                                "quedan"=>(int)$quedan[0]->cantidad_existencias_unidades,
                                 "created_at"=>$datos->hora_cliente,
                                "updated_at"=>$datos->hora_cliente,   
                                "observaciones"=>"REGISTRO GENERADO AGREGAR ENTRADA INVENTARIO"

                                ]);

                            DB::table('productos')
                                 ->where("id","=",$pro[0]->id)
                                 ->update(["estado_producto"=>"1"]);   

                            DB::table('detalle_inventarios')
                                ->where("id","=",$id)
                                ->update(["estado_producto_sede"=>"1","estado_inventario"=>"activo"]);
                            return response()->json($rr);        
            
            }
            
        }else{
            return response()->json(["respuesta"=>false,"mensaje"=>"Ha ocurrido un error ala ctualizar cantidad de inventario"]);
        }
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
        $di=new DetalleInventario();
        return response()-json($di->eliminar(["id","=",$id]));
    }

    public function agregar_promocion(Request $request,$id_pro,$id_sed){
        if($id_sed==0){
            //aplicar a todos los productos 
            $datos=json_decode($request->get("datos"));
                $rr=DB::table('detalle_inventarios')
                    ->where("fk_id_producto","=",$id_pro)
                    ->get();
                 if(count($rr)>0){
                    DB::table('detalle_inventarios')
                    ->where("fk_id_producto","=",$id_pro)
                    ->update([
                           "promocion"=>"1",
                           "nombre_promocion"=>$datos->datos->nombre_promocion,
                           "promo_desde"=>$datos->datos->promo_desde,
                           "promo_hasta"=>$datos->datos->promo_hasta,
                           "precio_promo_venta"=>$datos->datos->precio_promo_venta, 
                           "tipo_venta_promo"=>$datos->datos->tipo_promo

                        ]);   
                    return response()->json(["mensaje"=>"promocion creada para todas las sedes","respuesta"=>true]);
                 }else{
                    return response()->json(["mensaje"=>"promocion NO se a podido crear por que este producto no se encuantra registrado en ninguna sede","respuesta"=>false]);
                 }   
                 

        }else{
            //Aplicar promocion a ese prodcuto en esa sede
            $datos=json_decode($request->get("datos"));
                $rr=DB::table('detalle_inventarios')
                    ->where([["fk_id_producto","=",$id_pro],
                        ["fk_id_sede","=",$id_sed]])
                    ->get();

                    //var_dump($rr);
                 if(count($rr)>0){
                        DB::table('detalle_inventarios')
                    ->where([
                        ["fk_id_producto","=",$id_pro],
                        ["fk_id_sede","=",$id_sed]
                        ])
                    ->update([
                           "promocion"=>"1",
                           "nombre_promocion"=>$datos->datos->nombre_promocion,
                           "promo_desde"=>$datos->datos->promo_desde,
                           "promo_hasta"=>$datos->datos->promo_hasta,
                           "precio_promo_venta"=>$datos->datos->precio_promo_venta,
                           "tipo_venta_promo"=>$datos->datos->tipo_promo 

                        ]);   
                    return response()->json(["mensaje"=>"promocion creada para  la sede","respuesta"=>true]);
                 }else{
                    return response()->json(["mensaje"=>"promocion NO se a podido crear por que este producto no se encuentra registrado en esta sede","respuesta"=>false]);
                 }  
        }
    }

    public function consultar_promociones(){
        $rr=DB::table('detalle_inventarios')
            ->join('productos',"productos.id","=","detalle_inventarios.fk_id_producto")
            ->where("promocion","=","1")
            ->get();
        if(count($rr)>0){
            return response()->json(["mensaje"=>"Promociones encontradas","respuesta"=>true,"datos"=>$rr]);
        }else{
            return response()->json(["mensaje"=>"NO hay promociones actuvas","respuesta"=>false]);    
        }    
        
    }
    
    public function detalle_inventarios_ajuste(Request $request,$id) {
        
        $datos= json_decode($request->get("datos"));
        
        
        if($datos->datos->cantidad_existencias_unidades!=""){
            $habia=DB::table('detalle_inventarios')
                   ->where("id","=",$id)
                    ->get();
            DB::table('detalle_inventarios')
                ->where("id","=",$id)
                ->update(["cantidad_existencias_unidades"=>$datos->datos->cantidad_existencias_unidades,
                        "updated_at"=>$datos->hora_cliente]);
            $quedan=DB::table('detalle_inventarios')
                     ->where("id","=",$id)
                    ->get();
            DB::table('movimientos_inventario')
                    ->insert([
                         "fk_id_det_inventario"=>$id,
                                "habia"=>$habia[0]->cantidad_existencias_unidades,
                                "fk_id_usuario"=>$datos->datos->fk_id_usuario,
                                "tipo"=>"AJUSTE",
                                "descripcion"=>"unidad",
                                "cantidad"=>(int)$datos->datos->cantidad_existencias,
                                "quedan"=>(int)$quedan[0]->cantidad_existencias_unidades,
                                "created_at"=>$datos->hora_cliente,
                                "updated_at"=>$datos->hora_cliente,   
                                "observaciones"=>$datos->datos->observaciones,
                    ]);
        
        }
        
        if($datos->datos->cantidad_existencias!=""){
             $habia=DB::table('detalle_inventarios')
                   ->where("id","=",$id)
                    ->get();
            DB::table('detalle_inventarios')
                ->where("id","=",$id)
                ->update(["cantidad_existencias"=>$datos->datos->cantidad_existencias,
                        "updated_at"=>$datos->hora_cliente]);
            $quedan=DB::table('detalle_inventarios')
                     ->where("id","=",$id)
                    ->get();
            DB::table('movimientos_inventario')
                    ->insert([
                        "fk_id_det_inventario"=>$id,
                                "habia"=>$habia[0]->cantidad_existencias,
                                "fk_id_usuario"=>$datos->datos->fk_id_usuario,
                                "tipo"=>"AJUSTE",
                                "descripcion"=>"caja",
                                "cantidad"=>(int)$datos->datos->cantidad_existencias,
                                "quedan"=>(int)$quedan[0]->cantidad_existencias,
                                "created_at"=>$datos->hora_cliente,
                                "updated_at"=>$datos->hora_cliente,   
                                "observaciones"=>$datos->datos->observaciones,
                    ]);
        }
        
        if($datos->datos->cantidad_existencias_blister!=""){
             $habia=DB::table('detalle_inventarios')
                   ->where("id","=",$id)
                    ->get();
            DB::table('detalle_inventarios')
                ->where("id","=",$id)
                ->update(["cantidad_existencias_blister"=>$datos->datos->cantidad_existencias_blister,
                        "updated_at"=>$datos->hora_cliente]);
            $quedan=DB::table('detalle_inventarios')
                     ->where("id","=",$id)
                    ->get();
            DB::table('movimientos_inventario')
                    ->insert([
                        "fk_id_det_inventario"=>$id,
                                "habia"=>$habia[0]->cantidad_existencias,
                                "fk_id_usuario"=>$datos->datos->fk_id_usuario,
                                "tipo"=>"AJUSTE",
                                "descripcion"=>"blister",
                                "cantidad"=>(int)$datos->datos->cantidad_existencias,
                                "quedan"=>(int)$quedan[0]->cantidad_existencias,
                                "created_at"=>$datos->hora_cliente,
                                "updated_at"=>$datos->hora_cliente,   
                                "observaciones"=>$datos->datos->observaciones,
                    ]);
        }
        
        return response()->json(["mensaje"=>"Ajuste realizado","respuesta"=>true]);
    }
}
