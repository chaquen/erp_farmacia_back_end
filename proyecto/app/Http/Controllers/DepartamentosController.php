<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use App\Departamento;

class DepartamentosController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $dep=new Departamento();

        return response()->json($dep->consultar_todos());
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
        $dat=json_decode($request->get('datos'));
        $dep=new Departamento();

        return response()->json($dep->insertar(array(
                    "nombre_departamento"=>$dat->datos->nombre_departamento,
                    "created_at"=>$dat->hora_cliente,
                    "updated_at"=>$dat->hora_cliente, 
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
        $dep=new Departamento();
        return response()->json($dep->consultar_por_campo(array(["id",'=',$id],["estado_departamento","=","1"]),"OR",array(array("nombre_departamento","LIKE","%".$id."%"))));
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
        $dep=new Departamento();
        $dat=json_decode($request->get('datos'));
        return response()->json($dep->editar(array(
                    "nombre_departamento"=>$dat->datos->nombre_departamento,
                    "updated_at"=>$dat->hora_cliente,
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
        $dep=new Departamento();
        return response()->json($dep->eliminar(array(["id","=",$id])));
    }
}
