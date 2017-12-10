<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use DB;

class NotificacionesController extends Controller
{
    //
    public function notificaciones(Request $request){
    	$datos=json_decode($request->get("datos"));
    DB::table('notificaciones')
    ->where([["trabajo","=","BajoInventario"],["fk_id_sede","=",$datos->datos->sede]])
        ->update(["correos"=>$datos->datos->bajoInv]);
     DB::table('notificaciones')
     ->where([["trabajo","=","CorteDiario"],["fk_id_sede","=",$datos->datos->sede]])
        ->update(["correos"=>$datos->datos->corteDia]);  
     DB::table('notificaciones')
     ->where([["trabajo","=","PedidoDiario"],["fk_id_sede","=",$datos->datos->sede]])
        ->update(["correos"=>$datos->datos->pedidoDia]);     
     return response()->json(["respuesta"=>true,"mensaje"=>"notificaciones actualizadas"]);        
    }

    public function buscar_notificaciones($id_sede){
        $d=DB::table('notificaciones')
            ->where("fk_id_sede","=",$id_sede)
            ->get();
        return response()->json(["respuesta"=>true,"mensaje"=>"notificaciones encontradas","datos"=>$d]);                   
    }

}
