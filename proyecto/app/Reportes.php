<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use DB;

class Reportes {
    public function reporte_inventario($datos) {
       
        switch($datos->datos->tipo){
    		case "GENERAL":

              
                if($datos->datos->fk_id_categoria==0){
                   if($datos->datos->nombre_producto!=""){
                        $reporte=DB::table('productos')
                        ->join('detalle_inventarios','detalle_inventarios.fk_id_producto','=','productos.id')
                        ->join('sedes','sedes.id','=','detalle_inventarios.fk_id_sede')
                        ->join('proveedors','proveedors.id','=','productos.fk_id_proveedor')        
                                ->where([
                                           ["productos.codigo_producto","LIKE", trim($datos->datos->nombre_producto)]
                                        ])
                                ->orwhere([
                                           ["productos.codigo_distribuidor","LIKE",trim($datos->datos->nombre_producto)]
                                        ])
                        ->groupby('productos.id')
                        ->orderby('total_existencias_unidades',"DESC")        
                        ->select('productos.id',
                                 'proveedors.nombre_proveedor',
                            'productos.nombre_producto',
                            'productos.codigo_producto',
                            'productos.codigo_distribuidor',
                            'productos.precio_compra',
                            'productos.precio_compra_blister',
                            'productos.precio_compra_unidad',
                            'productos.unidades_por_caja',
                            'productos.unidades_por_blister',
                            'productos.precio_venta',
                            'productos.precio_venta_blister',
                            'productos.precio_mayoreo as precio_venta_unidad',
                            'productos.minimo_inventario',
                            'productos.porcentaje_ganancia',
                            'productos.estado_producto',
                             'productos.tipo_venta_producto',   
                             DB::raw("SUM(detalle_inventarios.cantidad_existencias) as total_existencias"),
                             DB::raw("SUM(detalle_inventarios.cantidad_existencias_unidades) as total_existencias_unidades"),
                             DB::raw("SUM(detalle_inventarios.cantidad_existencias_blister) as total_existencias_blister" )
                             
                                )
                        ->get();

                   }
                   else{
                        $reporte=DB::table('productos')
                        ->join('detalle_inventarios','detalle_inventarios.fk_id_producto','=','productos.id')
                        ->join('sedes','sedes.id','=','detalle_inventarios.fk_id_sede')
                        ->join('proveedors','proveedors.id','=','productos.fk_id_proveedor')        
                        ->groupby('productos.id')
                        ->orderby('total_existencias_unidades',"DESC")    
                        ->select('productos.id',
                              'proveedors.nombre_proveedor',   
                            'productos.nombre_producto',
                            'productos.codigo_producto',
                            'productos.codigo_distribuidor',
                            'productos.precio_compra',
                            'productos.precio_compra_blister',
                            'productos.precio_compra_unidad',
                             'productos.unidades_por_caja',
                                'productos.unidades_por_blister',
                            'productos.precio_venta',
                                'productos.precio_venta_blister',
                            'productos.precio_mayoreo as precio_venta_unidad',
                            'productos.tipo_venta_producto',   
                            'productos.minimo_inventario',
                            'productos.porcentaje_ganancia',
                            'productos.estado_producto',
                             DB::raw("SUM(detalle_inventarios.cantidad_existencias) as total_existencias"),
                             DB::raw("SUM(detalle_inventarios.cantidad_existencias_unidades) as total_existencias_unidades"),
                                DB::raw("SUM(detalle_inventarios.cantidad_existencias_blister) as total_existencias_blister" )
                            )
                        ->get();

                   }


                }else{
                    if($datos->datos->fk_id_categoria!="0" && $datos->datos->fk_id_proveedor !="0"){

                        $reporte=DB::table('productos')
                        ->join('detalle_inventarios','detalle_inventarios.fk_id_producto','=','productos.id')
                        ->join('sedes','sedes.id','=','detalle_inventarios.fk_id_sede')
                            ->join('proveedors','proveedors.id','=','productos.fk_id_proveedor')        
                        ->groupby('productos.id')
                        ->orderby('total_existencias_unidades',"DESC")    
                        ->where([["productos.fk_id_departamento",'=',$datos->datos->fk_id_categoria],["productos.fk_id_proveedor",'=',$datos->datos->fk_id_proveedor]])
                        ->select('productos.id',
                             'proveedors.nombre_proveedor',
                            'productos.nombre_producto',
                            'productos.codigo_producto',
                            'productos.codigo_distribuidor',
                            'productos.precio_compra',
                            'productos.precio_compra_blister',
                            'productos.precio_compra_unidad',
                             'productos.unidades_por_caja',
                                'productos.unidades_por_blister',
                            'productos.tipo_venta_producto',   
                            'productos.precio_venta',
                                'productos.precio_venta_blister',
                            'productos.precio_mayoreo as precio_venta_unidad',
                            'productos.minimo_inventario',
                            'productos.porcentaje_ganancia',
                            'productos.estado_producto',
                             DB::raw("SUM(detalle_inventarios.cantidad_existencias) as total_existencias"),
                             DB::raw("SUM(detalle_inventarios.cantidad_existencias_unidades) as total_existencias_unidades"),
                                DB::raw("SUM(detalle_inventarios.cantidad_existencias_blister) as total_existencias_blister" )
                            )
                        ->get();
                    }else{
                        $reporte=DB::table('productos')
                        ->join('detalle_inventarios','detalle_inventarios.fk_id_producto','=','productos.id')
                        ->join('sedes','sedes.id','=','detalle_inventarios.fk_id_sede')
                            ->join('proveedors','proveedors.id','=','productos.fk_id_proveedor')        
                        ->groupby('productos.id')
                        ->orderby('total_existencias_unidades',"DESC")    
                        ->where("productos.fk_id_departamento",'=',$datos->datos->fk_id_categoria)
                        ->select('productos.id',
                             'proveedors.nombre_proveedor',
                            'productos.nombre_producto',
                            'productos.codigo_producto',
                            'productos.codigo_distribuidor',
                            'productos.precio_compra',
                            'productos.precio_compra_blister',
                            'productos.precio_compra_unidad',
                            'productos.unidades_por_caja',
                                'productos.unidades_por_blister',
                            'productos.tipo_venta_producto',   
                            'productos.precio_venta',
                                'productos.precio_venta_blister',
                            'productos.precio_mayoreo as precio_venta_unidad',
                            'productos.minimo_inventario',
                            'productos.porcentaje_ganancia',
                            'productos.estado_producto',
                             DB::raw("SUM(detalle_inventarios.cantidad_existencias) as total_existencias"),
                             DB::raw("SUM(detalle_inventarios.cantidad_existencias_unidades) as total_existencias_unidades"),
                                DB::raw("SUM(detalle_inventarios.cantidad_existencias_blister) as total_existencias_blister" )
                             )
                        ->get();
                    }

                }
                        $arr_repo=[];
                        $i=0;
                        if(count($reporte)>0){
                             foreach ($reporte as $key => $value) {
                            $arr_repo[$i]=(array)$value;
                                $i++;
                            }


                            return ["mensaje"=>"REPORTE INVENTARIO GENERADO",
                                "respuesta"=>true,
                                "datos"=>$arr_repo];   
                        }else{
                            return ["mensaje"=>"REPORTE INVENTARIO NO SE HA  GENERADO NO HAY DATOS QUE COINCIDAN",
                                "respuesta"=>false];   
                        }
                        
    			break;
    		case "SEDE":
                
                    $arr_where=array();
                    $arr_where_2=array();
                    $i=0;
                  //  var_dump($datos->datos->fk_id_categoria);
                    //$arr_where[$i]=["detalle_inventarios.estado_producto_sede",'=',"1"];
                    $i++;
                    if((int)$datos->datos->fk_id_categoria!==0){
                        $arr_where[$i]=["productos.fk_id_departamento",'=',$datos->datos->fk_id_categoria];
                         $arr_where_2[$i]=["productos.fk_id_departamento",'=',$datos->datos->fk_id_categoria];
                                $i++;
                    }
                    
                    if($datos->datos->nombre_producto!=""){
                         $arr_where[$i]=["productos.codigo_producto","LIKE",trim($datos->datos->nombre_producto)];
                         $arr_where_2[$i]=["productos.codigo_distribuidor","LIKE",trim($datos->datos->nombre_producto)];       
                         $i++;
                         
                    }
                    
                     if($datos->datos->sede!=0){
                          $arr_where[$i]=["sedes.id",'=',$datos->datos->sede];
                          $arr_where_2[$i]=["sedes.id",'=',$datos->datos->sede];
                                $i++;
                     }
                     //var_dump($datos->datos->fk_id_proveedor);
                       if((int)$datos->datos->fk_id_proveedor!==0){
                          $arr_where[$i]=["proveedors.id",'=',$datos->datos->fk_id_proveedor];
                          $arr_where_2[$i]=["proveedors.id",'=',$datos->datos->fk_id_proveedor];
                               
                     }
                     //echo count($arr_where);
                    
                    if(count($arr_where)>0){
                          
                            $reporte=DB::table('productos')
                            ->join('detalle_inventarios','detalle_inventarios.fk_id_producto','=','productos.id')
                            ->join('sedes','sedes.id','=','detalle_inventarios.fk_id_sede')
                            ->join('proveedors',"proveedors.id","=",'productos.fk_id_proveedor')         
                            ->where($arr_where)
                            ->orwhere($arr_where_2)
                            ->orderby('total_existencias_unidades',"DESC")            
                            ->select('productos.id',
                                    'proveedors.nombre_proveedor',
                                    'productos.nombre_producto',
                                    'productos.codigo_producto',
                                    'productos.codigo_distribuidor',
                                    'productos.precio_compra',
                                    'productos.precio_compra_blister',
                                    'productos.precio_compra_unidad',
                                    'productos.precio_venta',
                                    'productos.minimo_inventario',
                                    'productos.tipo_venta_producto',   
                                    'productos.porcentaje_ganancia',
                                    'productos.unidades_por_caja',
                                    'productos.unidades_por_blister',
                                    'detalle_inventarios.precio_venta_sede',
                                    'detalle_inventarios.precio_venta_blister_sede',
                                    'detalle_inventarios.precio_mayoreo_sede',
                                    'detalle_inventarios.minimo_inventario_sede',
                                    'detalle_inventarios.porcentaje_ganancia_sede',
                                    'productos.estado_producto',
                                    'detalle_inventarios.cantidad_existencias AS total_existencias',
                                    'detalle_inventarios.cantidad_existencias_unidades AS total_existencias_unidades',
                                    'detalle_inventarios.cantidad_existencias_blister AS total_existencias_blister',
                                    'sedes.nombre_sede',
                                    'detalle_inventarios.id as id_detalle_inventario')
                            ->get();


                     }
                     else{
                            
                     }
                    
                        
                       $arr_repo=[];
                        $i=0;
                        foreach ($reporte as $key => $value) {
                            $arr_repo[$i]=(array)$value;
                            $i++;
                        }
                if(count($arr_repo)>0){
                    return ["mensaje"=>"REPORTE INVENTARIO ",
                            "respuesta"=>true,
                            "datos"=>$arr_repo];    
                }else{
                    return ["mensaje"=>"NO EXISTEN PRODCUTOS ACTIVOS PARA ESTA SEDE ",
                            "respuesta"=>false,
                            "datos"=>$arr_repo];    
                }
                
    			
                    
    		    break;	
    		 default:
    		 	$reporte=[];
    		 	return ["mensaje"=>"Por favor selecciona un tipo de reporte ","respuesta"=>false,"datos"=>$reporte];
    		 	break;   
    	}
    }
    
