<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use DB;

class Producto extends Model
{
    //
    //
	private $TABLA="productos";

    public function consultar_todos(){
    	$datos=DB::table($this->TABLA)
    		->get();

    	return array("mensaje"=>"Elementos consultados",
    				"respuesta"=>true,
    				"datos"=>$datos);	

    }
   //$campo=array('campo1','signo_comparacion','campo2')
     public function consultar_por_campo($campo=array(),$operador,$campo2){
        if($operador=="AND"){
            $datos=DB::table($this->TABLA)
                    ->where($campo)
                    ->limit(10)
                    ->get();
                 if(count($datos)>0){
                    return array("mensaje"=>"Elementos consultados",
                            "respuesta"=>true,
                            "datos"=>$datos);   
                 }else{
                    return array("mensaje"=>"Elementos NO encontrados",
                            "respuesta"=>false,
                            "datos"=>$datos);
                 }  
        }else if($operador=="OR"){
           
            $datos=DB::table($this->TABLA)
            ->where($campo)
            ->orwhere($campo2)
            ->limit(10)
            ->get();
         if(count($datos)>0){
            return array("mensaje"=>"Elementos consultados",
                    "respuesta"=>true,
                    "datos"=>$datos);   
         }else{
            return array("mensaje"=>"Elementos NO encontrados",
                    "respuesta"=>false,
                    "datos"=>$datos);
         }
        }
           
        
    }
    //$campo=array('campo1'=>'valor1','campo2'=>'valor2','campo3'=>'valor3')
    public function insertar($campos=array()){
    	$id=DB::table($this->TABLA)
    		->insertGetId($campos);
    	return array("mensaje"=>"Elemento insertado",
    				"respuesta"=>true,
    				"id"=>$id);			
    }
    //$campo=array('campo1'=>'valor1','campo2'=>'valor2','campo3'=>'valor3')
    //$filtro=array('campo1','signo_comparacion','campo2')
    public function editar($campos,$filtro){
    	DB::table($this->TABLA)
    		->where($filtro)
    		->update($campos);
    	return array("mensaje"=>"Elemento editado",
    				"respuesta"=>true);
    }

    public function eliminar($filtro){
    	$datos=DB::table($this->TABLA)
    		->where($filtro)
    		->get();

    	if(count($datos)>0){
    		if($datos[0]->estado_producto==1){
    			DB::table($this->TABLA)
    			->where("id",$datos[0]->id)
    			->update(["estado_producto"=>"0"]);	
    			return array("mensaje"=>"Elemento deshabilitado",
    				"respuesta"=>true);
    		}else{
    			DB::table($this->TABLA)
    			->where("id",$datos[0]->id)
    			->update(["estado_producto"=>"1"]);
    			return array("mensaje"=>"Elemento habilitado",
    				"respuesta"=>true);
    		}
    		
    	}else{
    		return array("mensaje"=>"Elemento no existe",
    				"respuesta"=>false);
    	}
    }

