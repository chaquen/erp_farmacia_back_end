<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use App\Reportes;


class ReportesController extends Controller
{
    //
    public function reporte_inventario(Request $request){
    	$datos=json_decode($request->get('datos'));
        $rep=new Reportes();
        return response()->json($rep->reporte_inventario($datos));
    }

    public function reporte_bajo_inventario(Request $request){
    	
        $datos=json_decode($request->get('datos'));
        $rep=new Reportes();
    	return response()->json($rep->reporte_bajo_inventario($datos));

    }
    public function reporte_movimientos_inventario(Request $request){
	$datos=json_decode($request->get('datos'));
        $rep=new Reportes();
    	return response()->json($rep->reporte_movimientos_inventario($datos));
    }


    public function reporte_saldos(Request $request){
    	$datos=json_decode($request->get('datos'));
        $rep=new Reportes();
        return response()->json($rep->reporte_saldos($datos));
    	
    }
    public function reporte_ventas_por_periodo(Request $request){
    	$datos=json_decode($request->get('datos'));
        $rep=new Reportes();
    	return response()->json($rep->reporte_ventas_por_periodo($datos));
    }
    public function reporte_corte_diario(Request $request){
    	$datos=json_decode($request->get('datos'));
        $rep=new Reportes();
    	return response()->json($rep->reporte_corte_diario($datos));
    }
}
