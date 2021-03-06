<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use App\Producto;

use App\DetalleInventario;

use App\MovimientosInventario;

use App\Sede;

use DB;

class ProductosController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $prod=new Producto();
        return response()->json($prod->consultar_todos());
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
        $datos=json_decode($request->get('datos'));
        
        if($datos->datos->fk_id_departamento==0){
            return response()->json(["mensaje"=>"Por favor selecciona una categoria","respuesta"=>false]);
        }
        if($datos->datos->fk_id_proveedor==0){
            return response()->json(["mensaje"=>"por favor selecciona un proveedor","respuesta"=>false]);
        }

        $prod=new Producto();
        $dt=new DetalleInventario();
        $mi=new MovimientosInventario();
       
        //consulto que no exista producto
        $pro=$prod->consultar_por_campo(array(array("codigo_producto",'=',$datos->datos->codigo_producto)),"AND",array());



        if($pro["respuesta"]==false){
            //insero si no existe
            $arr=$prod->insertar(array(
                "codigo_producto"=>$datos->datos->codigo_producto,
                "codigo_distribuidor"=>$datos->datos->codigo_distribuidor,
                "nombre_producto"=>$datos->datos->nombre_producto,
                "nombre_producto_venta"=>$datos->datos->nombre_producto,
                "descripcion_producto"=>$datos->datos->descripcion_producto,
                "laboratorio"=>$datos->datos->laboratorio,
                "tipo_presentacion"=>$datos->datos->tipo_presentacion_producto,
                "tipo_venta_producto"=>$datos->datos->tipo_venta_producto,
                "precio_compra"=>$datos->datos->precio_compra,
                "precio_compra_blister"=>$datos->datos->precio_compra_blister,
                "precio_compra_unidad"=>$datos->datos->precio_compra_unidad,
                "precio_compra_impuesto"=>$datos->datos->precio_compra_impuesto,
                "precio_compra_blister_impuesto"=>$datos->datos->precio_compra_blister_impuesto,
                "precio_compra_unidad_impuesto"=>$datos->datos->precio_compra_unidad_impuesto,
                "precio_venta"=>$datos->datos->precio_venta,
                "precio_venta_blister"=>$datos->datos->precio_venta_blister,
                "precio_mayoreo"=>$datos->datos->precio_mayoreo,
                "unidades_por_caja"=>$datos->datos->unidades_por_caja,
                "unidades_por_blister"=>$datos->datos->unidades_por_blister,                
                "porcentaje_ganancia"=>$datos->datos->porcentaje_ganancia,
                "porcentaje_ganancia_blister"=>$datos->datos->porcentaje_ganancia_blister,
                "porcentaje_ganancia_unidad"=>$datos->datos->porcentaje_ganancia_unidad,
                "minimo_inventario"=>$datos->datos->minimo_inventario,
                "fk_id_departamento"=>$datos->datos->fk_id_departamento,
                "fk_id_proveedor"=>$datos->datos->fk_id_proveedor,
                "created_at"=>$datos->hora_cliente,
                "updated_at"=>$datos->hora_cliente, 
                "impuesto"=>$datos->datos->impuestos,
                "grupo"=>"",
                "sub_grupo"=>"",               

            ));
            
            
        }else{
          return response()->json(["mensaje"=>"Este producto ya esta registardo","respuesta"=>false]); 
        }
        


        if($datos->datos->fk_id_sede!=false){
            $id;
            if($pro["respuesta"]==false){
                $id=$arr["id"];
            }else{
                $id=$pro["datos"][0]->id;
            }
            if($datos->datos->tipo_venta_producto=="PorUnidad"){
               $unidades =$datos->datos->cantidad_existencias;
            }else{
                $unidades=$datos->datos->cantidad_existencias*$datos->datos->unidades_por_caja;
            }
            $rr=$dt->insertar([
                    "fk_id_producto"=>$id,
                    "fk_id_sede"=>$datos->datos->fk_id_sede,                   

                     "cantidad_existencias"=>$datos->datos->cantidad_existencias,
                     "cantidad_existencias_blister"=>$unidades,
                     "cantidad_existencias_unidades"=>$unidades,
                    "precio_venta_sede"=>$datos->datos->precio_venta,     
                    "precio_venta_blister_sede"=>$datos->datos->precio_venta_blister,                 
                    "precio_mayoreo_sede"=>$datos->datos->precio_mayoreo,
                    "porcentaje_ganancia_sede"=>$datos->datos->porcentaje_ganancia,
                    "porcentaje_ganancia_blister_sede"=>$datos->datos->porcentaje_ganancia_blister,
                    
                    "porcentaje_ganancia_sede_unidad"=>$datos->datos->porcentaje_ganancia_unidad,
                    "minimo_inventario_sede"=>$datos->datos->minimo_inventario,
                    "created_at"=>$datos->hora_cliente,
                    "updated_at"=>$datos->hora_cliente, 
                    "estado_producto_sede"=>"1",
                    "estado_inventario"=>"activo"
                    ]);
                  if($rr["respuesta"]==TRUE){
                      $mi->insertar([
                        "fk_id_det_inventario"=>$rr["id"],
                        "habia"=>0,
                          "fk_id_usuario"=>$datos->datos->fk_id_usuario,
                        "tipo"=>"ENTRADA",
                        "cantidad"=>$datos->datos->cantidad_existencias,
                        "quedan"=>$datos->datos->cantidad_existencias,
                         "created_at"=>$datos->hora_cliente,
                        "updated_at"=>$datos->hora_cliente, 
                        "observaciones"=>"Entrada inicial de inventario"
                        ]);
                      return response()->json(["respuesta"=>true,"mensaje"=>"Producto registrado"]);
                      
                  }else{
                    return response()->json(["respuesta"=>false,"mensaje"=>"No se a podido registrar el movimiento"]);
                  }
        }
        else{
            //aqui registrar en todas las sedes
            $se=new Sede();
            $sed=$se->consultar_todos();
            $dti=new DetalleInventario();
            $id_pro=0;
                if($pro["respuesta"]==false){
                    $id_pro=$arr["id"];
               }else{
                    $id_pro=$pro["datos"][0]->id;
               }
               
               if($datos->datos->tipo_venta_producto=="PorUnidad"){
                    $unidades =$datos->datos->cantidad_existencias;
                 }else{
                     $unidades=$datos->datos->cantidad_existencias*$datos->datos->unidades_por_caja;
                 }
            foreach ($sed["datos"] as $key => $value) {
               

                $rr=$dti->insertar([
                    "fk_id_producto"=>$id_pro,
                    "fk_id_sede"=>$value->id,                    
                     "cantidad_existencias"=>$datos->datos->cantidad_existencias,
                     "cantidad_existencias_blister"=>$unidades,
                     "cantidad_existencias_unidades"=>$unidades,
                    "precio_venta_sede"=>$datos->datos->precio_venta,     
                    "precio_venta_blister_sede"=>$datos->datos->precio_venta_blister,                 
                    "precio_mayoreo_sede"=>$datos->datos->precio_mayoreo,
                    "porcentaje_ganancia_sede"=>$datos->datos->porcentaje_ganancia,
                    "porcentaje_ganancia_blister_sede"=>$datos->datos->porcentaje_ganancia_blister,
                    
                    "porcentaje_ganancia_sede_unidad"=>$datos->datos->porcentaje_ganancia_unidad,
                    "minimo_inventario_sede"=>$datos->datos->minimo_inventario,
                    "created_at"=>$datos->hora_cliente,
                    "updated_at"=>$datos->hora_cliente, 
                    "estado_inventario"=>"activo"
                    ]);
                
                  if($rr["respuesta"]==true && $datos->datos->cantidad_existencias>0){
                    if($datos->datos->cantidad_existencias>0){
                            DB::table('detalle_inventarios')
                            ->where('id',"=",$rr["id"])
                            ->update(["estado_producto_sede"=>"1","estado_inventario"=>"activo"]);
                            
                    }
                        $ri=$mi->insertar([
                            "fk_id_det_inventario"=>$rr["id"],
                            "habia"=>0,
                            "tipo"=>"ENTRADA",
                            "fk_id_usuario"=>$datos->datos->fk_id_usuario,
                            "cantidad"=>$datos->datos->cantidad_existencias,
                            "quedan"=>$datos->datos->cantidad_existencias,
                             "created_at"=>$datos->hora_cliente,
                            "updated_at"=>$datos->hora_cliente, 
                            "observaciones"=>"Entrada inicial de inventario"
                            ]);

                        if($ri["respuesta"]==false){
                            return response()->json(["mensaje"=>"movimiento inventario producto NO registrado","respuesta"=>false]);      
                        }
                        //return response()->json(["mensaje"=>"producto registrado","respuesta"=>true]);
                  }else{
                    //return response()->json(["mensaje"=>"detalle inventario producto NO registrado","respuesta"=>false]);
                  }
            }

               return response()->json(["mensaje"=>"producto registrado","respuesta"=>true]);


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
        
        $prod=new Producto();
        return response()->json($prod->consultar_por_campo(array(array("codigo_producto",'LIKE',$id)),"OR",array(array("nombre_producto","LIKE","%".$id."%"))));
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
     

        $prod=new Producto();
        $datos=json_decode($request->get("datos"));
        
        //valido si sede fue seleccionada
        if($datos->datos->fk_id_sede=="0"){
            
            //ACTUALIZAR UIDAES POR CAJA
            switch ($datos->datos->tipo_venta_producto) {
                case 'PorUnidad':
                    # code...
                    $datos->datos->unidades_por_blister=1;
                    
                    $datos->datos->precio_compra_blister_impuesto=$datos->datos->precio_compra_impuesto;
                    $datos->datos->precio_compra_unidad_impuesto=$datos->datos->precio_compra_impuesto;

                    $datos->datos->precio_venta_blister=$datos->datos->precio_mayoreo;
                    

                    $datos->datos->porcentaje_ganancia_blister=$datos->datos->porcentaje_ganancia;
                    $datos->datos->porcentaje_ganancia_unidad=$datos->datos->porcentaje_ganancia;                    
                    
                    

                    break;
                case 'Caja':
                  $datos->datos->precio_venta_blister=$datos->datos->precio_mayoreo;
                
                    # code...
                    break;
                 case 'CajaBlister':
                        # code...
                        break;   
                
            }
            //var_dump($datos->datos->unidades_por_caja);
            //var_dump($id);
            $rr=$prod->editar(array(
                /*"codigo_producto"=>$datos->datos->codigo_producto,
                "codigo_distribuidor"=>$datos->datos->codigo_distribuidor,*/
                "nombre_producto"=>$datos->datos->nombre_producto,
                "nombre_producto_venta"=>$datos->datos->nombre_producto,
                "descripcion_producto"=>$datos->datos->descripcion_producto,
                "tipo_venta_producto"=>$datos->datos->tipo_venta_producto,
                "unidades_por_caja"=>$datos->datos->unidades_por_caja,
                "unidades_por_blister"=>$datos->datos->unidades_por_blister,
                "precio_compra"=>$datos->datos->precio_compra,
                "precio_compra_blister"=>$datos->datos->precio_compra_blister,
                "precio_compra_unidad"=>$datos->datos->precio_compra_unidad,
                "precio_compra_impuesto"=>$datos->datos->precio_compra_impuesto,
                "precio_compra_blister_impuesto"=>$datos->datos->precio_compra_blister_impuesto,
                "precio_compra_unidad_impuesto"=>$datos->datos->precio_compra_unidad_impuesto,
                "precio_venta"=>$datos->datos->precio_venta,
                "precio_venta_blister"=>$datos->datos->precio_venta_blister,
                "precio_mayoreo"=>$datos->datos->precio_mayoreo,
                "porcentaje_ganancia"=>$datos->datos->porcentaje_ganancia,
                "porcentaje_ganancia_blister"=>$datos->datos->porcentaje_ganancia_blister,
                "porcentaje_ganancia_unidad"=>$datos->datos->porcentaje_ganancia_unidad,
                "minimo_inventario"=>$datos->datos->minimo_inventario,
                "fk_id_departamento"=>$datos->datos->fk_id_departamento,
                "fk_id_proveedor"=>$datos->datos->fk_id_proveedor,
                "updated_at"=>$datos->hora_cliente, 
                "impuesto"=>$datos->datos->impuestos,   
                "inventario"=>$datos->datos->inventario,
                "laboratorio"=>$datos->datos->laboratorio,

            ),array(["id","=",$id]));

             
            $pr_dt=DB::table('detalle_inventarios')
                    ->where("fk_id_producto","=",$id)
                    ->get();
            //consultar cantidades y recalcularlas
            //para cada sede
            //$sede=DB::table("sedes")->get();        
            foreach ($pr_dt as $key => $value) {

                     $dt=new DetalleInventario();
             
                    $dt->editar([
                               
                                "precio_venta_sede"=>$datos->datos->precio_venta,
                                "precio_venta_blister_sede"=>$datos->datos->precio_venta_blister, 
                                "precio_mayoreo_sede"=>$datos->datos->precio_mayoreo,
                                "cantidad_existencias_unidades"=>$value->cantidad_existencias_unidades,
                                "cantidad_existencias_blister"=>floor($value->cantidad_existencias_unidades/$datos->datos->unidades_por_blister),
                                "cantidad_existencias"=>floor(($value->cantidad_existencias_unidades/$datos->datos->unidades_por_blister)/$datos->datos->unidades_por_caja),
                                "porcentaje_ganancia_sede"=>$datos->datos->porcentaje_ganancia,
                                "porcentaje_ganancia_blister_sede"=>$datos->datos->porcentaje_ganancia_blister,
                                "porcentaje_ganancia_sede_unidad"=>$datos->datos->porcentaje_ganancia_unidad,
                                "minimo_inventario_sede"=>$datos->datos->minimo_inventario,
                                "updated_at"=>$datos->hora_cliente, 
                                "estado_inventario"=>"activo"
                                        ],[["fk_id_producto","=",$id],["fk_id_sede","=",$value->fk_id_sede]]);
                }    
           
                    
            return response()->json($rr);
        }
        else{
              //var_dump($datos);
           //editar valores en una sede
            $resp=DB::table('detalle_inventarios')
                    ->join("productos","detalle_inventarios.fk_id_producto","=","productos.id")
                     ->where([
                        ["fk_id_producto","=",$id],
                        ["fk_id_sede","=",$datos->datos->fk_id_sede]
                     ])
                     ->get();   
            
            if(count($resp)>0){
                    switch ($datos->datos->tipo_venta_producto) {
                            case 'PorUnidad':
                                # code...
                                $datos->datos->unidades_por_blister=1;
                              

                                $datos->datos->precio_venta_blister=$datos->datos->precio_mayoreo;
                                

                                $datos->datos->porcentaje_ganancia_blister=$datos->datos->porcentaje_ganancia;
                                $datos->datos->porcentaje_ganancia_unidad=$datos->datos->porcentaje_ganancia;                    
                                
                                

                                break;
                            case 'Caja':
                            $datos->datos->precio_venta_blister=$datos->datos->precio_mayoreo;
                                # code...
                                break;
                             case 'CajaBlister':
                                    # code...
                                    break;   
                            
                        }


                if($resp[0]->fk_id_sede==$datos->datos->fk_id_sede){
                    //falkta actualizar las cantidades



                    DB::table("detalle_inventarios")
                            ->where([ ["fk_id_producto","=",$id],
                                     ["fk_id_sede","=",$datos->datos->fk_id_sede]])
                            ->update(["precio_venta_sede"=>$datos->datos->precio_venta,
                                       "precio_venta_blister_sede"=>$datos->datos->precio_venta_blister, 
                                      "precio_mayoreo_sede"=>$datos->datos->precio_mayoreo,
                                       "porcentaje_ganancia_sede"=>$datos->datos->porcentaje_ganancia,
                                       "porcentaje_ganancia_blister_sede"=>$datos->datos->porcentaje_ganancia_blister,
                                       "porcentaje_ganancia_sede_unidad"=>$datos->datos->porcentaje_ganancia_unidad,
                                       "minimo_inventario_sede"=>$datos->datos->minimo_inventario,
                                       "cantidad_existencias_unidades"=>$resp[0]->cantidad_existencias_unidades,
                                       "cantidad_existencias_blister"=>floor($resp[0]->cantidad_existencias_unidades/$datos->datos->unidades_por_blister),
                                       "cantidad_existencias"=>floor(($resp[0]->cantidad_existencias_unidades/$datos->datos->unidades_por_blister)/$datos->datos->unidades_por_caja),                                
                                        "updated_at"=>$datos->hora_cliente]);
                    return response()->json(["respuesta"=>true,"mensaje"=>"Producto editado para esta sede"]);
                }else{
                    return response()->json(["respuesta"=>false,"mensaje"=>"Producto no esta registrado para esta sede"]);
                }
            }else{
                   //se agrega el producto a la sede
                    $dt=new DetalleInventario();
                    $dt->insertar([ "fk_id_producto"=>$id,
                                     "fk_id_sede"=>$datos->datos->fk_id_sede,
                                     "cantidad_existencias"=>0,
                                     "cantidad_existencias_unidades"=>0,
                                     "cantidad_existencias_blister"=>0,
                                     "precio_venta_sede"=>$datos->datos->precio_venta,
                                     "precio_venta_blister_sede"=>$datos->datos->precio_venta_blister, 
                                      "precio_mayoreo_sede"=>$datos->datos->precio_mayoreo,
                                       "porcentaje_ganancia_sede"=>$datos->datos->porcentaje_ganancia,
                                       "porcentaje_ganancia_blister_sede"=>$datos->datos->porcentaje_ganancia_blister,
                                       "porcentaje_ganancia_sede_unidad"=>$datos->datos->porcentaje_ganancia_unidad,
                                        "minimo_inventario_sede"=>$datos->datos->minimo_inventario,
                                        "created_at"=>$datos->hora_cliente,
                                        "updated_at"=>$datos->hora_cliente]);
                return response()->json(["respuesta"=>true,"mensaje"=>"Producto editado y agregado para esta sede"]);
            }
            
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
        $prod=new Producto();
        return response()->json($prod->eliminar(array(["id","=",$id])));
    }

    public function traer_productos($pro,$sede){
       
        $prod=new Producto();
        
        return $prod->buscar_producto_por_sede(trim($pro),$sede);
    }
    public function traer_productos_para_factura($pro,$sede){
       
        $prod=new Producto();
        return $prod->buscar_producto_por_sede_para_factura(trim($pro),$sede);
    }
    public function buscar_proveedor() {
        $pro=DB::table("productos")
                ->groupby("proveedor")
                ->select("proveedor")
                ->get();
        return response()->json(["respuesta"=>true,"mensaje"=>"Proveedores encontrados","datos"=>$pro]);
    }
    public function traer_productos_por_proveedor($pro,$provee,$sed){
       
        $prod=new Producto();
        
        return $prod->buscar_producto_por_proveedor($pro,$provee,$sed);
    }
    
    public function editar_informacion(Request $request){
        
        $datos=json_decode($request->get("datos"));
        
        $campo=$datos->datos->campo;
        $columan_dt = DB::select('SHOW COLUMNS FROM detalle_inventarios WHERE Field = ?', [$campo]);
        $columan2_pr = DB::select('SHOW COLUMNS FROM productos WHERE Field = ?', [$campo]);
        $id_producto;
        $nom_tabla;
        $valor=$datos->datos->valor;
        $sede=$datos->datos->sede;
        $usuario=$datos->datos->usuario;
        //echo "sede\n";
        //var_dump($sede);
        //echo "campo\n";
        //var_dump($campo);
        //echo "valor\n";
        //var_dump($valor);
        //echo "columna_dt\n";
        //var_dump(count($columan_dt));
        //echo "columna2_pr\n";
        //var_dump(count($columan2_pr));
        
        if(count($columan_dt)>0){
            $id_producto=$datos->datos->id_producto_inventario;
            $nom_tabla="detalle_inventarios";
        }
        else{
            $id_producto=$datos->datos->id_producto;            
            $nom_tabla="productos";
        }
        //echo "id_producto\n";
        //var_dump($datos->datos->id_producto);
        //echo "id_producto_inventario\n";
        //var_dump($datos->datos->id_producto_inventario);
        //echo "id_producto\n";
        //var_dump($id_producto);
        //echo "nombre_columna\n";
        //var_dump($nom_tabla);
        if($nom_tabla=="productos"){
            if($sede==0 ){

              
                    $actualizar=true;
                     $campo2=$campo;
                     switch ($campo){
                         case "precio_compra":
                             
                             $valor=(double)$datos->datos->valor;
                               $actualizar=false;
                               $pp=DB::table("productos")
                                       ->where("id","=",$id_producto)
                                       ->get();
                               $pp_dt=DB::table("detalle_inventarios")
                                       ->where("fk_id_producto","=",$id_producto)
                                       ->get();

                             $precio_blister=($valor/$pp[0]->unidades_por_caja);
                             $precio_unidad=($precio_blister/$pp[0]->unidades_por_blister);

                             $impuesto_caja=$valor*($pp[0]->impuesto/100);
                             $impuesto_blister=($precio_blister)*($pp[0]->impuesto/100);
                             $impuesto_unidad=($precio_unidad)*($pp[0]->impuesto/100);         
                             //actualizo productos
                             $new_precio_compra_blister=($precio_blister)+$impuesto_blister;
                             $new_precio_compra_unidad=($precio_unidad)+$impuesto_unidad;

                             $dif=($pp[0]->precio_venta/((double)$valor+$impuesto_caja))-1;
                             $diff_blister=($pp[0]->precio_venta_blister/(double)$new_precio_compra_blister)-1;
                             $diff_unidad=($pp[0]->precio_mayoreo/(double)$new_precio_compra_unidad)-1;
                             
                             $porcentaje=(double)round((($dif)*100),2);
                             $porcentaje_bli=(double)round((($diff_blister)*100),2);
                             $porcentaje_uni=(double)round((($diff_unidad)*100),2);
                             
                               
                               DB::table("productos")
                                       ->where("id","=",$id_producto)
                                       ->update([
                                          "precio_compra"=>$valor,
                                          "precio_compra_blister"=>$precio_blister,
                                          "precio_compra_unidad"=>$precio_unidad,
                                          "precio_compra"=>$new_precio_compra,
                                          "precio_compra_blister"=>$new_precio_compra_blister,
                                          "precio_compra_unidad"=>$new_precio_compra_unidad, 
                                          "porcentaje_ganancia"=>$porcentaje,
                                          "porcentaje_ganancia_blister"=>$porcentaje_bli,
                                          "porcentaje_ganancia_unidad"=>$porcentaje_uni
                                         ]);
                             
                             //actualiza detall inventario
                               
                             $dif_dt=($pp_dt[0]->precio_venta_sede/(double)$valor)-1;
                             $diff_blister_dt=($pp_dt[0]->precio_venta_blister_sede/(double)$new_precio_compra_blister)-1;
                             $diff_unidad_dt=($pp_dt[0]->precio_mayoreo_sede/(double)$new_precio_compra_unidad)-1;
                             
                             $porcentaje_dt=(double)round((($dif_dt)*100),2);
                             $porcentaje_bli_dt=(double)round((($diff_blister_dt)*100),2);
                             $porcentaje_uni_dt=(double)round((($diff_unidad_dt)*100),2);
                          
                            
                                 DB::table("detalle_inventarios")
                                       ->where("fk_id_producto","=",$id_producto)
                                       ->update([
                                           "porcentaje_ganancia_sede"=>$porcentaje_dt,
                                           "porcentaje_ganancia_blister_sede"=>$porcentaje_bli_dt,
                                           "porcentaje_ganancia_sede_unidad"=>$porcentaje_uni_dt]);
                               
                             break;
                         case "precio_venta":
                             $campo2.="_sede";
                             $valor=(double)$datos->datos->valor;
                             //calcular el nuevo porcentaje
                             $pre_com=DB::table("productos")
                                     ->where("id","=",$datos->datos->id_producto)
                                     ->select("productos.precio_compra_impuesto")
                                     ->get();
                            
                             
                             $dif=($valor/(double)$pre_com[0]->precio_compra_impuesto)-1;
                             
                             $porcentaje=(double)round((($dif)*100),2);
                          
                             Producto::where("id",$datos->datos->id_producto)
                                     ->update(["porcentaje_ganancia"=>$porcentaje]);
                            
                             DB::update('update detalle_inventarios set porcentaje_ganancia_sede ='.$porcentaje.'  where fk_id_producto = ?', [$datos->datos->id_producto]);
                             
                               $actualizar=true;
                             break;
                         case "precio_venta_blister":
                             $campo2.="_sede";
                             $valor=(double)$datos->datos->valor;
                             $pre_com=DB::table("productos")
                                     ->where("id","=",$datos->datos->id_producto)
                                     ->select("productos.precio_compra_blister_impuesto")
                                     ->get();
                            
                             
                             $dif=($valor/(double)$pre_com[0]->precio_compra_blister_impuesto)-1;
                             
                             $porcentaje=(double)round((($dif)*100),2);
                            
                             
                             
                             Producto::where("id",$datos->datos->id_producto)
                                     ->update(["porcentaje_ganancia_blister"=>$porcentaje]);
                            
                             DB::update('update detalle_inventarios set porcentaje_ganancia_blister_sede ='.$porcentaje.'  where fk_id_producto = ?', [$datos->datos->id_producto]);
                               $actualizar=true;
                             break;
                         case "precio_mayoreo":
                             $campo2.="_sede";
                             $valor=(double)$datos->datos->valor;
                             $pre_com=DB::table("productos")
                                     ->where("id","=",$datos->datos->id_producto)
                                     ->select("productos.precio_compra_unidad_impuesto")
                                     ->get();
                            
                             
                             $dif=($valor/(double)$pre_com[0]->precio_compra_unidad_impuesto)-1;
                             
                             $porcentaje=(double)round((($dif)*100),2);
                            
                             
                             
                             Producto::where("id",$datos->datos->id_producto)
                                     ->update(["porcentaje_ganancia_unidad"=>$porcentaje]);
                            
                             DB::update('update detalle_inventarios set porcentaje_ganancia_sede_unidad ='.$porcentaje.'  where fk_id_producto = ?', [$datos->datos->id_producto]);
                               $actualizar=true;
                             break;
                         case "minimo_inventario":
                             $campo2.="_sede";
                             $valor=(int)$datos->datos->valor;                             
                             break;
                         case "unidades_por_caja":
                             
                            $valor=(int)$datos->datos->valor;
                            $actualizar=true;
                            $actualizar2=false;
                             //actulizar las cantidades de las cajas y los blister
                              $s=DB::table("sedes")
                                    ->get();
                            foreach ($s as $key => $value) {
                                
                                $d=DB::table("detalle_inventarios")
                                    ->join("productos","productos.id","=","detalle_inventarios.fk_id_producto")
                                    ->where([["productos.id","=",$id_producto],["fk_id_sede","=",$value->id]])
                                         ->select("detalle_inventarios.id",
                                                    "detalle_inventarios.fk_id_producto",
                                                    "detalle_inventarios.cantidad_existencias_unidades",
                                                    "detalle_inventarios.cantidad_existencias_blister",
                                                    "detalle_inventarios.cantidad_existencias",
                                                    "productos.unidades_por_caja",
                                                    "productos.unidades_por_blister",
                                                    "productos.precio_compra"
                                                    )
                                    ->get();
                                if(count($d)>0){
                                    if((int)$d[0]->unidades_por_blister!=0 && $valor != 0){
                                     DB::table("productos")
                                        ->where("productos.id","=",$id_producto)        
                                        ->update(["precio_compra_blister"=>(int)$d[0]->precio_compra/(int)$valor,"precio_compra_unidad"=>(int)((int)$d[0]->precio_compra/(int)$valor)/(int)$d[0]->unidades_por_blister]);
                                    }else{
                                        return response()->json(["respuesta"=>false,"mensaje"=>"Unidades de blister estan en cero"]);
                                    }    
                                

                                    if((int)$d[0]->unidades_por_blister != 0 && $valor != 0){
                                        DB::table("detalle_inventarios")
                                        ->where("id","=",$d[0]->id)
                                        ->update([
                                                    "cantidad_existencias"=>floor(((int)$d[0]->cantidad_existencias_unidades/(int)$d[0]->unidades_por_blister)/(int)$valor),
                                                    "cantidad_existencias_blister"=>floor(((int)$d[0]->cantidad_existencias_unidades/(int)$d[0]->unidades_por_blister))]);    
                                    }else{
                                        return response()->json(["respuesta"=>false,"mensaje"=>"Unidades de blister estan en cero"]);
                                    }

                                }    
                                    
                                    

                               
                                
                            }
                            
                              $actualizar=false;
                             break;
                         case "unidades_por_blister":
                             $valor=(int)$datos->datos->valor;
                            $actualizar=true;
                            $actualizar2=false;
                             //actulizar las cantidades de las cajas y los blister
                            $s=DB::table("sedes")
                                    ->get();
                            foreach ($s as $key => $value) {
                                
                                $d=DB::table("detalle_inventarios")
                                    ->join("productos","productos.id","=","detalle_inventarios.fk_id_producto")
                                    ->where([["productos.id","=",$id_producto],["fk_id_sede","=",$value->id]])
                                        ->select("detalle_inventarios.id",
                                                    "detalle_inventarios.fk_id_producto",
                                                    "detalle_inventarios.cantidad_existencias_unidades",
                                                    "detalle_inventarios.cantidad_existencias_blister",
                                                    "detalle_inventarios.cantidad_existencias",
                                                    "productos.unidades_por_caja",
                                                    "productos.unidades_por_blister",
                                                    "productos.precio_compra"
                                                    )
                                    ->get();
                                
                                   
                                
                                if(count($d)>0){
                                    if((int)$d[0]->unidades_por_caja != 0 && (int)$valor != 0){
                                         DB::table("productos")
                                            ->where("productos.id","=",$id_producto)        
                                            ->update(["precio_compra_blister"=>(int)$d[0]->precio_compra/(int)$d[0]->unidades_por_caja,"precio_compra_unidad"=>(int)((int)$d[0]->precio_compra/(int)$d[0]->unidades_por_caja)/(int)$valor]);
                                    }else{
                                        return response()->json(["respuesta"=>false,"mensaje"=>"Unidades de caja estan en cero"]);
                                    }    
                                    
                                    
                                    DB::table("detalle_inventarios")
                                        ->where("id","=",$d[0]->id)
                                        ->update([  
                                                    "cantidad_existencias"=>floor(((int)$d[0]->cantidad_existencias_unidades/(int)$valor)/(int)$d[0]->unidades_por_caja),
                                                    "cantidad_existencias_blister"=>floor(((int)$d[0]->cantidad_existencias_unidades/(int)$valor))]);
                                 
                                }
                                
                            }
                              $actualizar=false;
                           
                            
                            
                             break;
                         case "codigo_producto":
                             $actualizar=false;
                             break;
                     }
                     
                     DB::table($nom_tabla)
                             ->where("id","=",$id_producto)
                             ->update([$campo=>$valor]);
                      if($actualizar){
                          DB::table("detalle_inventarios")
                         ->where([["detalle_inventarios.fk_id_producto","=",$datos->datos->id_producto]])
                         ->update([$campo2=>$valor]);
                     
                      }
                     
                
            }
            else{
                    $actualizar=true;//para actualizar en tabla productos
                    $actualizar2=true;//para actualizar en tabal detall inventarios
                    $campo2=$campo;
                     switch ($campo){
                         case "precio_compra":
                             $campo2.="_sede";
                             $valor=(double)$datos->datos->valor;
                             $actualizar=true;
                             $actualizar2=false;
                             break;
                         case "precio_venta":
                             $campo2.="_sede";
                             $valor=(double)$datos->datos->valor; 
                             
                             $pre_com=DB::table("productos")
                                     ->where("id","=",$datos->datos->id_producto)
                                     ->select("productos.precio_compra_impuesto")
                                     ->get();
                            
                             
                             $dif=($valor/(double)$pre_com[0]->precio_compra_impuesto)-1;
                             
                             $porcentaje=(double)round((($dif)*100),2);
                          
                             
                            
                             DB::table("detalle_inventarios")
                                     ->where([["fk_id_producto","=",$datos->datos->id_producto],["fk_id_sede","=",$sede]])
                                     ->update(["porcentaje_ganancia_sede"=>$porcentaje]);
                                     
                              
                             $actualizar=false;
                             $actualizar2=true;
                             break;
                         case "precio_venta_blister":
                             $campo2.="_sede";
                             $valor=(double)$datos->datos->valor;
                              $pre_com=DB::table("productos")
                                     ->where("id","=",$datos->datos->id_producto)
                                     ->select("productos.precio_compra_blister_impuesto")
                                     ->get();
                            
                             
                             $dif=($valor/(double)$pre_com[0]->precio_compra_blister_impuesto)-1;
                             
                             $porcentaje=(double)round((($dif)*100),2);
                          
                             
                            
                             DB::table("detalle_inventarios")
                                     ->where([["fk_id_producto","=",$datos->datos->id_producto],["fk_id_sede","=",$sede]])
                                     ->update(["porcentaje_ganancia_blister_sede"=>$porcentaje]);
                                     
                             $actualizar=false;
                             $actualizar2=true;
                             break;
                         case "precio_mayoreo":
                             $campo2.="_sede";
                             
                             if($datos->datos->valor==0){
                                $datos->datos->valor=0.1;
                             }

                             $valor=(double)$datos->datos->valor;

                             
                              $pre_com=DB::table("productos")
                                     ->where("id","=",$datos->datos->id_producto)
                                     ->select("productos.precio_compra_unidad_impuesto")
                                     ->get();
                            
                             
                             $dif=($valor/(double)$pre_com[0]->precio_compra_unidad_impuesto)-1;
                             
                             $porcentaje=(double)round((($dif)*100),2);
                          
                             //echo "%";
                             //var_dump($porcentaje);
                             DB::table("detalle_inventarios")
                                     ->where([
                                                ["fk_id_producto","=",$datos->datos->id_producto],
                                                ["fk_id_sede","=",$sede]
                                            ])
                                     ->update(["porcentaje_ganancia_sede_unidad"=>$porcentaje]);
                             
                             $actualizar=false;
                             $actualizar2=true;

                             break;
                         case "minimo_inventario":
                             
                             $campo2.="_sede";
                             $valor=(int)$datos->datos->valor;
                             $actualizar=false;
                             $actualizar2=true;
                             
                             
                             break;
                         case "unidades_por_caja":
                             
                            $valor=(int)$datos->datos->valor;
                            $actualizar=true;
                            $actualizar2=false;
                             //actulizar las cantidades de las cajas y los blister
                            $d=DB::table("detalle_inventarios")
                                    
                                    ->join("productos","productos.id","=","detalle_inventarios.fk_id_producto")
                                    ->where([["productos.id","=",$id_producto],["fk_id_sede","=",$sede]])
                                    ->get();
                            if($d[0]->unidades_por_blister!= 0){
                                DB::table("productos")
                                ->where("productos.id","=",$id_producto)        
                                ->update(["precio_compra_blister"=>(int)$d[0]->precio_compra/(int)$valor,"precio_compra_unidad"=>(int)((int)$d[0]->precio_compra/(int)$valor)/(int)$d[0]->unidades_por_blister]);
    
                            }else{
                                return response()->json(["respuesta"=>false,"mensaje"=>"Unidades de caja estan en cero"]);
                            }        
                            
                            if($d[0]->unidades_por_blister!= 0){
                                DB::table("detalle_inventarios")
                                ->where("fk_id_producto","=",$id_producto)
                                ->update(["cantidad_existencias"=>floor(((int)$d[0]->cantidad_existencias_unidades/(int)$d[0]->unidades_por_blister)/(int)$valor)]);    
                            }else{
                                return response()->json(["respuesta"=>false,"mensaje"=>"Unidades de blister estan en cero"]);
                            }

                            
                             break;
                         case "unidades_por_blister":
                             $valor=(int)$datos->datos->valor;
                            $actualizar=true;
                            $actualizar2=false;
                             //actulizar las cantidades de las cajas y los blister

                           

                            $d=DB::table("detalle_inventarios")
                                    
                                    ->join("productos","productos.id","=","detalle_inventarios.fk_id_producto")
                                    ->where([["productos.id","=",$id_producto],["fk_id_sede","=",$sede]])
                                    ->get();
                            if($d[0]->unidades_por_caja!= 0){
                                DB::table("productos")
                                ->where("productos.id","=",$id_producto)        
                                ->update(["precio_compra_blister"=>(int)$d[0]->precio_compra/(int)$d[0]->unidades_por_caja,"precio_compra_unidad"=>(int)((int)$d[0]->precio_compra/(int)$d[0]->unidades_por_caja)/(int)$valor]);
                                
                            }else{
                                return response()->json(["respuesta"=>false,"mensaje"=>"Unidades de blister estan en cero"]);
                            }        
                            

                            DB::table("detalle_inventarios")
                                ->where("fk_id_producto","=",$id_producto)
                                ->update(["cantidad_existencias_blister"=>floor(((int)$d[0]->cantidad_existencias_unidades/(int)$valor))]);
                             break;
                     }
                     if($actualizar){
                          DB::table($nom_tabla)
                            ->where("id","=",$id_producto)
                             ->update([$campo=>$valor]);   
                     } 
                     //no actualiza minimo inventario
                     //echo "--";
                     //var_dump($campo2);
                     //var_dump($valor);
                     if($actualizar2){
                         DB::table("detalle_inventarios")
                         ->where([
                             ["detalle_inventarios.fk_id_producto","=",$datos->datos->id_producto],
                             ["detalle_inventarios.fk_id_sede","=",$sede]])
                         ->update([$campo2=>$valor]);
                        
                     }
                     
                    
            }
            
        
        }
        else if($nom_tabla=="detalle_inventarios"){
            //si la tabla es detallle_inventarios
           
            if($sede==0 ){
                $s=DB::table("sedes")
                       ->select()
                         ->get();
                 //recorro todas las sedes
                 foreach ($s as $key => $value) {
                     DB::table($nom_tabla)
                    ->where([["id","=",$id_producto],["fk_id_sede","=",$value->id]])
                     ->update([$campo=>$valor]);
                 }
                
            }
            else{
               
            
                switch ($campo){
                    case "cantidad_existencias_unidades":
                        if($valor<0){
                            return response()->json(["respuesta"=>false,"mensaje"=>"la unidades no pueden ser negativas"]);
                        }
                        $d=DB::table($nom_tabla)
                                ->join("productos","productos.id","=",$nom_tabla.".fk_id_producto")
                                ->where($nom_tabla.".id","=",$id_producto)
                                ->get();
                        //echo floor((int)$valor/(int)$d[0]->unidades_por_blister)."--\n";
                        //echo floor(((int)$valor/(int)$d[0]->unidades_por_blister)/(int)$d[0]->unidades_por_caja);
                        
                        if($valor>0){
                            $es="activo";
                        }else{
                            $es="agotado";
                        }
                        if($d[0]->unidades_por_blister != 0 && $d[0]->unidades_por_caja != 0){
                            DB::table($nom_tabla)
                            ->where("id","=",$id_producto)
                             ->update([ 
                                        "cantidad_existencias_blister"=>floor((int)$valor/(int)$d[0]->unidades_por_blister),
                                        "cantidad_existencias"=>floor(((int)$valor/(int)$d[0]->unidades_por_blister)/(int)$d[0]->unidades_por_caja),
                                        "estado_inventario"=>$es]);


                            DB::table("movimientos_inventario")
                                      ->insert(["fk_id_det_inventario"=>$id_producto,
                                                "habia"=>$d[0]->cantidad_existencias_unidades,
                                            "tipo"=>"AJUSTE",
                                            "descripcion"=>"unidad",
                                            "cantidad"=>$valor,
                                            "quedan"=>$valor,
                                            "observaciones"=>"Ajuste de unidades en inventario ".$datos->hora_cliente,
                                            "fk_id_usuario"=>$usuario,
                                            "updated_at"=>$datos->hora_cliente,
                                            "created_at"=>$datos->hora_cliente  ]);    
                        }else{
                            return response()->json(["respuesta"=>false,"mensaje"=>"Las unidades de caja y blister deben ser mayor a cero"]);
                        }
                           
                        

                        break;
                    case "minimo_inventario":
                        $campo.="_sede";
                       
                        
                        break;
                    case "estado_inventario":
                     //echo  $valor;
                        break;   
                }
                 DB::table($nom_tabla)
                    ->where("id","=",$id_producto)
                     ->update([$campo=>$valor]);
                 
            }
            //echo "here ";
            DB::table("movimientos_inventario")
                ->insert(["fk_id_det_inventario"=>$id_producto,
                                        "habia"=>0,
                                        "tipo"=>"EDICION",
                                        "descripcion"=>"n/a",
                                        "cantidad"=>0,
                                        "quedan"=>0,
                                        "observaciones"=>"Ajuste de informacion del campo ".$campo." en inventario nuevo dato ".$valor. ", ajuste en la fecha ".$datos->hora_cliente,
                                        "fk_id_usuario"=>$usuario,
                                        "updated_at"=>$datos->hora_cliente,
                                        "created_at"=>$datos->hora_cliente  ]);
        }
        
        

        return response()->json(["respuesta"=>true,"mensaje"=>"Producto editado "]);
        
        
        
        
    }
    public function crear_productos_inventario(Request $request){
        var_dump("wtf?");
        $prod=new Producto();
        $dt=new DetalleInventario();
        $mi=new MovimientosInventario();
        $datos=json_decode($request->get('datos'));
        //consulto que no exista producto
            $pro=$prod->consultar_por_campo(array(array("codigo_producto",'=',$datos->datos->codigo_producto)),"AND",array());
            
        
        if($pro["respuesta"]==false){
            //insero si no existe
            if($datos->datos->precio_compra==0){
                $datos->datos->precio_compra=1;
            }
            if($datos->datos->precio_compra_blister==0){
                $datos->datos->precio_compra_blister=1;
            }
            if($datos->datos->precio_compra_unidad==0){
                $datos->datos->precio_compra_unidad=1;
            }
            if($datos->datos->precio_venta==0){
                $datos->datos->precio_venta=1;
            }
            if($datos->datos->precio_venta_blister==0){
                $datos->datos->precio_venta_blister=1;
            }
            if($datos->datos->precio_mayoreo==0){
                $datos->datos->precio_mayoreo=1;
            }
            $dif=$datos->datos->precio_venta-(double)$datos->datos->precio_compra;
            $porcentaje_ganancia=(double)round((($dif)*100)/$datos->datos->precio_venta,2);

            $dif=$datos->datos->precio_venta_blister-(double)$datos->datos->precio_compra_blister;
            $porcentaje_ganancia_blister=(double)round((($dif)*100)/$datos->datos->precio_venta_blister,2);

            $dif=$datos->datos->precio_mayoreo-(double)$datos->datos->precio_compra_unidad;
            $porcentaje_ganancia_unidad=(double)round((($dif)*100)/$datos->datos->precio_mayoreo,2);
            

            $arr=$prod->insertar(array(
                "codigo_producto"=>$datos->datos->codigo_producto,
                "codigo_distribuidor"=>$datos->datos->codigo_distribuidor,
                "nombre_producto"=>$datos->datos->nombre_producto,
                "nombre_producto_venta"=>$datos->datos->nombre_producto,
                "descripcion_producto"=>$datos->datos->descripcion_producto,
                "laboratorio"=>$datos->datos->laboratorio,
                "tipo_presentacion"=>$datos->datos->tipo_presentacion_producto,
                "tipo_venta_producto"=>$datos->datos->tipo_venta_producto,
                "precio_compra"=>$datos->datos->precio_compra,
                "precio_compra_blister"=>$datos->datos->precio_compra_blister,
                "precio_compra_unidad"=>$datos->datos->precio_compra_unidad,                
                /*"precio_compra_impuesto"=>$datos->datos->precio_compra_impuesto,
                "precio_compra_blister_impuesto"=>$datos->datos->precio_compra_blister_impuesto,
                "precio_compra_unidad_impuesto"=>$datos->datos->precio_compra_unidad_impuesto,*/
                "precio_venta"=>$datos->datos->precio_venta,
                "precio_venta_blister"=>$datos->datos->precio_venta_blister,
                "precio_mayoreo"=>$datos->datos->precio_mayoreo,
                "unidades_por_caja"=>$datos->datos->unidades_por_caja,
                "unidades_por_blister"=>$datos->datos->unidades_por_blister,                
                "porcentaje_ganancia"=>$porcentaje_ganancia,
                "porcentaje_ganancia_blister"=>$porcentaje_ganancia_blister,
                "porcentaje_ganancia_unidad"=>$porcentaje_ganancia_unidad,
                "minimo_inventario"=>$datos->datos->minimo_inventario,
                "fk_id_departamento"=>$datos->datos->fk_id_departamento,
                "fk_id_proveedor"=>$datos->datos->fk_id_proveedor,
                "created_at"=>$datos->hora_cliente,
                "updated_at"=>$datos->hora_cliente, 
                
                "grupo"=>"n/a",
                "sub_grupo"=>"n/a"
            ));
            
            if($datos->datos->fk_id_sede!=false){
            $id;
            if($pro["respuesta"]==false){
                $id=$arr["id"];
            }else{
                $id=$pro["datos"][0]->id;
            }
            if($datos->datos->tipo_venta_producto=="PorUnidad"){
               $unidades =$datos->datos->cantidad_existencias;
            }else{
                $unidades=$datos->datos->cantidad_existencias*$datos->datos->unidades_por_caja;
            }
            $rr=$dt->insertar([
                    "fk_id_producto"=>$id,
                    "fk_id_sede"=>$datos->datos->fk_id_sede,                   

                     "cantidad_existencias"=>$datos->datos->cantidad_existencias,
                     "cantidad_existencias_blister"=>$unidades,
                     "cantidad_existencias_unidades"=>$unidades,
                    "precio_venta_sede"=>$datos->datos->precio_venta,     
                    "precio_venta_blister_sede"=>$datos->datos->precio_venta_blister,                 
                    "precio_mayoreo_sede"=>$datos->datos->precio_mayoreo,
                    "porcentaje_ganancia_sede"=>$porcentaje_ganancia,
                    "porcentaje_ganancia_blister_sede"=>$porcentaje_ganancia_blister,
                    
                    "porcentaje_ganancia_sede_unidad"=>$porcentaje_ganancia_unidad,
                    "minimo_inventario_sede"=>$datos->datos->minimo_inventario,
                    "created_at"=>$datos->hora_cliente,
                    "updated_at"=>$datos->hora_cliente, 
                    "estado_producto_sede"=>"1",
                    "estado_inventario"=>"activo"
                    ]);
                  if($rr["respuesta"]==TRUE){
                      $mi->insertar([
                        "fk_id_det_inventario"=>$rr["id"],
                        "habia"=>0,
                          "fk_id_usuario"=>$datos->datos->fk_id_usuario,
                        "tipo"=>"ENTRADA",
                        "cantidad"=>$datos->datos->cantidad_existencias,
                        "quedan"=>$datos->datos->cantidad_existencias,
                         "created_at"=>$datos->hora_cliente,
                        "updated_at"=>$datos->hora_cliente, 
                        "observaciones"=>"Entrada inicial de inventario"
                        ]);
                      return response()->json(["respuesta"=>true,"mensaje"=>"Producto registrado"]);
                      
                  }else{
                    return response()->json(["respuesta"=>false,"mensaje"=>"No se a podido registrar el movimiento"]);
                  }
        }else{
                //aqui registrar en todas las sedes
                $se=new Sede();
                $sed=$se->consultar_todos();
                $dti=new DetalleInventario();
                $id_pro=0;
                    if($pro["respuesta"]==false){
                        $id_pro=$arr["id"];
                   }else{
                        $id_pro=$pro["datos"][0]->id;
                   }
                   
                   if($datos->datos->tipo_venta_producto=="PorUnidad"){
                        $unidades =$datos->datos->cantidad_existencias;
                     }else{
                         $unidades=$datos->datos->cantidad_existencias*$datos->datos->unidades_por_caja;
                     }
                        foreach ($sed["datos"] as $key => $value) {
                           

                            $rr=$dti->insertar([
                                "fk_id_producto"=>$id_pro,
                                "fk_id_sede"=>$value->id,                    
                                 "cantidad_existencias"=>$datos->datos->cantidad_existencias,
                                 "cantidad_existencias_blister"=>$unidades,
                                 "cantidad_existencias_unidades"=>$unidades,
                                "precio_venta_sede"=>$datos->datos->precio_venta,     
                                "precio_venta_blister_sede"=>$datos->datos->precio_venta_blister,                 
                                "precio_mayoreo_sede"=>$datos->datos->precio_mayoreo,
                                "porcentaje_ganancia_sede"=>$porcentaje_ganancia,
                                "porcentaje_ganancia_blister_sede"=>$porcentaje_ganancia_blister,
                                
                                "porcentaje_ganancia_sede_unidad"=>$porcentaje_ganancia_unidad,
                                "minimo_inventario_sede"=>$datos->datos->minimo_inventario,
                                "created_at"=>$datos->hora_cliente,
                                "updated_at"=>$datos->hora_cliente, 
                                "estado_inventario"=>"activo"
                                ]);
                            
                              if($rr["respuesta"]==true && $datos->datos->cantidad_existencias>0){
                                if($datos->datos->cantidad_existencias>0){
                                        DB::table('detalle_inventarios')
                                        ->where('id',"=",$rr["id"])
                                        ->update(["estado_producto_sede"=>"1","estado_inventario"=>"activo"]);
                                        
                                }
                                    $ri=$mi->insertar([
                                        "fk_id_det_inventario"=>$rr["id"],
                                        "habia"=>0,
                                        "tipo"=>"ENTRADA",
                                        "fk_id_usuario"=>$datos->datos->fk_id_usuario,
                                        "cantidad"=>$datos->datos->cantidad_existencias,
                                        "quedan"=>$datos->datos->cantidad_existencias,
                                         "created_at"=>$datos->hora_cliente,
                                        "updated_at"=>$datos->hora_cliente, 
                                        "observaciones"=>"Entrada inicial de inventario"
                                        ]);

                                    if($ri["respuesta"]==false){
                                        return response()->json(["mensaje"=>"movimiento inventario producto NO registrado","respuesta"=>false]);      
                                    }
                                    //return response()->json(["mensaje"=>"producto registrado","respuesta"=>true]);
                              }else{
                                //return response()->json(["mensaje"=>"detalle inventario producto NO registrado","respuesta"=>false]);
                              }
                        }

                   return response()->json(["mensaje"=>"producto registrado","respuesta"=>true]);


            }  
        }
        else{
            return response()->json(["mensaje"=>"producto NO registrado este codigo ya exite","respuesta"=>false]);            
        }
        


        
    }
  
  
}