    public function reporte_bajo_inventario($datos) {
        switch($datos->datos->tipo){
    		case "GENERAL":
    		   $reporte=DB::table('productos')
    				->join('detalle_inventarios','detalle_inventarios.fk_id_producto','=','productos.id')
    				->join('sedes','sedes.id','=','detalle_inventarios.fk_id_sede')
                        ->join('proveedors','proveedors.id','=','productos.fk_id_proveedor')   
                        ->join('departamentos','departamentos.id','=','productos.fk_id_departamento')
                        ->where('detalle_inventarios.estado_inventario','=',"agotado")
                        ->orwhere([
                                    ["detalle_inventarios.cantidad_existencias_unidades",'<=','detalle_inventarios.minimo_inventario_sede'],
                                    ['detalle_inventarios.estado_inventario','<>',"inactivo"]
                                   ])
                        ->select('productos.id',
                            'productos.nombre_producto',
                            'productos.codigo_producto',
                            'productos.codigo_distribuidor',
                            'precio_compra',
                            'productos.precio_venta',
                            'productos.precio_mayoreo',
                            'productos.tipo_presentacion',   
                            'sedes.nombre_sede',
                            'sedes.codigo_sede',
                            'productos.minimo_inventario',
                            'detalle_inventarios.cantidad_existencias',
                             'detalle_inventarios.cantidad_existencias_unidades',
                            'departamentos.nombre_departamento',
                             'proveedors.nombre_proveedor'   )
                        ->get();
                        $arr_repo=[];
                        $i=0;
                        foreach ($reporte as $key => $value) {
                            $arr_repo[$i]=(array)$value;
                            $i++;
                        }
    			return ["mensaje"=>"REPORTE MINIMO INVENTARIO GENERADO",
                            "respuesta"=>true,
                            "datos"=>$arr_repo];
    			break;
    		case "SEDE":
    			$reporte=DB::table('productos')
                                    ->join('proveedors','proveedors.id','=','productos.fk_id_proveedor')   
                                    ->join('detalle_inventarios','detalle_inventarios.fk_id_producto','=','productos.id')
                                    ->join('sedes','sedes.id','=','detalle_inventarios.fk_id_sede')
                                    ->join('departamentos','departamentos.id','=','productos.fk_id_departamento')
        			    ->where([
        						
                                                ["sedes.id",'=',$datos->datos->sedes],
                                                ["detalle_inventarios.estado_inventario","=","agotado"]	

        				    ])
                                    ->orwhere([
        						
                                                ["sedes.id",'=',$datos->datos->sedes],
                                                ["detalle_inventarios.cantidad_existencias_unidades",'<=','detalle_inventarios.minimo_inventario_sede'],
                                                ['detalle_inventarios.estado_inventario','<>',"inactivo"]	
                                              ])        
                        ->select('detalle_inventarios.id',
                            'productos.codigo_producto',
                            'productos.codigo_distribuidor',
                            'productos.nombre_producto',
                            'precio_compra',
                            'productos.precio_venta',
                            'productos.precio_mayoreo',
                            'productos.tipo_presentacion',
                            'sedes.nombre_sede',
                            'sedes.codigo_sede',
                            'productos.minimo_inventario',
                            'detalle_inventarios.cantidad_existencias',
                            'detalle_inventarios.cantidad_existencias_blister',
                            'detalle_inventarios.cantidad_existencias_unidades',
                            'departamentos.nombre_departamento',
                            'proveedors.nombre_proveedor')
    				->get();

                    if(count($reporte)>0){
                        $arr_repo=[];
                        $i=0;
                        foreach ($reporte as $key => $value) {
                            $arr_repo[$i]=(array)$value;
                            $i++;
                        }
                        return ["mensaje"=>"REPORTE MINIMO INVENTARIO POR SEDES GENERADO",
                            "respuesta"=>true,
                            "datos"=>$arr_repo];
                    }else{
                        return ["mensaje"=>"NO SE HA GENERAR REPORTE MINIMO INVENTARIO POR SEDES NO HAY DATOS QUE COINCIDAN","respuesta"=>false,"datos"=>$reporte];
                    }
    		
    		    break;	
    		  
             
             default:
                return ["mensaje"=>"Por favor selecciona un tipo de reporte ","respuesta"=>true];
                break;      
    	}
    }
    
