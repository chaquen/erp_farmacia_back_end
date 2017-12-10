<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use App\Impuesto;

class ImpuestosController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $inv=new Impuesto();

        return response()->json($inv->consultar_todos());
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
        $inv=new Impuesto();
        $datos=json_decode($request->get('datos'));
        return response()->json($inv->insertar(
                        array(
                            "nombre_impuesto"=>$datos->datos->nombre_impuesto,
                            "valor_impuesto"=>$datos->datos->valor_impuesto,
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
        $inv=new Impuesto();
        return response()->json($inv->consultar_por_campo(array(array("id","=",$id)),"AND",array()));
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
        $inv=new Impuesto();
        return response()->json($inv->editar(array(
                 "nombre_impuesto"=>$datos->datos->nombre_impuesto,
                 "valor_impuesto"=>$datos->datos->valor_impuesto,
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
        $inv=new Impuesto();
        return response()->json($inv->eliminar(array("id","=",$id)));
    }
}
