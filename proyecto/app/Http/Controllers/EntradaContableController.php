<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use App\EntradaContable;

class EntradaContableController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $ent=new EntradaContable();
        return response()->json($ent->consultar_todos());
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
        $ent=new EntradaContable();
        $datos=json_decode($request->get("datos"));
        return response()->json($ent->insertar(array(
                "nombre_entrada"=>$datos->datos->nombre_entrada,
                "descripcion_entrada"=>$datos->datos->descripcion_entrada,
                "maximo_valor_entrada"=>$datos->datos->maximo_valor_entrada,
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
        $ent=new EntradaContable();
        return response()->json($ent->consultar_por_campo(array(array("id","=",$id)),"AND",array()));
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
        $ent=new EntradaContable();
        return response()->json($ent->editar(array(
                "nombre_entrada"=>$datos->datos->nombre_entrada,
                "descripcion_entrada"=>$datos->datos->descripcion_entrada,
                "maximo_valor_entrada"=>$datos->datos->maximo_valor_entrada,
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
        $ent=new EntradaContable();
        return response()->json($ent->eliminar(array("id","=",$id)));
    }
}
