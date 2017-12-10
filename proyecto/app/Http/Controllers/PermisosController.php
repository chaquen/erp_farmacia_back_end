<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use App\Permisos;

class PermisosController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $per=new Permisos();
        return response()->json($per->consultar_todos());
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
        $per=new Permisos();
        $datos=json_decode($request->get("datos"));
        return response()->json($per->insertar(
            [
                "nombre_permiso"=>$datos->datos->nombre_permiso,
                "descripcion_permiso"=>$datos->datos->descripcion_permiso,
                "created_at"=>$datos->hora_cliente,
                "updated_at"=>$datos->hora_cliente, 
            ]
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
        $per=new Permisos();
        return response()->json($per->consultar_por_campo(array(["id","=",$id]),"AND",array()));
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
        $per=new Permisos();
        return response()->json($per->editar([
                "nombre_permiso"=>$datos->datos->nombre_permiso,
                "descripcion_permiso"=>$datos->datos->descripcion_permiso,
                "updated_at"=>$datos->hora_cliente, 

            ],["id","=",$id]));
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
        $per=new Permisos();
        return response()->json($per->eliminar(["id","=",$id]));
    }
}
