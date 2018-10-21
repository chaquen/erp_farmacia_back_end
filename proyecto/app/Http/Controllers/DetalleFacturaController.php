<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use App\DetalleFactura;

use DB;

class DetalleFacturaController extends Controller
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

        $datos=json_decode($request->get("datos"));
        //aqui debo validar las unidades ya solictadas y en caso de que no existan suficientes no registrar
    
        $sel=DB::table("detalle_facturas")
            ->join("facturas","detalle_facturas.fk_id_factura","=","facturas.id")

            ->where([   
                        ["detalle_facturas.fk_id_producto","=",$datos->datos->producto->id_producto_inventario],
                        ["facturas.estado_factura","=","pendiente"],
                        ["facturas.fk_id_sede","=",$datos->datos->sede]
                        
                    ])
            ->get();
        //var_dump($sel);    

         $hay=DB::table("detalle_inventarios")
            ->join("productos","productos.id","=","detalle_inventarios.fk_id_producto")     
            ->where("detalle_inventarios.id","=",$datos->datos->producto->id_producto_inventario)
            ->select("detalle_inventarios.cantidad_existencias_unidades","productos.inventario","productos.unidades_por_blister","productos.unidades_por_caja")     
            ->get();  
            //var_dump($hay);

         //var_dump($datos->datos->producto);    
        if(count($sel)>0){
            
            //var_dump($datos->datos->id_ticket);
            $solictadas=0;
            //aqui sumo los seleccionados con anterioridad
            foreach ($sel as $key => $value) {
                
                switch ($value->tipo_venta) {
                    case 'unidad':
                        # code...
                        $solictadas+=(int)$value->cantidad_producto;     
                        break;
                    case 'blister':
                        $solictadas+=(int)$value->cantidad_producto*$hay[0]->unidades_por_blister;    
                        
                        break;
                    case 'caja':
                        # code...
                        $solictadas+=(int)$value->cantidad_producto*$hay[0]->unidades_por_caja;    
                        break;
                }
                
            }
            //aqui sumo los solicitados en la peticion
            switch ($value->tipo_venta) {
                    case 'unidad':
                        # code...
                        $solictadas+=(int)$datos->datos->producto->cantidad_producto;     
                        break;
                    case 'blister':
                        $solictadas+=(int)$datos->datos->producto->cantidad_producto*$hay[0]->unidades_por_blister;    
                        
                        break;
                    case 'caja':
                        # code...
                        $solictadas+=(int)$datos->datos->producto->cantidad_producto*$hay[0]->unidades_por_caja;    
                        break;
            }

            //var_dump($solictadas);
            //var_dump($hay[0]->cantidad_existencias_unidades);
            if($hay[0]->inventario == 1 && $hay[0]->cantidad_existencias_unidades >= (int)$solictadas){
                $id=DB::table("detalle_facturas")
                    ->insertGetId(array(
                                "fk_id_factura"=>$datos->datos->id_ticket,
                                "fk_id_producto"=>$datos->datos->producto->id_producto_inventario,
                                "cantidad_producto"=>$datos->datos->producto->cantidad_producto,
                                "descuento"=>0,
                                "tipo_venta"=>$datos->datos->producto->tipo_venta,
                                "valor_item"=>$datos->datos->producto->valor_item,
                                "created_at"=>$datos->hora_cliente,
                                "updated_at"=>$datos->hora_cliente, 


                            ));    
                     return response()->json(["mensaje"=>"detalle registrado","respuesta"=>true,"id"=>$id]);    
            }else{
                if($hay[0]->inventario==1){
                    /*DB::table("detalle_inventarios")
                    ->where("id","=",$datos->datos->producto->id_producto_inventario)
                    ->update(["estado_inventario"=>"agotado"]);*/
                    return response()->json(["mensaje"=>"No hay unidades suficientes para esta venta","respuesta"=>false]);    
                }else{
                    $id=DB::table("detalle_facturas")
                    ->insertGetId(array(
                                "fk_id_factura"=>$datos->datos->id_ticket,
                                "fk_id_producto"=>$datos->datos->producto->id_producto_inventario,
                                "cantidad_producto"=>$datos->datos->producto->cantidad_producto,
                                "descuento"=>0,
                                "tipo_venta"=>$datos->datos->producto->tipo_venta,
                                "valor_item"=>$datos->datos->producto->valor_item,
                                "created_at"=>$datos->hora_cliente,
                                "updated_at"=>$datos->hora_cliente, 


                            ));    
                    return response()->json(["mensaje"=>"detalle registrado","respuesta"=>true,"id"=>$id]);    
                }
                

                
            }
            

           
        }else {
           $id=DB::table("detalle_facturas")
                    ->insertGetId(array(
                                "fk_id_factura"=>$datos->datos->id_ticket,
                                "fk_id_producto"=>$datos->datos->producto->id_producto_inventario,
                                "cantidad_producto"=>$datos->datos->producto->cantidad_producto,
                                "descuento"=>0,
                                "tipo_venta"=>$datos->datos->producto->tipo_venta,
                                "valor_item"=>$datos->datos->producto->valor_item,
                                "created_at"=>$datos->hora_cliente,
                                "updated_at"=>$datos->hora_cliente, 


                            ));    


            return response()->json(["mensaje"=>"detalle registrado","respuesta"=>true,"id"=>$id]);    
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
        $df=new DetalleFactura();
        return response()->json($df->consultar_por_campo(array(["id","=",$id]),"AND",array()));
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
       


        $datos=json_decode($request->get("datos"));
       
    
        $sel=DB::table("detalle_facturas")
            ->where([   
                        ["fk_id_producto","=",$datos->datos->producto->id_producto_inventario],
                        ["fk_id_factura","=",$datos->datos->id_factura]
                    ])
            ->get();

         
        
         $hay=DB::table("detalle_inventarios")
            ->join("productos","productos.id","=","detalle_inventarios.fk_id_producto")
            ->where("detalle_inventarios.id","=",$datos->datos->producto->id_producto_inventario)
            ->get();  
         //var_dump($datos->datos->producto);    
        //var_dump($sel);
        if(count($sel)>0){
            
            
           
            $solictadas=0;
            $tp="";
            //sumo la unidades solicitadas del producto
            foreach ($sel as $key => $value) {

                if($value->id==$datos->datos->producto->id_factura){
                    
                }else{
                    switch ($value->tipo_venta) {
                        case 'unidad':
                            # code...
                            $solictadas+=(int)$value->cantidad_producto;     
                            break;
                        case 'blister':
                            $solictadas+=(int)$value->cantidad_producto*$hay[0]->unidades_por_blister;    
                            
                            break;
                        case 'caja':
                            # code...
                            $solictadas+=(int)$value->cantidad_producto*$hay[0]->unidades_por_caja;    
                            break;
                    }    
                }
                
                
            }

            //var_dump($solictadas);

            if($value->id==$datos->datos->producto->id_factura){
                    $tp=$datos->datos->producto->tipo_venta;
            }else{
                    $tp=$value->tipo_venta;
            }    
            $hay_uni=0;
            switch ($datos->datos->producto->tipo_venta) {
                    case 'unidad':
                        # code...
                        $hay_uni=(int)$hay[0]->cantidad_existencias_unidades;
                        $solictadas+=(int)$datos->datos->producto->cantidad_producto;     
                        break;
                    case 'blister':
                        $solictadas+=(int)$datos->datos->producto->cantidad_producto*$hay[0]->unidades_por_blister;    
                        $hay_uni=(int)$hay[0]->cantidad_existencias_unidades;
                        break;
                    case 'caja':
                        # code...
                        $solictadas+=(int)$datos->datos->producto->cantidad_producto*$hay[0]->unidades_por_caja;
                        $hay_uni=(int)$hay[0]->cantidad_existencias_unidades;    
                        break;
                }

            var_dump($solictadas);
            var_dump($hay_uni);
            if($hay_uni >= (int)$solictadas){

                    //var_dump((int)$hay[0]->cantidad_existencias_unidades);
                    //var_dump((int)$solictadas);
                    if((int)$hay[0]->cantidad_existencias_unidades == (int)$solictadas){
                         DB::table("detalle_inventarios")
                            ->where("id","=",$datos->datos->producto->id_producto_inventario)
                            ->update(["estado_inventario"=>"agotado"]);
                    }else{
                         DB::table("detalle_inventarios")
                            ->where("id","=",$datos->datos->producto->id_producto_inventario)
                            ->update(["estado_inventario"=>"activo"]);
                    }

                   $df=new DetalleFactura();
                    
                    return response()->json($df->editar(
                            array(
                                "fk_id_producto"=>$datos->datos->producto->id_producto_inventario,
                                "cantidad_producto"=>$datos->datos->producto->cantidad_producto,
                                "tipo_venta"=>$datos->datos->producto->tipo_venta,
                                "descuento"=>0,
                                "valor_item"=>$datos->datos->producto->valor_item,
                                "updated_at"=>$datos->hora_cliente, 
                                ),
                            [["id","=",$datos->datos->producto->id_factura]]

                        ));  

            }else{
                
                return response()->json(["mensaje"=>"No hay unidades suficientes para la venta","respuesta"=>false]);    
            }
            

           
        }else {
            return response()->json(["mensaje"=>"error al actualizar detalle","respuesta"=>false]);    
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
        //1-CONSULTAR DETALLE FACTURA
        $dt=DB::table("detalle_facturas")
                ->join("detalle_inventarios","detalle_facturas.fk_id_producto","=","detalle_inventarios.id")
                ->join("facturas","facturas.id","=","detalle_facturas.fk_id_factura")
                ->join("productos","productos.id","=","detalle_inventarios.fk_id_producto")
                ->select(
                         "detalle_inventarios.id AS id",
                         "productos.unidades_por_caja",
                         "productos.unidades_por_blister",
                         "detalle_facturas.cantidad_producto",
                         "detalle_facturas.fk_id_factura",
                         "detalle_facturas.fk_id_producto",
                         "detalle_facturas.valor_item",
                         "detalle_facturas.tipo_venta",
                         "detalle_facturas.id as id_factura",
                         "facturas.estado_factura"
                            )
                ->where("detalle_facturas.id","=",$id)

                 ->get();   
         //var_dump($dt[0]);        
        //2 RECALCULAR VALOR DE FACTURA  Y VALOR A DESCONTAR     
           
         $valor_a_descontar=(float)$dt[0]->cantidad_producto*(float)$dt[0]->valor_item;        
           
         

        //QUITAR EL DETALLE
        DB::table("detalle_facturas")
                ->where("id","=",$id)
                ->delete();


        $dt_ff=DB::table('detalle_facturas')
                ->where("fk_id_factura","=",$dt[0]->fk_id_factura)
                ->get();        
       
        if($dt[0]->estado_factura=="paga"){
            
            if(count($dt_ff)==0){
                //eliminar la factura
                $dt_con_fact=DB::table("detalle_entrada_contable_factura")
                    ->where("fk_id_factura_venta","=",$dt[0]->fk_id_factura)->get();
              
                 //ELIMINAR DETALLE   
                   DB::table("detalle_entrada_contables")
                        ->where("fk_id_entrada_contable","=",$dt_con_fact[0]->fk_id_entrada_contable)
                        ->delete();  

                DB::table("facturas")->where("id","=",$dt[0]->fk_id_factura)->delete();
                //BUSCO ID DEL DETALLE CONTABLE

            }else{
                
                $dt_con_fact=DB::table("detalle_entrada_contable_factura")
                    ->where("fk_id_factura_venta","=",$dt[0]->fk_id_factura)
                    ->get();

                  //EDITAR DETALLE CONTABLE       
                DB::table("detalle_entrada_contables")
                        ->where("fk_id_entrada_contable","=",$dt_con_fact[0]->fk_id_entrada_contable)
                        ->decrement("valor_entrada",$valor_a_descontar);        
                    
            }

            //ACTUALIZAR LA CANTIDAD DE EXISTENCIAS EN LA SEDE
        switch ($dt[0]->tipo_venta) {
                    case 'unidad':
         
                        DB::table("detalle_inventarios")
                            ->where("id","=",$dt[0]->fk_id_producto)
                            ->increment("cantidad_existencias_unidades",$dt[0]->cantidad_producto);   
                        //CALCULAR CAJAS Y BLISTER    
                        //1 consultar el producto nuevamente y 

                            $dt2=DB::table("detalle_inventarios")
                                       ->join("productos","productos.id","=","detalle_inventarios.fk_id_producto")
                                       ->where("detalle_inventarios.id","=",$dt[0]->id)
                                       ->select(
                                                "detalle_inventarios.cantidad_existencias_unidades",
                                                "detalle_inventarios.cantidad_existencias_blister"
                                                
                                            )
                                       ->get(); 
                             if($dt2[0]->cantidad_existencias_unidades>0){

                                    DB::table("detalle_inventarios")
                                                ->where( "id","=",$dt[0]->id)
                                                ->update([
                                                           "estado_producto_sede"=>"1"
                                                            

                                                         ]);                                
                             }          

                             DB::table("detalle_inventarios")
                                                ->where( "id","=",$dt[0]->id)
                                                ->update([
                                                           "cantidad_existencias_blister"=>floor($dt2[0]->cantidad_existencias_unidades/$dt[0]->unidades_por_blister),
                                                            "cantidad_existencias"=>floor(floor($dt2[0]->cantidad_existencias_unidades/$dt[0]->unidades_por_blister)/$dt[0]->unidades_por_caja)

                                                         ]);
                        break;
                    case 'caja':
                         DB::table("detalle_inventarios")
                            ->where("id","=",$dt[0]->fk_id_producto)
                            ->increment("cantidad_existencias_unidades",floor($dt[0]->cantidad_producto*$dt[0]->unidades_por_blister)*$dt[0]->unidades_por_caja);   


                             $dt2=DB::table("detalle_inventarios")
                                       ->join("productos","productos.id","=","detalle_inventarios.fk_id_producto")
                                       ->where("detalle_inventarios.id","=",$dt[0]->id)
                                       ->select(
                                                "detalle_inventarios.cantidad_existencias_unidades",
                                                "detalle_inventarios.cantidad_existencias_blister"
                                                
                                            )
                                       ->get(); 

                              if($dt2[0]->cantidad_existencias_unidades>0){
                                    DB::table("detalle_inventarios")
                                                ->where( "id","=",$dt[0]->id)
                                                ->update([
                                                           "estado_producto_sede"=>"1"
                                                            

                                                         ]);                                
                             }         
                             DB::table("detalle_inventarios")
                                                ->where( "id","=",$dt[0]->id)
                                                ->update([
                                                           "cantidad_existencias_blister"=>floor($dt2[0]->cantidad_existencias_unidades/$dt[0]->unidades_por_blister),
                                                            "cantidad_existencias"=>floor(floor($dt2[0]->cantidad_existencias_unidades/$dt[0]->unidades_por_blister)/$dt[0]->unidades_por_caja)

                                                         ]); 
                            //CALCULAR CAJAS Y BLISTER
                         break;
                    case 'blister':
                        DB::table("detalle_inventarios")
                            ->where("id","=",$dt[0]->fk_id_producto)
                            ->increment("cantidad_existencias_unidades",$dt[0]->cantidad_producto*$dt[0]->unidades_por_blister);    
                            //CALCULAR CAJAS Y BLISTER
                             $dt2=DB::table("detalle_inventarios")
                                       ->join("productos","productos.id","=","detalle_inventarios.fk_id_producto")
                                       ->where("detalle_inventarios.id","=",$dt[0]->id)
                                       ->select(
                                                "detalle_inventarios.cantidad_existencias_unidades",
                                                "detalle_inventarios.cantidad_existencias_blister"
                                                
                                            )
                                       ->get(); 

                              if($dt2[0]->cantidad_existencias_unidades>0){
                                    DB::table("detalle_inventarios")
                                                ->where( "id","=",$dt[0]->id)
                                                ->update([
                                                           "estado_producto_sede"=>"1"
                                                            

                                                         ]);                                
                             }         
                             DB::table("detalle_inventarios")
                                                ->where( "id","=",$dt[0]->id)
                                                ->update([
                                                           "cantidad_existencias_blister"=>floor($dt2[0]->cantidad_existencias_unidades/$dt[0]->unidades_por_blister),
                                                            "cantidad_existencias"=>floor(floor($dt2[0]->cantidad_existencias_unidades/$dt[0]->unidades_por_blister)/$dt[0]->unidades_por_caja)

                                                         ]);
                        # code...
                        break;
                }      
            
        }

        DB::table("detalle_inventarios")
                    ->where("id","=",$dt[0]->fk_id_producto)
                    ->update(["estado_inventario"=>"activo"]);
        
        
                
         return response()->json(["mensaje"=>"Detalle devuelto","respuesta"=>true]);       
    }
}

