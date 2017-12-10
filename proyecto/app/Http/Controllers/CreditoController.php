<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use DB;

class CreditoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
            $r=DB::table('creditos')
                ->join("facturas","facturas.id","=","creditos.fk_id_factura")
                ->join("clientes","clientes.id","=","creditos.fk_id_cliente")
                ->select(DB::raw('CONCAT(clientes.nombre_cliente ," / ",clientes.direccion) as nombre_cliente'),
                    'clientes.telefono',
                    'clientes.limite_de_credito',
                    'creditos.valor_actual_credito',
                    'creditos.valor_credito',
                    'creditos.id')
                ->get();
             $arr=[];
             $i=0;
             foreach ($r as $key => $value) {
                 $arr[$i]=(array)$value;
                 $arr[$i]["detalle_credito"]=array();
                 $dt_cre=DB::table('detalle_credito_abonos')
                                                ->where("fk_id_credito","=",$value->id)
                                                ->orderBy("id","DESC")
                                                ->get();
                $ii=0;
                 foreach ($dt_cre as $key => $value) {
                      $arr[$i]["detalle_credito"][$ii]=$value;
                 }

                 $i++;
             }
            if(count($r)>0){
                return response()->json(["mensaje"=>"Creditos","respuesta"=>true,"datos"=>$arr]);    
            }else{
                
                return response()->json(["mensaje"=>"Creditos NO registrados","respuesta"=>false]);    
            }    
            
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
        //ABONAR 
        $datos=json_decode($request->get("datos"));
        DB::table("detalle_credito_abonos")
            ->insert(["fk_id_credito"=>$id,
                    "fk_id_sede"=>$datos->datos->id_sede,
                    "observacion"=>"Abono a la deuda ".$datos->hora_cliente,
                    "abono"=>$datos->datos->valor_abono,
                    "fecha_abono"=>$datos->hora_cliente,
                    "created_at"=>$datos->hora_cliente,
                    "updated_at"=>$datos->hora_cliente,
                    ]);
            DB::table('creditos')
                ->where("id","=",$id)
                ->decrement("valor_actual_credito",$datos->datos->valor_abono);
            DB::table("detalle_entrada_contables")
            ->insert(["fk_id_entrada_contable"=>4,
                "fk_id_usuario"=>$datos->datos->id_usuario,
                "fk_id_sede"=>$datos->datos->id_sede,
                "valor_entrada"=>$datos->datos->valor_abono,
                "fecha_entrada"=>$datos->hora_cliente,
                "created_at"=>$datos->hora_cliente,
                "updated_at"=>$datos->hora_cliente,
                ]);   
         return response()->json(["respuesta"=>true,"mensaje"=>"Abono registrado"]);   
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        
    }
}
