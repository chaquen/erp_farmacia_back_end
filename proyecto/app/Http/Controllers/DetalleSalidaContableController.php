<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use App\DetalleSalidaContable;

class DetalleSalidaContableController extends Controller
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
        $dsc=new DetalleSalidaContable();
        $datos=json_decode($request->get("datos"));
        return response()->json($dsc->insertar(
                    array(
                        "fk_id_salida_contable"=>$datos->datos->fk_id_salida_contable,
                        "fk_id_usuario"=>$datos->datos->fk_id_usuario,
                        "fk_id_sede"=>$datos->datos->fk_id_sede,
                        "valor_salida"=>$datos->datos->valor_salida,
                        "fecha_registro_salida"=>$datos->hora_cliente,
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
        $dsc=new DetalleSalidaContable();
        return response()->json($dsc->consultar_por_campo(array(array("id","=",$id)),"AND",array()));
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
