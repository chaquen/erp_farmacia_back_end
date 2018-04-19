<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use App\Sede;

use DB;

class SedesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $sede=new Sede();
        return response()->json($sede->consultar_todos());
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
        $sede=new Sede();
        $datos=json_decode($request->get('datos'));
        $rr=$sede->insertar(
            array(
                "nombre_sede"=>$datos->datos->nombre_sede,
                "direccion_sede"=>$datos->datos->direccion_sede,
                "telefono_sede"=>$datos->datos->telefono_sede,
                "horario"=>json_encode($datos->datos->horario),
                "fk_id_administrador"=>$datos->datos->fk_id_administrador,
                "created_at"=>$datos->hora_cliente,
                "updated_at"=>$datos->hora_cliente, 
                )

            );
        if($rr["respuesta"]==TRUE){
            if($datos->datos->inventario==TRUE){
                //aqui insertar todo el inventario general ala nueva sede
                $p=DB::table('productos')
                    ->get();
                    $r=0;
                foreach ($p as $key => $value) {
                       
                         $arr_dt_inv[$r]=[ "fk_id_producto"=>$value->id,
                                           "fk_id_sede"=>$rr["id"],
                                           "fecha_caducidad"=>"0000-00-00",
                                           "cantidad_existencias"=>0,
                                           "cantidad_existencias_unidades"=>0,
                                           "cantidad_devueltas"=>0,
                                           "porcentaje_ganancia_sede"=>$value->porcentaje_ganancia,
                                            "porcentaje_ganancia_sede_unidad"=>$value->porcentaje_ganancia,
                                            "precio_venta_sede"=>$value->precio_venta,
                                            "precio_mayoreo_sede"=>$value->precio_mayoreo,
                                            "minimo_inventario_sede"=>$value->minimo_inventario,
                                             "created_at"=>$datos->hora_cliente,
                                              "updated_at"=>$datos->hora_cliente,

                                                                                ];
                                                                             $r++;   
                }    
                 $limitStatements = DB::selectOne( DB::raw("SELECT @@max_prepared_stmt_count AS count")
                                                  )->count;
                 $div=count($arr_dt_inv)/500;
                 $lotes = array_chunk($arr_dt_inv, $div);
                                                                    
                foreach ($lotes as $lote) {
                    DB::table("detalle_inventarios")
                        ->insert($lote); 
                }
            }
            $u=DB::table("users")
                ->where("id","=",$datos->datos->fk_id_administrador)->get();
              
            DB::table("notificaciones")
                ->insert([
                        ["trabajo"=>"BajoInventario","correos"=>$u[0]->email,"fk_id_sede"=>$rr["id"]],
                        ["trabajo"=>"CorteDiario","correos"=>$u[0]->email,"fk_id_sede"=>$rr["id"]],
                        ["trabajo"=>"BajoInventario","correos"=>$u[0]->email,"fk_id_sede"=>$rr["id"]]
                    ]);


            DB::table("detalle_cajero_sedes")->insert([
                "fk_id_usuario"=>$datos->datos->fk_id_administrador,
                "fk_id_sede"=>$rr["id"],
                "tipo"=>"administrador",
                "created_at"=>$datos->hora_cliente,
                "updated_at"=>$datos->hora_cliente, 

            ]);    
        }
        
        return response()->json($rr);
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
         $sede=new Sede();
         
                 
        return response()->json($sede->consultar_por_campo(array(array("id","=",$id)),"AND",array()));
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
         $sede=new Sede();
        return response()->json($sede->editar(array(
                "nombre_sede"=>$datos->datos->nombre_sede,
                "direccion_sede"=>$datos->datos->direccion_sede,
                "telefono_sede"=>$datos->datos->telefono_sede,
                "horario"=>json_encode($datos->datos->horario),
                "fk_id_administrador"=>$datos->datos->fk_id_administrador,
                "updated_at"=>$datos->hora_cliente, 


            ),
                             array(["id","=",$id])));
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
         $sede=new Sede();
        return response()->json($sede->eliminar(array(["id","=",$id])));
    }
    
    public function eliminar_detalle_cajero_sede($id) {
        DB::table('detalle_cajero_sedes')
                ->where("id","=",$id)
                ->delete();
        return response()->json(["respuesta"=>true,"mensaje"=>"Usario eliminado de esta sede"]);
    }
}
