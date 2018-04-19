<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use  App\DetalleEntradaContable;

use DB;

class DetalleEntradaContableController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $dec=new DetalleEntradaContable();
        return response()->json($dec->consultar_todos());
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
        $dec=new DetalleEntradaContable();
        $datos=json_decode($request->get("datos"));
        if($datos->datos->tipo_entrada=="CajaInicial"){
            $e=DB::table("entrada_contables")
                ->join("detalle_entrada_contables","detalle_entrada_contables.fk_id_entrada_contable","=","entrada_contables.id")
                ->where([["detalle_entrada_contables.fecha_entrada","LIKE",explode(" ",$datos->hora_cliente)[0]." %"],
                        ["entrada_contables.nombre_entrada","=","CajaInicial"],
                        ["detalle_entrada_contables.fk_id_sede","=",$datos->datos->fk_id_sede]])    
                ->get();
        
            if(count($e)==1){
                return response()->json(["respuesta"=>false,"mensaje"=>"Ya esta registrada una entrada"]);
            }    
        }
        if($datos->datos->tipo_entrada!=0){
            return response()->json($dec->insertar(array(
            "fk_id_entrada_contable"=>$datos->datos->fk_id_entrada_contable,
            "fk_id_usuario"=>$datos->datos->fk_id_usuario,
            "fk_id_sede"=>$datos->datos->fk_id_sede,
            "valor_entrada"=>$datos->datos->valor_entrada,
            "fecha_entrada"=>$datos->hora_cliente,
            "created_at"=>$datos->hora_cliente,
            "updated_at"=>$datos->hora_cliente, 
            )));    
        }else{
            return response()->json(["respuesta"=>false,"mensaje"=>"Debes seleccionar un tipo de entrada"]);
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
        $consulta= explode("&", $id);
        $va=explode(" ",$consulta[0]);
        
        if($va[0]!=""){
            $fecha1=explode(" ",$va[0])[0]." 00:00:00";    
            $fecha2=$consulta[0];
        }else{
            $fecha1="";
            $fecha2="";
        }
        
        $dec=new DetalleEntradaContable();
        if($fecha1!="" && $fecha2!=""){
          
            $d=DB::table('detalle_entrada_contables')
              ->join("entrada_contables","entrada_contables.id","=","detalle_entrada_contables.fk_id_entrada_contable")  
              ->where([
                        ["detalle_entrada_contables.fk_id_sede","=",$consulta[1]],
                        //["entrada_contables.nombre_entrada","<>","CajaInicial"],
                        //["entrada_contables.nombre_entrada","<>","VentaDiaria"],
                        ["detalle_entrada_contables.fecha_entrada",">=",$fecha1],
                        ["detalle_entrada_contables.fecha_entrada","<=",$fecha2],
                  ])
                ->get();
        }else{
           
            $d=DB::table('detalle_entrada_contables')
              ->join("entrada_contables","entrada_contables.id","=","detalle_entrada_contables.fk_id_entrada_contable")  
              ->where([
                        ["detalle_entrada_contables.fk_id_sede","=",$consulta[1]],
                        //["entrada_contables.nombre_entrada","<>","CajaInicial"],
                        //["entrada_contables.nombre_entrada","<>","VentaDiaria"],
                        
                  ])
                ->get();
        }    
        if(count($d)>0){
              return response()->json(["mensaje"=>"Entrada consultara","respuesta"=>true,"datos"=>$d]);  
        }
        return response()->json(["mensaje"=>"Entradas NO encontradas","respuesta"=>false]);
        
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
        $dec=new DetalleEntradaContable();
        $datos=json_decode($request->get("datos"));
        return response()->json($dec->editar(array(
                "fk_id_entrada_contable"=>$datos->datos->fk_id_entrada_contable,
                "fk_id_usuario"=>$datos->datos->fk_id_usuario,
                "valor_entrada"=>$datos->datos->valor_entrada,
                "updated_at"=>$datos->hora_cliente, 

                ),
                array("id","=",$id)
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
        $dec=new DetalleEntradaContable();
        return response()->json($dec->eliminar(array("id","=",$id)));
    }
}
