<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use App\Cliente;

use DB;

class ClienteController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //  
        $cli=new Cliente();
        return response()->json($cli->consultar_todos());


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
        $cli=new Cliente();
        $r=$cli->consultar_por_campo(
                                        array(
                                                array("documento",'=',$datos->datos->documento)
                                              ),"AND",array(
                                                
                                              )
                                     );
            
        if($r["respuesta"]!=true){

            $arr_res=$cli->insertar(
                array(
                    "nombre_cliente"=>$datos->datos->nombre_cliente,
                    "documento"=>$datos->datos->documento,
                    "email"=>$datos->datos->email,
                    "celular"=>$datos->datos->celular,
                    "telefono"=>$datos->datos->telefono,
                    "direccion"=>$datos->datos->direccion,
                    "limite_de_credito"=>$datos->datos->limite_de_credito,
                    "created_at"=>$datos->hora_cliente,
                    "updated_at"=>$datos->hora_cliente, 
                ));
            if($arr_res["respuesta"]==true){
                    DB::table('creditos')
                        ->insert([
                            "fk_id_cliente"=>$arr_res["id"],
                             "created_at"=>$datos->hora_cliente,
                             "updated_at"=>$datos->hora_cliente, 
                             "valor_credito"=>0,
                             "valor_actual_credito"=>0,
                            ]);
                    return response()->json($arr_res);    
            }else{
                return response()->json($arr_res);    
            }

            

        }else{
            return response()->json(["respuesta"=>false,"mensaje"=>"Cliente ya existe"]);
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
        /*$cli=new Cliente();
        return response()->json($cli->consultar_por_campo(
                                        array(
                                                array("nombre_cliente",'LIKE',"%".$id."%")
                                              ),"OR",array(["documento",'=',$id]
                                                
                                              )
                                     )
                            );*/
        $cli=DB::table('clientes')
            ->join("creditos","creditos.fk_id_cliente","=","clientes.id")
            ->where("nombre_cliente",'LIKE',"%".$id."%")
            ->orwhere("documento",'=',$id)
            ->select("nombre_cliente",
                    "documento",
                    "email",
                    "celular",
                    "telefono",
                    "direccion",
                    "estado_cliente",
                    "valor_credito",
                    "valor_actual_credito",
                    "limite_de_credito",
                    "creditos.id")
            ->get();
            $arr_cli=array();
            $i=0;
            foreach ($cli as $key => $value) {
                $arr_cli[$i]=(array)$value;

                $arr_cli[$i]["facturas"]=(array)DB::table('facturas')
                    //->join('detalle_facturas','detalle_facturas.fk_id_factura',"=","facturas.id")
                    ->where("fk_id_cliente","=",$value->id)
                    ->get();

                   

                   $i++; 
            }
            $l=0;
            $arr_cli_fac=array();
            foreach ($arr_cli as $key => $vf) {
                $arr_cli_fac[$key]=(array)$vf;

                foreach ($vf["facturas"] as $k => $v) {
                    
                    $arr_cli_fac[$key]["facturas"][$k]=(array)$v;
                    
                    $arr_dt=(array)DB::table('detalle_facturas')
                                        ->join('detalle_inventarios',"detalle_inventarios.fk_id_producto","=","detalle_facturas.fk_id_producto")
                                                                                    ->join('productos',"detalle_inventarios.fk_id_producto","=","productos.id")
                                                                                    ->join("facturas","facturas.id","=","detalle_facturas.fk_id_factura")
                                                                                    ->join("sedes","sedes.id","=","facturas.fk_id_sede")    
                                                                                    ->where([["fk_id_factura","=",$v->id],["sedes.id","=",$v->fk_id_sede]])
                                                                                    ->select("detalle_facturas.fk_id_factura",
                                                                                        "facturas.fk_id_sede",
                                                                                        "detalle_facturas.tipo_venta",
                                                                                        "productos.nombre_producto_venta",
                                                                                        "detalle_facturas.valor_item",
                                                                                        "detalle_facturas.cantidad_producto"
                                                                                        )
                                                                                    ->groupby("sedes.id")
                                                                                    ->get();
                            // var_dump($arr_dt);                                                       
                 
                    $arr_cli_fac[$key]["facturas"][$k]["detalle_factura"]=$arr_dt;
                }
                        
                       $l++;
            }

            if(count($cli)>0){
                return response()->json(["respuesta"=>true,"mensaje"=>"Cliente encontrado","datos"=>$arr_cli_fac]);        
            }
            return response()->json(["respuesta"=>false,"mensaje"=>"Cliente no existe"]);  
          
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

        $datos=json_decode($request->get('datos'));
        $cli=new Cliente();
        return response()->json(
                        $cli->editar(array(
                                "nombre_cliente"=>$datos->datos->nombre_cliente,
                                "documento"=>$datos->datos->documento,
                                "email"=>$datos->datos->email,
                                "celular"=>$datos->datos->celular,
                                "limite_de_credito"=>$datos->datos->limite_de_credito,
                                "telefono"=>$datos->datos->telefono,
                                "direccion"=>$datos->datos->direccion,
                                "updated_at"=>$datos->hora_cliente, 
                                ),
                             array(["id","=",$id])
                        ));
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
        $cli=new Cliente();
        return response()->json(
                                $cli->eliminar("id","=",$id)
                                );
    }
}