    public function reporte_movimientos_inventario($datos) {
           switch($datos->datos->tipo){
    		case "GENERAL":
                   
                if(count($datos->datos->filtro)>0){
                    
                    $reporte=DB::table('productos')
                    ->join('detalle_inventarios','detalle_inventarios.fk_id_producto','=','productos.id')
                    ->join('movimientos_inventario','movimientos_inventario.fk_id_det_inventario','=','detalle_inventarios.id')
                    ->join('users','users.id','=','movimientos_inventario.fk_id_usuario')
                    ->join('departamentos','departamentos.id','=','productos.fk_id_departamento')
                    ->join('sedes','sedes.id','=','detalle_inventarios.fk_id_sede')
                    ->where($datos->datos->filtro)
                    //->orWhere($datos->datos->filtro2)
                    ->select('productos.id','productos.nombre_producto','productos.minimo_inventario','detalle_inventarios.cantidad_existencias',
                        'movimientos_inventario.habia',
                        'movimientos_inventario.cantidad',
                        'movimientos_inventario.quedan',
                        'movimientos_inventario.tipo',
                        'movimientos_inventario.observaciones',
                        'movimientos_inventario.created_at',
                        'movimientos_inventario.descripcion',
                        'users.nombres',
                        'departamentos.nombre_departamento',
                        'sedes.nombre_sede')    
                    ->orderBy("movimientos_inventario.created_at","DESC")
                    ->get();
                
                }else{
                    $reporte=DB::table('productos')
                    ->join('detalle_inventarios','detalle_inventarios.fk_id_producto','=','productos.id')
                    ->join('movimientos_inventario','movimientos_inventario.fk_id_det_inventario','=','detalle_inventarios.id')
                    ->join('users','users.id','=','movimientos_inventario.fk_id_usuario')
                    ->join('departamentos','departamentos.id','=','productos.fk_id_departamento')
                    ->join('sedes','sedes.id','=','detalle_inventarios.fk_id_sede')
                    ->select('productos.id','productos.nombre_producto','productos.minimo_inventario','detalle_inventarios.cantidad_existencias',
                        'movimientos_inventario.habia',
                        'movimientos_inventario.cantidad',
                        'movimientos_inventario.quedan',
                        'movimientos_inventario.tipo',
                            'movimientos_inventario.observaciones',
                        'movimientos_inventario.created_at',
                            'movimientos_inventario.descripcion',
                        'users.nombres',
                        'departamentos.nombre_departamento',
                        'sedes.nombre_sede',
                        'sedes.codigo_sede')    
                    ->orderBy("movimientos_inventario.created_at","DESC")
                    ->get();
                    
                }   
                $arr_repo=[];
                        $i=0;
                        foreach ($reporte as $key => $value) {
                            $arr_repo[$i]=(array)$value;
                            $i++;
                        }
                if(count($arr_repo)>0){
                    return ["mensaje"=>"REPORTE MOVIMIENTOS INVENTARIO GENERADO","respuesta"=>true,"datos"=>$arr_repo];
                }else{
                    return ["mensaje"=>"NO HAY MOVIMIENTOS REGISTRADOS","respuesta"=>false,"datos"=>$arr_repo];    
                }
    			
    			break;
    		case "SEDE":
                      
                if(count($datos->datos->filtro)>0){
    				$reporte=DB::table('productos')
    				->join('detalle_inventarios','detalle_inventarios.fk_id_producto','=','productos.id')
    				->join('movimientos_inventario','movimientos_inventario.fk_id_det_inventario','=','detalle_inventarios.id')
    				->join('sedes','sedes.id','=','detalle_inventarios.fk_id_sede')
                    ->join('users','users.id','=','movimientos_inventario.fk_id_usuario')
                    ->join('departamentos','departamentos.id','=','productos.fk_id_departamento')
                    
    				->where($datos->datos->filtro)   
                    //->orWhere($datos->datos->filtro2)
                    ->select('productos.id','productos.nombre_producto','productos.minimo_inventario','detalle_inventarios.cantidad_existencias',
                        'movimientos_inventario.habia',
                        'movimientos_inventario.cantidad',
                        'movimientos_inventario.quedan',
                        'sedes.id',
                        'sedes.nombre_sede',
                        'movimientos_inventario.tipo',
                        'movimientos_inventario.observaciones',
                        'movimientos_inventario.descripcion',
                        'movimientos_inventario.created_at',
                        'users.nombres',
                        'sedes.codigo_sede',
                        'departamentos.nombre_departamento')  
                        ->orderBy("movimientos_inventario.created_at","DESC") 				
    				->get();
    		  }else{
                        $reporte=DB::table('productos')
                    ->join('detalle_inventarios','detalle_inventarios.fk_id_producto','=','productos.id')
                    ->join('movimientos_inventario','movimientos_inventario.fk_id_det_inventario','=','detalle_inventarios.id')
                    ->join('sedes','sedes.id','=','detalle_inventarios.fk_id_sede')
                    ->join('users','users.id','=','movimientos_inventario.fk_id_usuario')
                    ->join('departamentos','departamentos.id','=','productos.fk_id_departamento')
                    
                    ->select('productos.id','productos.nombre_producto','productos.minimo_inventario','detalle_inventarios.cantidad_existencias',
                        'movimientos_inventario.habia',
                        'movimientos_inventario.cantidad',
                        'movimientos_inventario.quedan',
                        'sedes.id',
                        'sedes.nombre_sede',
                        'sedes.codigo_sede',
                        'movimientos_inventario.tipo',
                        'movimientos_inventario.descripcion',
                        'movimientos_inventario.created_at',
                        'users.nombres',
                        'departamentos.nombre_departamento')   
                        ->orderBy("movimientos_inventario.created_at","DESC")              
                    ->get();

                        
              }
                         $arr_repo=[];
                        $i=0;
                        foreach ($reporte as $key => $value) {
                            $arr_repo[$i]=(array)$value;
                            $i++;
                        }
    		    if(count($arr_repo)>0){
                    return ["mensaje"=>"REPORTE MOVIMIENTOS INVENTARIO GENERADO","respuesta"=>true,"datos"=>$arr_repo];
                }else{
                    return ["mensaje"=>"NO HAY MOVIMIENTOS REGISTRADOS","respuesta"=>false,"datos"=>$arr_repo];    
                }
              
    		    break;	
    		 default:
    		 	return ["mensaje"=>"Por favor selecciona un tipo de reporte ","respuesta"=>true];
    		 	break;   
    	}    	 
    }
    
