<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use App\DetalleSalidaContable;

use DB;

class   DetalleSalidaContableController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $dsc=new DetalleSalidaContable();
        return response()->json($dsc->consultar_todos());
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
        //var_dump($datos->datos->motivo);///

        $dsc=new DetalleSalidaContable();
        
        return response()->json($dsc->insertar(
                    array(
                        "fk_id_salida_contable"=>$datos->datos->fk_id_salida_contable,
                        "fk_id_usuario"=>$datos->datos->fk_id_usuario,
                        "fk_id_sede"=>$datos->datos->fk_id_sede,
                        "valor_salida"=>$datos->datos->valor_salida,
                        "fecha_registro_salida"=>$datos->hora_cliente,
                        "motivo"=>$datos->datos->motivo,
                        "created_at"=>$datos->hora_cliente,
                        "updated_at"=>$datos->hora_cliente, 
                        )
            ));
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
        //var_dump($id);
        
        $consulta= explode("&", $id);
        $va=explode(" ",$consulta[0]);
        
        if($va[0]!=""){
            $fecha1=explode(" ",$va[0])[0]." 00:00:00";    
            $fecha2=$consulta[0];
        }else{
            $fecha1="";
            $fecha2="";
        }
        
        $dec=new DetalleSalidaContable();

            //var_dump($fecha1);
            //var_dump($fecha2);
            if($fecha1!="" && $fecha2!=""){
             $d=DB::table('detalle_salida_contables')
              ->join("salida_contables","salida_contables.id","=","detalle_salida_contables.fk_id_salida_contable")  
              ->where([
                        ["detalle_salida_contables.fk_id_sede","=",$consulta[1]],
                        //["entrada_contables.nombre_entrada","<>","CajaInicial"],
                        //["entrada_contables.nombre_entrada","<>","VentaDiaria"],
                        ["detalle_salida_contables.fecha_registro_salida",">=",$fecha1],
                        ["detalle_salida_contables.fecha_registro_salida","<=",$fecha2],
                  ])
                ->get();
            }else{
                $d=DB::table('detalle_salida_contables')
                  ->join("salida_contables","salida_contables.id","=","detalle_salida_contables.fk_id_salida_contable")  
                  ->where([
                            ["detalle_salida_contables.fk_id_sede","=",$consulta[1]],
                            //["entrada_contables.nombre_entrada","<>","CajaInicial"],
                            //["entrada_contables.nombre_entrada","<>","VentaDiaria"],
                           
                      ])
                    ->get();
            }
        if(count($d)>0){
              return response()->json(["mensaje"=>"Salida consultara","respuesta"=>true,"datos"=>$d]);  
        }
        return response()->json(["mensaje"=>"Salidas NO encontradas","respuesta"=>false]);
        
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
        $dsc=new DetalleSalidaContable();
        return response()->json($dsc->editar(array(
                "fk_id_salida_contable"=>$datos->datos->fk_id_salida_contable,
                        "fk_id_usuario"=>$datos->datos->fk_id_usuario,
                        "valor_salida"=>$datos->datos->valor_salida,
                        "updated_at"=>$datos->hora_cliente, 

            ),
                             array("id","=",$id)));
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
        $dsc=new DetalleSalidaContable();
        return response()->json($dsc->eliminar(array("id","=",$id)));
    }
}
