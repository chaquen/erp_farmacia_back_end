<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use App\DetallePermiso;

class DetallePermisoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $dp=new DetallePermiso();
        return response()->json($dp->consultar_todos());
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
        $dp=new DetallePermiso();
        return response()->json($dp->insertar([

            "fk_id_rol"=>$datos->datos->fk_id_rol,
            "fk_id_permiso"=>$datos->datos->fk_id_permiso,
            "consultar"=>$datos->datos->consultar,
            "editar"=>$datos->datos->editar,
            "eliminar"=>$datos->datos->eliminar,
            "crear"=>$datos->datos->crear,
            "created_at"=>$datos->hora_cliente,
            "updated_at"=>$datos->hora_cliente, 


            ]));
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
        $dp=new DetallePermiso();
        return response()->json($dp->consultar_por_campo([["fk_id_rol","=",$id]],"OR",[["fk_id_permiso","=",$id]]));
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
        $dp=new DetallePermiso();
        $datos=json_decode($request->get("datos"));
        return response()->json($dp->editar([
            "fk_id_rol"=>$datos->datos->fk_id_rol,
            "fk_id_permiso"=>$datos->datos->fk_id_permiso,
            "consultar"=>$datos->datos->consultar,
            "editar"=>$datos->datos->editar,
            "eliminar"=>$datos->datos->eliminar,
            "crear"=>$datos->datos->crear,
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
        $dp=new DetallePermiso();
        return response()->json($dp->eliminar(["id","=",$id]));
    }
}
