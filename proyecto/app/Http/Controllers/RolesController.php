<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use App\Rol;

use DB;

class RolesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $rol=new Rol();
        return response()->json($rol->consultar_todos());
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
        $rol=new Rol();
        $datos=json_decode($request->get('datos'));
        return response()->json($rol->insertar(array(
            "nombre_rol"=>$datos->datos->nombre_rol,
            "descripcion_rol"=>$datos->datos->descripcion_rol,
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
        //$rol=new Rol();
        //$rrol=$rol->consultar_por_campo(array(array("id","=",$id)),"AND",array());
        $rrol=DB::table("rols")
                
                ->join("detalle_permisos","detalle_permisos.fk_id_rol","=","rols.id")
                ->join("permisos","permisos.id","=","detalle_permisos.fk_id_permiso")
                ->where("rols.id","=",$id)
                ->groupby("permisos.id")
                ->select("nombre_permiso","estado_permiso","permisos.id")
                ->get();
                $arr_rol=array();
                foreach ($rrol as $key => $value) {
                    $arr_rol[$key]=(array)$value;
                    $arr_rol[$key]["permisos"]=(array)DB::table("detalle_permisos")

                                            ->where([["detalle_permisos.fk_id_permiso","=",$value->id],["fk_id_rol","=",$id]])
                                            ->get();    
                }        
        return response()->json(["mensaje"=>"Roles encontrados","respuesta"=>true,"datos"=>$arr_rol]);
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
        $rol=new Rol();
        return response()->json($rol->editar(array(

               "nombre_rol"=>$datos->datos->nombre_rol,
                "descripcion_rol"=>$datos->datos->descripcion_rol,      
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
        $rol=new Rol();
        return response()->json($rol->eliminar(array("id","=",$id)));
    }
}