    public function reporte_saldos($datos) {
           switch($datos->datos->tipo){
    		case "GENERAL":
    			$reporte=DB::table('clientes')
    					->join('creditos','creditos.fk_id_cliente','=','clientes.id')
    					->join('detalle_credito_abonos','detalle_credito_abonos.fk_id_credito','=','creditos.id')	
    					->where($datos->datos->filtro)
                        ->select('clientes.id',
                                'clientes.nombre_cliente',
                                'clientes.celular',
                                'clientes.direccion',
                                'clientes.limite_de_credito',
                                'clientes.documento',
                                'clientes.email',
                                'detalle_credito_abonos.observacion',
                                'detalle_credito_abonos.abono',
                                'creditos.valor_credito',
                                'creditos.estado_credito',
                                'creditos.valor_actual_credito',
                                'detalle_credito_abonos.fecha_abono')
    					->get();
                    
                    $arr_repo=[];
                        $i=0;
                        foreach ($reporte as $key => $value) {
                            $arr_repo[$i]=(array)$value;
                            $i++;
                        }
                    return ["mensaje"=>"REPORTE GENERADO","respuesta"=>true,"datos"=>$arr_repo];
    			break;
    		case "SEDE":
    			$reporte=DB::table('clientes')
    					->join('creditos','creditos.fk_id_cliente','=','clientes.id')
    					->join('detalle_credito_abonos','detalle_credito_abonos.fk_id_credito','=','creditos.id')
                        ->join('facturas','creditos.fk_id_factura','=','facturas.id')
                        ->join('sedes','sedes.id','=','facturas.fk_id_sede')

    					->where($datos->datos->filtro)
                        ->select('clientes.id',
                                'clientes.nombre_cliente',
                                'clientes.celular',
                                'clientes.direccion',
                                'clientes.limite_de_credito',
                                'clientes.documento',
                                'clientes.email',
                                'detalle_credito_abonos.observacion',
                                'detalle_credito_abonos.abono',
                                'creditos.valor_credito',
                                'creditos.estado_credito',
                                'creditos.valor_actual_credito',
                                'sedes.id as id_sede',
                                'sedes.nombre_sede',
                                'detalle_credito_abonos.fecha_abono')
    					->get();
                    $arr_repo=[];
                        $i=0;
                        foreach ($reporte as $key => $value) {
                            $arr_repo[$i]=(array)$value;
                            $i++;
                        }
    		return ["mensaje"=>"REPORTE GENERADO","respuesta"=>true,"datos"=>$arr_repo];
    		    break;	
    		 default:
    		 	return ["mensaje"=>"Por favor selecciona un tipo de reporte ","respuesta"=>true];
    		 	break;   
    	} 
    }   
    
