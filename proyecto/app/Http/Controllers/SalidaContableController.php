<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use App\SalidaContable;

class SalidaContableController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $sal=new SalidaContable();
        return response()->json($sal->consultar_todos());
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
        $sal=new SalidaContable();
        $datos=json_decode($request->get("datos"));
        return response()->json($sal->insertar(array(
                "nombre_salida"=>$datos->datos->nombre_salida,
                "descipcion_salida"=>$datos->datos->descipcion_salida,
                "maximo_valor_salida"=>$datos->datos->maximo_valor_salida,
                "created_at"=>$datos->hora_cliente,
                "updated_at"=>$datos->hora_cliente, 
            )));
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
        $sal=new SalidaContable();
        return response()->json($sal->consultar_por_campo(array(array("id","=",$id)),"AND",array()));
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
        $sal=new SalidaContable();
        return response()->json($sal->editar(array(
                "nombre_salida"=>$datos->datos->nombre_salida,
                "descipcion_salida"=>$datos->datos->descipcion_salida,
                "maximo_valor_salida"=>$datos->datos->maximo_valor_salida,
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
        $sal=new SalidaContable();
        return response()->json($sal->eliminar(array("id","=",$id)));
    }
}
