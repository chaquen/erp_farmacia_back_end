<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use Mail;

use DB;

use App\Reportes;

use Carbon\Carbon;

use DateTimeZone; 

use Jenssegers\Date\Date;

use File;

use Maatwebsite\Excel\Facades\Excel;

class PedidoAutomatizado extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pedido_automatizado';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Comando que genera pedidos automatizados';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //
        //$base_url="https://api.asopharma.com/";
        $base_url="https://apierpfarmacia.mohansoft.com/";
        $fecha=Carbon::now(new DateTimeZone('America/Bogota'));
        $anio=explode("-", $fecha)[0];

             
        $fecha_formato=Date::now(new DateTimeZone('America/Bogota'));
        $fecha2=$fecha_formato->format("l j F Y H:i");
        $fecha3=$fecha_formato->format("Y-m-d H:i:s");
       

         $rep=new Reportes();
        //$destinos="edgar.guzman21@gmail.com";
        $sede=DB::table("sedes")->get();
        $i=0;
         $tipo_separacion_archivo_plano=" ";
         $s="";
        foreach ($sede as $key => $value) {
            $s=$value->nombre_sede;
            $destinos=DB::table("notificaciones")
                ->where([["trabajo","=","PedidoDiario"],["fk_id_sede","=",$value->id]])
                ->select("correos")->get();

            $datos=(object)["datos"=>(object)[
                    "tipo"=>"SEDE",
                    
                    "sedes"=>$value->id,
                    
                ],
                "hora_cliente"=>$fecha3,
                
                ];
            //var_dump($datos);
            if(count($destinos)>0){
                $arr["reporte"]=(array)$rep->reporte_bajo_inventario($datos);
                $arr["sede"]=$value->nombre_sede;    

                if($arr["reporte"]["respuesta"]){
                     
                      //CREAR ARCHIVO TXT
                     /* $mi_archivo="pedidos".explode(" ",$fecha3)[0]."_AUTOMATICO.txt";
                      $ruta2_txt="https://api.asopharma.com/archivos/pedidos/txt/".$value->nombre_sede."_".$mi_archivo;
                      //$ruta2_txt="https://apierpfarmacia.mohansoft.com/archivos/pedidos/txt/".$mi_archivo."_".$value->nombre_sede;
                      $ruta_txt="../archivos/pedidos/txt/".$value->nombre_sede."_".$mi_archivo;
                        
                   
                        
                        $contenido="";
                        foreach ($arr["reporte"]["datos"] as $key => $value) {
                           
                           $contenido.=trim($value["id"])
                                       .$tipo_separacion_archivo_plano
                                       .trim($value["codigo_producto"])
                                       .$tipo_separacion_archivo_plano
                                       .trim($value["nombre_producto"])
                                       .$tipo_separacion_archivo_plano
                                        .trim($value["minimo_inventario"])
                                        .$tipo_separacion_archivo_plano
                                        .trim($value["codigo_distribuidor"]).PHP_EOL;//CONSTANTE SALTO DE LINEA SEGUN EL S.O
                                        
                            
                        }
                        //echo $datos->datos->tipo_separacion_archivo_plano;
                        
                        File::put($ruta_txt,$contenido);
                       */ 
                        

                    //CREAR ARCHIVO EXCEL
                    
                    $mi_archivo="pedidos".explode(" ",$fecha3)[0]."_AUTOMATICO_".$value->nombre_sede;
                    $ruta2_xls=$base_url."archivos/pedidos/xls/";
                    //$ruta2_xls="https://apierpfarmacia.mohansoft.com/archivos/pedidos/xls/";
                    $ruta2="../archivos/pedidos/xls/";
                  
                    $ruta_xls=$ruta2.$mi_archivo."_". $arr["sede"];
                    $arr_xls=[];
                    $i=0;
                    foreach ($arr["reporte"]["datos"] as $key => $value) {
                        //var_dump($value->codigo_distribuidor);
                        $arr_xls[$i]=["id"=>$value["id"],
                                "codigo_sede"=>$value["codigo_sede"],
                                "codigo_producto"=>$value["codigo_producto"],
                                "nombre_producto"=>$value["nombre_producto"],
                                "cantidad_solicitada"=>$value["minimo_inventario"],
                                "codigo_distribuidor"=>$value["codigo_distribuidor"],
                                ];  
                        $i++;
                    }
                    Excel::create($mi_archivo, function($excel) use($arr_xls){
                            
                            $excel->sheet('productos',function($sheet) use($arr_xls){
                                    
                                 
                               
                                    $sheet->fromArray($arr_xls);
                            });
                    })->store('xls',$ruta2 );
                 
                  $ruta_xls.=".xls";
                  $ruta2_xls.=$mi_archivo.".xls";





                    
                    
                        $sed= $arr["sede"];
                        Mail::send('email.pedido_diario',["datos_pedido"=>$arr,"fecha"=>$fecha2,"anio"=>$anio,"ruta_xls"=>$ruta2_xls,"sede"=>$s],function($m)use($destinos,$fecha2,$sed){
                            //var_dump($destinos[0]);
                               $m->from("erp@asopharma.com","ERP ASOPHARMA")
                               ->to(explode(",", $destinos[0]->correos))->subject($sed.", reporte pedido automatizado, ".$fecha2." para la sede ".$sed);
                           });    
                    
                    
                }   
            }
                 
               
           
            
        }
        
    }
}