    public function reporte_ventas_por_periodo($datos) {
      
        switch($datos->datos->tipo){
    		case "GENERAL":
            
    				$reporte=DB::table('facturas')	
    					->join('detalle_facturas','detalle_facturas.fk_id_factura','=','facturas.id')
                                        ->join('detalle_inventarios','detalle_facturas.fk_id_producto','=','detalle_inventarios.id')
                                        ->join('productos','productos.id','=','detalle_inventarios.fk_id_producto')
                                        ->join("users","users.id","=","facturas.fk_id_vendedor")
                                        ->join('departamentos','departamentos.id','=','productos.fk_id_departamento')
 					->where($datos->datos->filtro)
                                        ->select('facturas.id',
                                              'facturas.numero_factura',
                                              'facturas.estado_factura',
                                              'facturas.registro_factura',
                                              'facturas.observaciones',
                                               'detalle_facturas.tipo_venta', 
                                              'productos.codigo_producto',
                                              'productos.nombre_producto',
                                              'productos.impuesto',
                                               'detalle_facturas.valor_item',
                                               
                                               DB::raw('CONCAT(users.nombres," ",users.apellidos) nombre_usuario'),  
                                                
                                               DB::raw('SUM(detalle_facturas.cantidad_producto) * detalle_facturas.valor_item as precio_venta' ),
                                              'departamentos.nombre_departamento',
                                               DB::raw("SUM(detalle_facturas.cantidad_producto) as cantidad_vendida")
                                                )
                                        ->groupby(DB::raw('productos.id,detalle_facturas.tipo_venta,detalle_facturas.valor_item,facturas.registro_factura'))
                                        ->orderBy("facturas.registro_factura","DESC")
                                                    ->get();

                        if(count($reporte)>0){
                            if($reporte[0]->numero_factura!=null){
                                $arr_repo=[];
                                $i=0;
                                foreach ($reporte as $key => $value) {
                                    $arr_repo[$i]=(array)$value;
                                    $i++;
                                }
                                return ["mensaje"=>"REPORTE GENERADO","respuesta"=>true,"datos"=>$arr_repo];                
                            }else{
                                 return ["mensaje"=>"REPORTE NO SE HA PODIDO GENERRA NO HAY DATOS QUE COINCIDAN","respuesta"=>false];   
                            }
                            
                        }else{
                            return ["mensaje"=>"REPORTE NO SE HA PODIDO GENERRA NO HAY DATOS QUE COINCIDAN","respuesta"=>false];
                        }
    			
    			break;
    		case "SEDE":
                        
    			$reporte=DB::table('facturas')	
    					->join('detalle_facturas','detalle_facturas.fk_id_factura','=','facturas.id')
                        ->join('detalle_inventarios','detalle_facturas.fk_id_producto','=','detalle_inventarios.id')
                        ->join('productos','productos.id','=','detalle_inventarios.fk_id_producto')
                        ->join("users","users.id","=","facturas.fk_id_vendedor")    
                        

                        ->join('departamentos','departamentos.id','=','productos.fk_id_departamento')
                        
    					->join('sedes','sedes.id','=','facturas.fk_id_sede')
    					->where($datos->datos->filtro)
                        
                        ->select('facturas.id',
                                  'facturas.numero_factura',
                                  'facturas.estado_factura',
                                  'facturas.registro_factura',
                                  'facturas.observaciones',
                                  'sedes.nombre_sede',
                                  'sedes.codigo_sede',
                                  'sedes.id',
                                   'detalle_inventarios.precio_venta_sede',
                                   'detalle_inventarios.precio_venta_blister_sede',
                                   'detalle_inventarios.precio_mayoreo_sede',
                                   'detalle_inventarios.porcentaje_ganancia_sede',
                                   'detalle_inventarios.porcentaje_ganancia_blister_sede',
                                   'detalle_inventarios.porcentaje_ganancia_sede_unidad',

                                  DB::raw('CONCAT(users.nombres," ",users.apellidos) nombre_usuario'),  
                                  'productos.codigo_producto',
                                  'productos.nombre_producto',
                                  'productos.precio_compra',
                                  'productos.precio_compra_blister',
                                  'productos.precio_compra_unidad',
                                  'detalle_facturas.tipo_venta', 
                                  'productos.impuesto',
                                    'detalle_facturas.valor_item',
                                   //DB::raw('SUM(detalle_facturas.cantidad_producto) * detalle_facturas.valor_item as precio_venta' ),
                                  'departamentos.nombre_departamento',
                                  

                                   DB::raw("SUM(detalle_facturas.cantidad_producto) as cantidad_vendida")
                                    )
                                ->groupby("productos.id",'detalle_facturas.tipo_venta','detalle_facturas.valor_item','facturas.registro_factura')
                                ->orderBy("facturas.registro_factura","DESC")
                                ->get();
                        
                        if(count($reporte)>0){
                            if($reporte[0]->numero_factura!=null){
                                $arr_repo=[];
                                $i=0;
                                foreach ($reporte as $key => $value) {
                                    $arr_repo[$i]=(array)$value;
                                    $i++;
                                }
                                return ["mensaje"=>"REPORTE GENERADO","respuesta"=>true,"datos"=>$arr_repo];    
                            }else{
                                return ["mensaje"=>"REPORTE NO SE HA PODIDO GENERAR NO HAY DATOS QUE COINCIDAN","respuesta"=>false];    
                            }
                            
                        }else{
                            return ["mensaje"=>"REPORTE NO SE HA PODIDO GENERAR NO HAY DATOS QUE COINCIDAN","respuesta"=>false];
                        }
    			
    		    break;	
    		 default:
                     //AQUI HACERLO PARA TODAS LAS SEDES
    		 	return ["mensaje"=>"Por favor selecciona un tipo de reporte ","respuesta"=>false];
    		 	break;   
    	}
    }
    
