<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use Maatwebsite\Excel\Facades\Excel;

use DB;

use App\Reportes;

use App\Producto;

use Mail;

use File;

class ExportarController extends Controller
{
    //
    public function exportar_a_xls($tipo_reporte,Request $request){
        
        
            $nombre_reporte="";
            switch($tipo_reporte){
                case "reporte_ventas_por_periodo":
                    
                    $datos= json_decode($request->get("datos"));
                    $hora=explode(":", explode(" ", $datos->hora_cliente)[1]);
                    $fecha=explode(" ", $datos->hora_cliente)[0]."_".$hora[0]."_".$hora[1]."_".$hora[2];
                    $nombre_reporte="Ventas por periodo ".$fecha;                   
                    $rep= new Reportes();
                    $reporte=$rep->reporte_ventas_por_periodo($datos);
                            
                    break;
                case "reporte_inventario":
                    $datos= json_decode($request->get("datos"));
                    $hora=explode(":", explode(" ", $datos->hora_cliente)[1]);
                    
                    $fecha=explode(" ", $datos->hora_cliente)[0]."_".$hora[0]."_".$hora[1]."_".$hora[2];
                    $nombre_reporte="Reporte inventario ".$fecha;                    
                    $rep= new Reportes();
                    $reporte=$rep->reporte_inventario($datos);
                    break;
                case "reporte_bajo_inventario":
                    $datos= json_decode($request->get("datos"));
                    $hora=explode(":", explode(" ", $datos->hora_cliente)[1]);
                    $fecha=explode(" ", $datos->hora_cliente)[0]."_".$hora[0]."_".$hora[1]."_".$hora[2];
                    $nombre_reporte="Reporte_productos_bajos_en_inventario_".$fecha;                    
                    $rep= new Reportes();
                    $reporte=$rep->reporte_bajo_inventario($datos);
                    break;
            }
            if($nombre_reporte!="" && $reporte["respuesta"]){
            
                    Excel::create($nombre_reporte, function($excel) use($reporte){
                         // use($datos->datos->nombre_reporte)   
                        $excel->sheet('productos',function($sheet) use($reporte){
                               $sheet->fromArray($reporte["datos"]);
                        });
                    })->store('xls', substr(base_path(),0,-8)."archivos/exportacion/excel");
                            
                    $mi_archivo="";
                    if($tipo_reporte=="reporte_bajo_inventario"){
                        $mi_archivo="pedidos_reporte_bajo_inventario_".explode(" ",$datos->hora_cliente)[0].".txt";
                        $ruta="archivos/pedidos/txt/".$mi_archivo;
                        $tipo_separacion_archivo_plano=" ";
                        $contenido="";

                        foreach ($reporte["datos"] as $key => $value) {
                           
                           $contenido.=trim($value["id"])
                                       .$tipo_separacion_archivo_plano
                                       .trim($value["codigo_producto"])
                                       .$tipo_separacion_archivo_plano
                                       .trim($value["nombre_producto"])
                                       .$tipo_separacion_archivo_plano
                                        .trim("CANT")
                                        .$tipo_separacion_archivo_plano
                                        .trim($value["codigo_distribuidor"]).PHP_EOL;//CONSTANTE SALTO DE LINEA SEGUN EL S.O
                                        
                            
                        }
                       
                        File::put($ruta,$contenido);

                            
                    }
                    //FIN CREAR ARCHIVO TXT
                    if($datos->datos->email_usuario!=false){
                        $email=$datos->datos->email_usuario;
                        Mail::send("email.reporte_generado",["url"=>"https://api.asopharma.com/archivos/exportacion/excel/".$nombre_reporte.".xls","nombre_descarga"=>$nombre_reporte.".xls"],function($msn) use($email){
                                            $msn->from('erp@asopharma.com',"ERP ASOPHARMA");
                                            $msn->to(explode(",", $email));                                 

                                            $msn->subject("ALERTA ERP-ASOPHARMA REPORTE GENERADO");
                                    });
                        echo json_encode(["mensaje"=>"Tarea finalizada revisa tu correo electronico","respuesta"=>true,"direccion"=>$nombre_reporte.".xls"]);

                    }else{
                        echo json_encode(["mensaje"=>"Archivo exportado","respuesta"=>true,"direccion"=>$nombre_reporte.".xls","archivo_plano"=>$mi_archivo]);
                    }
                    
            }else{
                echo json_encode(["mensaje"=>"No hay datos para exportar","respuesta"=>false]);
            }
		
    }
}