    //Busqueda para los productos de cada sede
    public function buscar_producto_por_sede($pro,$sede){

        //var_dump($pro);
        //var_dump($sede);
        if($pro=="*"){
                $datos=DB::table('productos')
                    ->join('detalle_inventarios','productos.id','=','detalle_inventarios.fk_id_producto')
                     ->where("detalle_inventarios.fk_id_sede","=",$sede)
                     ->get();
                 if(count($datos)>0){
                    $array=[];
                    foreach ($datos as $key => $value) {
                        if($value->fk_id_sede==$sede){
                            $array[$key]=$value;    
                        }
                        
                    }
                  if(count($array)==0){
                        return array("mensaje"=>"Este producto no tiene existencias asociadasa esta sede",
                            "respuesta"=>false,
                            "datos"=>$array);   
                  }
                    return array("mensaje"=>"Elementos consultados",
                            "respuesta"=>true,
                            "datos"=>$array);   
                 }else{
                    return array("mensaje"=>"Elementos NO encontrados",
                            "respuesta"=>false,
                            "datos"=>$datos);
                 }

        }else{
            
            if($sede!=0){
                $datos=DB::table('productos')
                    ->join('detalle_inventarios','productos.id','=','detalle_inventarios.fk_id_producto')
                    ->where(
                            [  
                                ["fk_id_sede","=",$sede],
                                ["codigo_producto",'=',trim($pro)]
                                
                            ]    
                            )
                    ->orwhere(
                            [  
                                ["fk_id_sede","=",$sede],
                                ["nombre_producto_venta",'LIKE',trim($pro).'%']
                            ]
                        )
                     ->orwhere(
                            [  
                                ["fk_id_sede","=",$sede],
                                ["codigo_producto",'LIKE',trim($pro)]
                            ]
                        )
                     ->orwhere(
                            [  
                                ["fk_id_sede","=",$sede],
                                ["codigo_distribuidor",'LIKE',trim($pro)]
                            ]
                        )
                    ->select("productos.nombre_producto",
                               "productos.descripcion_producto", 
                              "productos.nombre_producto_venta",
                               "productos.codigo_producto",
                               "productos.codigo_distribuidor",
                               "productos.tipo_venta_producto",
                               "productos.unidades_por_caja",
                               "productos.unidades_por_blister",
                               "productos.laboratorio",
                               "productos.impuesto",
                               "productos.precio_compra",
                               "productos.precio_compra_blister",
                               "productos.precio_compra_unidad",
                               "productos.precio_compra_impuesto",
                               "productos.precio_compra_blister_impuesto",
                               "productos.precio_venta",
                               "productos.precio_venta_blister",
                               "productos.precio_mayoreo",
                               "productos.porcentaje_ganancia",
                               "productos.porcentaje_ganancia_blister",
                               "productos.porcentaje_ganancia_unidad",                             
                               "productos.precio_compra_unidad_impuesto",
                               "productos.inventario",
                               "productos.minimo_inventario",
                               "productos.fk_id_proveedor",
                               "productos.fk_id_departamento",
                               "detalle_inventarios.porcentaje_ganancia_sede",
                               "detalle_inventarios.porcentaje_ganancia_blister_sede",
                               "detalle_inventarios.porcentaje_ganancia_sede_unidad",
                               "detalle_inventarios.precio_venta_sede",
                               "detalle_inventarios.precio_venta_blister_sede",
                               "detalle_inventarios.precio_mayoreo_sede",
                               "detalle_inventarios.id",
                               "detalle_inventarios.fk_id_producto",
                               "detalle_inventarios.fk_id_sede",
                               "detalle_inventarios.cantidad_existencias",
                               "detalle_inventarios.cantidad_existencias_blister",
                               "detalle_inventarios.cantidad_existencias_unidades",
                               "detalle_inventarios.minimo_inventario_sede" )
                    ->limit(30)   
                    ->get();
                 
                 if(count($datos)>0){
                    $array=[];
                    foreach ($datos as $key => $value) {
                        if($value->fk_id_sede==$sede){
                            $array[$key]=$value;    
                        }
                        
                    }
                    
                  if(count($array)==0){
                        return array("mensaje"=>"Este producto no tiene existencias asociadasa esta sede",
                            "respuesta"=>false,
                            "datos"=>$array);   
                  }
                    return array("mensaje"=>"Elementos consultados",
                            "respuesta"=>true,
                            "datos"=>$array);   
                 }else{
                    return array("mensaje"=>"Elementos NO encontrados",
                            "respuesta"=>false,
                            "datos"=>$datos);
                 }
            }
            else {
                $datos=DB::table('productos')
                    /*->join('detalle_inventarios','productos.id','=','detalle_inventarios.fk_id_producto')*/
                    ->where(
                        "nombre_producto_venta",'LIKE','%'.trim($pro).'%'
                        )
                    ->orwhere("codigo_producto",'=',trim($pro))
                    ->orwhere("codigo_distribuidor",'=',trim($pro))                     
                    ->limit(30)   
                    ->get();
                 // var_dump($datos);
                 if(count($datos)>0){
                    $array=[];
                    foreach ($datos as $key => $value) {
                        
                            $array[$key]=$value;    
                        
                        
                    }
                    if(count($array)==0){
                          return array("mensaje"=>"Este producto existe",
                              "respuesta"=>false,
                              "datos"=>$array);   
                    }
                    return array("mensaje"=>"Elementos consultados",
                              "respuesta"=>true,
                              "datos"=>$array);   
                 }else{
                    return array("mensaje"=>"Elementos NO encontrados",
                            "respuesta"=>false,
                            "datos"=>$datos);
                 }
            }
                
        }

                
    }
    