    public function reporte_corte_diario($datos) {
        switch($datos->datos->tipo){
    		case "GENERAL":
    		    $entradas_en_efectivo;
                $pagos_de_contado;
                $ventas_por_departamento;
                $pago_de_clientes;
                $pago_a_proveedores;
                $entrada_inicial_caja;

                $entradas_en_efectivo=DB::table('entrada_contables')
                                      ->join('detalle_entrada_contables','detalle_entrada_contables.fk_id_entrada_contable','=','entrada_contables.id')
                                      ->join('sedes','detalle_entrada_contables.fk_id_sede','=','sedes.id')
                                      ->where($datos->datos->filtro)
                                      ->where([
                                                ["entrada_contables.nombre_entrada","<>","VentaDiaria"],
                                                ["entrada_contables.nombre_entrada","<>","CajaInicial"]
                                          ])
                                      
                                      ->select(DB::raw("SUM(valor_entrada) AS total_entradas_corte"))
                                      ->get();  

                $pagos_de_contado_facturas=DB::table('facturas')
                                    ->join('detalle_facturas','detalle_facturas.fk_id_factura','=','facturas.id')
                                    ->where([
                                                ['estado_factura','=','paga'],
                                                ['registro_factura','>=',explode(" ", $datos->hora_cliente)[0]."  00:00:00"],
                                                ['registro_factura','<=',$datos->datos->fecha." ".explode(" ", $datos->hora_cliente)[1]]
                                            ])
                                     
                                    
                                    ->select(
                                             DB::raw('SUM(detalle_facturas.valor_item * detalle_facturas.cantidad_producto) as total_factura')  )
                                    ->get();
               
                $ventas_por_departamento=DB::table('facturas')
                                        ->join('detalle_facturas','detalle_facturas.fk_id_factura','=','facturas.id')
                                        ->join('detalle_inventarios','detalle_facturas.fk_id_producto','=','detalle_inventarios.id')
                                        ->join('productos','detalle_inventarios.fk_id_producto','=','productos.id')
                                        ->join('departamentos','productos.fk_id_departamento','=','departamentos.id')                    
                                        ->where([
                                                ['estado_factura','LIKE','paga'],
                                                ['registro_factura','>=',$datos->datos->fecha." 00:00:00"],
                                                ['registro_factura','<=',$datos->datos->fecha." 23:59:59"]
                                            ])
                                        ->groupby('departamentos.id')
                                        ->select('facturas.id',
                                             'facturas.numero_factura',
                                             'facturas.estado_factura',
                                             'facturas.registro_factura',
                                             'productos.nombre_producto',
                                             'departamentos.nombre_departamento',
                                             DB::raw('SUM(detalle_facturas.valor_item * detalle_facturas.cantidad_producto) as total_venta_por_departamento'))
                                             /*DB::raw('(detalle_facturas.valor_item * detalle_facturas.cantidad_producto) as total_venta_por_departamento'))*/
                                        ->get(); 

                $pago_de_clientes=DB::table('creditos')
                                   ->join('detalle_credito_abonos','detalle_credito_abonos.fk_id_credito','=','creditos.id')
                                   ->where([
                                                ['estado_credito','=','pendiente'],
                                                ['detalle_credito_abonos.fecha_abono','>=',$datos->datos->fecha."  00:00:00"],
                                                ['detalle_credito_abonos.fecha_abono','<=',$datos->datos->fecha." 23:59:59"]
                                            ])
                                   ->select(DB::raw('SUM(detalle_credito_abonos.abono) AS total_abonos'))
                                   ->get();
                $salidas_dinero_caja=DB::table('salida_contables')
                                    ->join('detalle_salida_contables','detalle_salida_contables.fk_id_salida_contable','=','salida_contables.id')
                                    ->where([
                                            ["detalle_salida_contables.fecha_registro_salida",'>=',
                                                $datos->datos->fecha."  00:00:00"],
                                            ['detalle_salida_contables.fecha_registro_salida','<=',$datos->datos->fecha." 23:59:59"]   
                                            ])
                                           ->where("salida_contables.nombre_salida","=","pago a proveedor") 
                                    ->select(DB::raw('SUM(detalle_salida_contables.valor_salida) AS total_salida'))
                                    ->get();                        
                $dinero_inicial_caja=DB::table("entrada_contables")
                                        ->join("detalle_entrada_contables","detalle_entrada_contables.fk_id_entrada_contable","=","entrada_contables.id")
                                        ->join("sedes","sedes.id","=","detalle_entrada_contables.fk_id_sede")
                                        ->where($datos->datos->filtro)
                                        ->where("entrada_contables.nombre_entrada","=","CajaInicial")
                                        ->groupby("detalle_entrada_contables.fk_id_entrada_contable")
                                        ->select(DB::raw("SUM(detalle_entrada_contables.valor_entrada) as total_entrada_inicial_caja"))
                                        ->get();

                $ganancias_venta_dia=DB::table('facturas')
                                        ->join('detalle_facturas','detalle_facturas.fk_id_factura','=','facturas.id')
                                        ->join('detalle_inventarios','detalle_facturas.fk_id_producto','=','detalle_inventarios.id')
                                        ->join('productos','detalle_inventarios.fk_id_producto','=','productos.id')
                                        ->join('departamentos','productos.fk_id_departamento','=','departamentos.id')                    
                                        ->where([
                                                ['estado_factura','LIKE','paga'],
                                                ['registro_factura','>=',$datos->datos->fecha." 00:00:00"],
                                                ['registro_factura','<=',$datos->datos->fecha." 23:59:59"]
                                            ])
                                        
                                       ->select('facturas.id',
                                             'facturas.numero_factura',
                                             'facturas.estado_factura',
                                             'facturas.registro_factura',
                                             "detalle_facturas.tipo_venta",
                                             'productos.nombre_producto',
                                             'productos.codigo_producto',
                                             'productos.precio_compra',
                                             'productos.precio_compra_blister',
                                             'productos.precio_compra_unidad',
                                             'productos.impuesto',
                                             "detalle_inventarios.precio_venta_sede",
                                             "detalle_inventarios.precio_venta_blister_sede",
                                             "detalle_inventarios.precio_mayoreo_sede",
                                             'departamentos.nombre_departamento',
                                             'detalle_facturas.valor_item',
                                             'detalle_facturas.cantidad_producto')
                                             
                                            
                                        ->get(); 

    			return ["mensaje"=>"REPORTE CORTE DIARIO GENERADO","respuesta"=>true,
                                                    "entradas_en_efectivo"=>$entradas_en_efectivo,
                                                    "pagos_de_contado"=>$pagos_de_contado_facturas,
                                                    "ventas_por_departamento"=>$ventas_por_departamento,
                                                    "pago_de_clientes"=>$pago_de_clientes,
                                                    "salidas_dinero_caja"=>$salidas_dinero_caja,
                                                     "dinero_caja_inicial"=>$dinero_inicial_caja,
                                                    "ganancias_venta_dia"=>$ganancias_venta_dia];
    			break;
    		case "SEDE":
                    
                $entradas_en_efectivo;
                $pagos_de_contado;
                $ventas_por_departamento;
                $pago_de_clientes;
                $pago_a_proveedores;
                $entrada_inicial_caja;

                $entradas_en_efectivo=DB::table('entrada_contables')
                                      ->join('detalle_entrada_contables','detalle_entrada_contables.fk_id_entrada_contable','=','entrada_contables.id')
                                      ->join('sedes','detalle_entrada_contables.fk_id_sede','=','sedes.id')
                                      ->where($datos->datos->filtro)
                                      ->where([
                                                ["entrada_contables.nombre_entrada","<>","VentaDiaria"],
                                                ["entrada_contables.nombre_entrada","<>","CajaInicial"]
                                          ])
                                      ->where("sedes.id","=",$datos->datos->sede)
                                      ->select(DB::raw("SUM(valor_entrada) AS total_entradas_corte"))
                                      ->get();  

                $pagos_de_contado_facturas=DB::table('facturas')
                                    ->join('detalle_facturas','detalle_facturas.fk_id_factura','=','facturas.id')
                                    ->where([
                                                ['estado_factura','=','paga'],
                                                ['registro_factura','>=',$datos->datos->fecha."  00:00:00"],
                                                ['registro_factura','<=',$datos->hora_cliente]
                                            ])
                                     ->where("facturas.fk_id_sede","=",$datos->datos->sede)
                                    
                                    ->select(
                                             DB::raw('SUM(detalle_facturas.valor_item * detalle_facturas.cantidad_producto) as total_factura')  )
                                    ->get();
                 $credito_facturas=DB::table('facturas')
                                    ->join('detalle_facturas','detalle_facturas.fk_id_factura','=','facturas.id')
                                    ->where([
                                                ['estado_factura','=','endeuda'],
                                                ['registro_factura','>=',$datos->datos->fecha."  00:00:00"],
                                                ['registro_factura','<=',$datos->hora_cliente]
                                            ])
                                     ->where("facturas.fk_id_sede","=",$datos->datos->sede)
                                    
                                    ->select(
                                             DB::raw('SUM(detalle_facturas.valor_item * detalle_facturas.cantidad_producto) as total_factura_credito')  )
                                    ->get();                   
                
                $ventas_por_departamento=DB::table('facturas')
                                        ->join('detalle_facturas','detalle_facturas.fk_id_factura','=','facturas.id')
                                        ->join('detalle_inventarios','detalle_facturas.fk_id_producto','=','detalle_inventarios.id')
                                        ->join('productos','detalle_inventarios.fk_id_producto','=','productos.id')
                                        ->join('departamentos','productos.fk_id_departamento','=','departamentos.id')                    
                                        ->where([
                                                ["facturas.fk_id_sede","=",$datos->datos->sede],
                                                ['estado_factura','=','paga'],
                                                ['registro_factura','>=',$datos->datos->fecha."  00:00:00"],
                                                ['registro_factura','<=',$datos->hora_cliente]
                                            ])
                                            
                                        ->groupby('departamentos.id')
                                        ->select('facturas.id',
                                             'facturas.numero_factura',
                                             'facturas.estado_factura',
                                             'facturas.registro_factura',
                                             'productos.nombre_producto',
                                             'departamentos.nombre_departamento',
                                             DB::raw('SUM(detalle_facturas.valor_item * detalle_facturas.cantidad_producto) as total_venta_por_departamento'))
                                        ->get();   
                $pago_de_clientes=DB::table('creditos')
                                   ->join('detalle_credito_abonos','detalle_credito_abonos.fk_id_credito','=','creditos.id')
                                   ->where([    
                                                ['estado_credito','=','pendiente'],
                                                ['detalle_credito_abonos.fecha_abono','>=',$datos->datos->fecha."  00:00:00"],
                                                ['detalle_credito_abonos.fecha_abono','<=',$datos->hora_cliente],
                                                ["detalle_credito_abonos.fk_id_sede","=",$datos->datos->sede]
                                            ])
                                   ->select(DB::raw('SUM(detalle_credito_abonos.abono) AS total_abonos'))
                                   ->get();
                
                $salidas_dinero_caja=DB::table('salida_contables')
                                    ->join('detalle_salida_contables','detalle_salida_contables.fk_id_salida_contable','=','salida_contables.id')
                                    ->where([
                                            ["detalle_salida_contables.fk_id_sede","=",$datos->datos->sede],
                                            ["detalle_salida_contables.fecha_registro_salida",'>=',
                                                $datos->datos->fecha."  00:00:00"],
                                            ['detalle_salida_contables.fecha_registro_salida','<=',$datos->hora_cliente]   
                                            ])
                                           ->where("salida_contables.nombre_salida","=","pago a proveedor") 
                                    ->select(DB::raw('SUM(detalle_salida_contables.valor_salida) AS total_salida'))
                                    ->get();                        
                $dinero_inicial_caja=DB::table("entrada_contables")
                                        ->join("detalle_entrada_contables","detalle_entrada_contables.fk_id_entrada_contable","=","entrada_contables.id")
                                        ->join("sedes","sedes.id","=","detalle_entrada_contables.fk_id_sede")
                                        ->where($datos->datos->filtro)
                                        ->where("entrada_contables.nombre_entrada","=","CajaInicial")
                                        ->where("detalle_entrada_contables.fk_id_sede","=",$datos->datos->sede)
                                        ->groupby("detalle_entrada_contables.fk_id_entrada_contable")
                                        ->select(DB::raw("SUM(detalle_entrada_contables.valor_entrada) as total_entrada_inicial_caja"))
                                        ->get();

                $ganancias_venta_dia=DB::table('facturas')
                                        ->join('detalle_facturas','detalle_facturas.fk_id_factura','=','facturas.id')
                                        ->join('detalle_inventarios','detalle_facturas.fk_id_producto','=','detalle_inventarios.id')
                                        ->join('productos','detalle_inventarios.fk_id_producto','=','productos.id')
                                        ->join('departamentos','productos.fk_id_departamento','=','departamentos.id')                    
                                        ->where([
                                                ["facturas.fk_id_sede","=",$datos->datos->sede],
                                                ['estado_factura','=','paga'],
                                                ['registro_factura','>=',$datos->datos->fecha."  00:00:00"],
                                                ['registro_factura','<=',$datos->hora_cliente]
                                            ])
                                        
                                        ->select('facturas.id',
                                             'facturas.numero_factura',
                                             'facturas.estado_factura',
                                             'facturas.registro_factura',
                                             "detalle_facturas.tipo_venta",
                                             'productos.codigo_producto',
                                             'productos.nombre_producto',
                                             'productos.precio_compra',
                                             'productos.precio_compra_blister',
                                             'productos.precio_compra_unidad',
                                             'productos.impuesto',
                                             "detalle_inventarios.precio_venta_sede",
                                             "detalle_inventarios.precio_venta_blister_sede",
                                             "detalle_inventarios.precio_mayoreo_sede",
                                             'departamentos.nombre_departamento',
                                             'detalle_facturas.valor_item',
                                             'detalle_facturas.cantidad_producto')
                                             
                                            
                                        ->get();                             



    			return ["mensaje"=>"REPORTE CORTE DIARIO GENERADO","respuesta"=>true,
                                                    "entradas_en_efectivo"=>$entradas_en_efectivo,
                                                    "pagos_de_contado"=>$pagos_de_contado_facturas,
                                                    "ventas_por_departamento"=>$ventas_por_departamento,
                                                    "pago_de_clientes"=>$pago_de_clientes,
                                                    "salidas_dinero_caja"=>$salidas_dinero_caja,
                                                     "dinero_caja_inicial"=>$dinero_inicial_caja,
                                                     "creditos"=>$credito_facturas,
                                                        "ganancias_venta_dia"=>$ganancias_venta_dia];
    		    break;	
    		 default:
    		 	return ["mensaje"=>"Por favor selecciona un tipo de reporte ","respuesta"=>true,"datos"=>$reporte];
    		 break;   
    	}
    }
}