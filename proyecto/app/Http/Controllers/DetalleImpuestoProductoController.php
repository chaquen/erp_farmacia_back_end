<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use App\DetalleImpuestoProducto;

class DetalleImpuestoProductoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $dip=new DetalleImpuestoProducto();
        return response()->json($dip->consultar_todos());
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
        $dip=new DetalleImpuestoProducto();
        return response()->json($dip->insertar([
             "fk_id_impuesto"=>$datos->datos->fk_id_impuesto,
             "fk_id_detalle_inventario"=>$datos->datos->fk_id_detalle_inventario,
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
        $dip=new DetalleImpuestoProducto();
        return response()->json($dip->consultar_por_campo([["id","=",$id]],"AND",[]));
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
        $dip=new DetalleImpuestoProducto();
        return response()->json($dip->editar([
             "fk_id_impuesto"=>$datos->datos->fk_id_impuesto,
             "fk_id_detalle_inventario"=>$datos->datos->fk_id_detalle_inventario,
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
        $dip=new DetalleImpuestoProducto();
        return response()->json($dip->eliminar(["id","=",$id]));
    }
}
