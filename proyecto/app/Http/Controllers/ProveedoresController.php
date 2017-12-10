<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use App\Proveedor;

class ProveedoresController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $pro=new Proveedor();
        return response()->json($pro->consultar_todos());
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
        $pro=new Proveedor();
        $datos=json_decode($request->get('datos'));
        return response()->json($pro->insertar(array(
            "nombre_proveedor"=>$datos->datos->nombre_proveedor,
            
            "nombre_contacto_proveedor"=>$datos->datos->nombre_contacto_proveedor,
            "telefono_contacto_proveedor"=>$datos->datos->telefono_contacto_proveedor,
            
            "email_contacto_proveedor"=>$datos->datos->email_contacto_proveedor,
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
     
        $pro=new Proveedor();
        return response()->json($pro->consultar_por_campo(array(array("id","=",$id)),"OR",array(array("nombre_proveedor","LIKE",$id))));
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
        $datos= json_decode($request->get("datos"));
        $pro=new Proveedor();
        return response()->json($pro->editar(array(
                "nombre_proveedor"=>$datos->datos->nombre_proveedor,
                
                "nombre_contacto_proveedor"=>$datos->datos->nombre_contacto_proveedor,
                "telefono_contacto_proveedor"=>$datos->datos->telefono_contacto_proveedor,
               
                "email_contacto_proveedor"=>$datos->datos->email_contacto_proveedor,
                 "updated_at"=>$datos->hora_cliente, ),
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
        $pro=new Proveedor();
        return response()->json($pro->eliminar(array("id","=",$id)));
    }
}
