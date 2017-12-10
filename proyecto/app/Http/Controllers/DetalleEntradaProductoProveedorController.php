<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use App\DetalleEntradaProductoProveedor;

class DetalleEntradaProductoProveedorController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $depp=new DetalleEntradaProductoProveedor();
        return response()->json($depp->consultar_todos());
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
        $depp=new DetalleEntradaProductoProveedor();
        $datos=json_decode($request->get("datos"));
        return response()->json($depp->insertar([
               "fk_id_proveedor"=>$datos->datos->fk_id_proveedor,
               "fk_id_det_inventario"=>$datos->datos->fk_id_det_inventario,
               "cantidad_entrada"=>$datos->datos->cantidad_entrada,
               "fecha_caducidad"=>$datos->datos->fecha_caducidad,
               "Observaciones"=>$datos->datos->observaciones,
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
        $depp=new DetalleEntradaProductoProveedor();
        return response()->json($depp->consultar_por_campo([["id","=",$id]],"AND",[]));
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
        $depp=new DetalleEntradaProductoProveedor();
        return response()->json($depp->editar([
                "fk_id_proveedor"=>$datos->datos->fk_id_proveedor,
               "fk_id_det_inventario"=>$datos->datos->fk_id_det_inventario,
               "cantidad_entrada"=>$datos->datos->cantidad_entrada,
               "fecha_caducidad"=>$datos->datos->fecha_caducidad,
               "Observaciones"=>$datos->datos->observaciones,
               "created_at"=>$datos->hora_cliente,
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
        $depp=new DetalleEntradaProductoProveedor();
        return response()->json($depp->eliminar(["id","=",$id]));
    }
}