    //Busqueda para los productos de cada sede para factura
    public function buscar_producto_por_sede_para_factura($pro,$sede){


        if($pro=="*"){
                $datos=DB::table('productos')
                    ->join('detalle_inventarios','productos.id','=','detalle_inventarios.fk_id_producto')
                     ->where([
                         ["fk_id_sede","=",$sede],
                    
                         ["detalle_inventarios.cantidad_existencias_unidades",">=",1],
                         ["detalle_inventarios.estado_inventario","LIKE","activo"],
                     ])
                    
                    ->get();
                 if(count($datos)>0){
                    $array=[];
                    foreach ($datos as $key => $value) {
                       
                        
                            $array[$key]=$value;    
                        
                        
                    }
                  if(count($array)==0){
                        return array("mensaje"=>"Este producto no tiene existencias asociadasa esta sede",
                            "respuesta"=>false,
                            "datos"=>$array);   
                  }
                    return array("mensaje"=>"Elementos consultados",
                            "respuesta"=>true,
                            "datos"=>$array);   
                 }else{
                    return array("mensaje"=>"Elementos NO encontrados",
                            "respuesta"=>false,
                            "datos"=>$datos);
                 }

        }else{
            $datos=DB::table('productos')
                    ->join('detalle_inventarios','productos.id','=','detalle_inventarios.fk_id_producto')
                   
                    
                     /*->where([
                           
                           ["fk_id_sede","=",$sede],
                           ["nombre_producto_venta",'LIKE','%'. strtoupper(trim($pro)).'%'],
                           ["detalle_inventarios.cantidad_existencias_unidades",">=",1],                           
                           ["detalle_inventarios.estado_inventario","=","activo"],
                     ])*/
                     ->where(
                            [
                              
                                ["codigo_producto",'=',trim($pro)],
                                ["fk_id_sede","=",$sede],
                                ["detalle_inventarios.cantidad_existencias_unidades",">=",1],
                                //["detalle_inventarios.estado_inventario","=","activo"],
                                
                            ]
                        
                        )
                     ->orwhere(
                            [
                              
                                ["codigo_distribuidor",'=',trim($pro)],
                                ["fk_id_sede","=",$sede],
                                ["detalle_inventarios.cantidad_existencias_unidades",">=",1],
                                //["detalle_inventarios.estado_inventario","=","activo"],
                                
                            ]
                        
                        )
                    /* ->orwhere(
                            [
                              
                                ["codigo_producto",'=',trim($pro)],
                                ["fk_id_sede","=",$sede],
                                ["productos.estado_producto","=",1],
                                ["detalle_inventarios.cantidad_existencias_unidades",">=",1]
                            ]
                        
                        )*/
                    /*->orwhere(
                            [
                              
                                ["nombre_producto_venta",'LIKE',"%". strtoupper(trim($pro))."%"],
                                ["fk_id_sede","=",$sede],
                                ["detalle_inventarios.cantidad_existencias_unidades",">=",1]
                                 ["productos.estado_producto","=",1],
                            ]
                        
                        )*/
                    /* ->orwhere(
                            [
                              
                                ["nombre_producto_venta",'LIKE',"%".strtoupper(trim($pro))."%"],
                                ["fk_id_sede","=",$sede],
                                ["productos.estado_producto","=",1],
                                ["detalle_inventarios.cantidad_existencias_unidades",">=",1]
                            ]
                        
                        )*/
                        ->select(

                                "productos.id",
                                "productos.tipo_venta_producto",
                                "productos.unidades_por_caja",
                                "productos.unidades_por_blister",  
                                "productos.tipo_venta_producto",
                                "productos.codigo_producto",    
                                "productos.nombre_producto",
                                "productos.nombre_producto_venta",
                                "productos.inventario",
                                "detalle_inventarios.precio_mayoreo_sede",
                                "detalle_inventarios.precio_venta_blister_sede",
                                "detalle_inventarios.precio_venta_sede",
                                "detalle_inventarios.precio_mayoreo_sede",
                                "detalle_inventarios.promocion",
                                "detalle_inventarios.promo_desde",
                                "detalle_inventarios.promo_hasta",                               
                                "detalle_inventarios.tipo_venta_promo",
                                "detalle_inventarios.minimo_inventario_sede",
                                "detalle_inventarios.promo_hasta",
                                "detalle_inventarios.promo_hasta",
                                "detalle_inventarios.fk_id_sede",
                                "detalle_inventarios.cantidad_existencias",
                                "detalle_inventarios.id as id_producto_inventario",
                                "detalle_inventarios.cantidad_existencias_blister",
                                "detalle_inventarios.cantidad_existencias_unidades",
                                "detalle_inventarios.precio_promo_venta"
                                )     
                    ->limit(10)
                    ->get();
                    
                 if(count($datos)>0){
                    $array=[];
                    
                    foreach ($datos as $key => $value) {
                        
                        if(( $value->fk_id_sede==$sede) && ($value->cantidad_existencias_unidades >= 1)) {
                            //echo $value->estado_producto;
                            $array[$key]=$value;    
                            break;
                        }
                        
                    }

                    
                  if(count($array)==0){
                            return array("mensaje"=>"Este producto no tiene existencias asociadasa esta sede",
                            "respuesta"=>false,
                            "datos"=>$array);   
                  }
                    return array("mensaje"=>"Elementos consultados",
                            "respuesta"=>true,
                            "datos"=>$array);   
                 }else{
                    return array("mensaje"=>"Elementos NO encontrados",
                            "respuesta"=>false,
                            "datos"=>$datos);
                 }
        }

                
    }
    
    //Busqueda para los productos de cada sede
    public function buscar_producto_por_proveedor($pro,$prov,$sed){

       if($pro=="*"){
            $datos=DB::table('productos')
                    ->join("proveedors","productos.fk_id_proveedor","=","proveedors.id")    
                    ->join('detalle_inventarios','productos.id','=','detalle_inventarios.fk_id_producto')
                    ->where(
                            [  
                                
                                ["fk_id_proveedor",'=',$prov],
                                ["fk_id_sede","=",$sed]
                            ]    
                            )
                     ->orwhere(
                            [  
                               
                                ["fk_id_proveedor",'=',$prov],
                                ["fk_id_sede","=",$sed]
                            ]    
                            )   
                     
                    ->limit(30)
                    ->select('productos.codigo_producto',
                            'productos.codigo_distribuidor',
                            'productos.nombre_producto',
                            'productos.nombre_producto_venta',
                            'productos.unidades_por_caja',
                            'productos.precio_compra',
                            'productos.tipo_presentacion',
                            'productos.impuesto',
                            'detalle_inventarios.cantidad_existencias',
                            'detalle_inventarios.cantidad_existencias_unidades',
                            'detalle_inventarios.id',
                            'detalle_inventarios.fk_id_producto',
                            'productos.fk_id_proveedor',
                            'productos.tipo_venta_producto',
                            'proveedors.nombre_proveedor')    
                    ->get();
          
                 if(count($datos)>0){
                    
                    
                  if(count($datos)==0){
                        return array("mensaje"=>"Este producto no tiene existencias asociadasa esta sede",
                            "respuesta"=>false
                            );   
                  }
                    return array("mensaje"=>"Elementos consultados",
                            "respuesta"=>true,
                            "datos"=>$datos);   
                 }else{
                    return array("mensaje"=>"Elementos NO encontrados",
                            "respuesta"=>false,
                            "datos"=>$datos);
                 }
            
       }
       else{
            $datos=DB::table('productos')
                    ->join("proveedors","productos.fk_id_proveedor","=","proveedors.id")    
                    ->join('detalle_inventarios','productos.id','=','detalle_inventarios.fk_id_producto')
                    ->where(
                            [  
                                ["codigo_distribuidor","=",$pro],
                                ["fk_id_proveedor",'=',$prov],
                                ["fk_id_sede","=",$sed]
                            ]    
                            )
                     ->where(
                            [  
                                ["codigo_producto","=",$pro],
                                ["fk_id_proveedor",'=',$prov],
                                ["fk_id_sede","=",$sed]
                            ]    
                            )
                     ->orwhere(
                            [  
                                ["nombre_producto","LIKE","%".$pro."%"],
                                ["fk_id_proveedor",'=',$prov],
                                ["fk_id_sede","=",$sed]
                            ]    
                            )   
                     
                    ->limit(30)
                    ->select('productos.codigo_producto',
                            'productos.codigo_distribuidor',
                            'productos.nombre_producto_venta',
                            'productos.nombre_producto',
                            'productos.tipo_presentacion',
                            'productos.unidades_por_caja',
                            'productos.precio_compra',
                            'productos.impuesto',
                            'detalle_inventarios.cantidad_existencias',
                            'detalle_inventarios.cantidad_existencias_unidades',
                            'detalle_inventarios.id',
                            'detalle_inventarios.fk_id_producto',
                            'productos.fk_id_proveedor',
                            'productos.tipo_venta_producto',
                            'proveedors.nombre_proveedor')    
                    ->get();
          
                 if(count($datos)>0){
                    
                    
                  if(count($datos)==0){
                        return array("mensaje"=>"Este producto no tiene existencias asociadasa esta sede",
                            "respuesta"=>false
                            );   
                  }
                    return array("mensaje"=>"Elementos consultados",
                            "respuesta"=>true,
                            "datos"=>$datos);   
                 }else{
                    return array("mensaje"=>"Elementos NO encontrados",
                            "respuesta"=>false,
                            "datos"=>$datos);
                 }
            
       }
      
            
           
               
            
                
    }

                
    
}
