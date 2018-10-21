<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use DB;

use Mail;

use App\Reportes;

use Carbon\Carbon;

use DateTimeZone; 

use Jenssegers\Date\Date;

use Maatwebsite\Excel\Facades\Excel;

class CorteDiario extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notificacion:corte_diario';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Este comando ejecuta una consulta para realizar el corte del dia';

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
         //ini_set('date.timezone','America/Bogota'); 
         //$fecha=date("Y-m-d H:i:s");
        //$url="https://api.asopharma.com/";
        //$url="localhost/erp_farmacia_back_end/";
        $URL="https://apierpfarmacia.mohansoft.com/";
        $fecha=Carbon::now(new DateTimeZone('America/Bogota'));
        $anio=explode("-", $fecha)[0];

             
        $fecha_formato=Date::now(new DateTimeZone('America/Bogota'));
        $fecha2=$fecha_formato->format("l j F Y H:i");
        $fecha3=$fecha_formato->format("Y-m-d H:i:s");
        //var_dump($fecha3);    
        $rep=new Reportes();
        //consultar correo de los administradores
        //consultar la hora y fecha del sistema
      
        
       
        //$destinos="edgar.guzman21@gmail.com";
        $sede=DB::table("sedes")->get();
        $i=0;
        foreach ($sede as $key => $value) {
            //var_dump($value);
            $destinos=DB::table("notificaciones")
                ->where([["trabajo","=","CorteDiario"],["fk_id_sede",$value->id]])
                ->select("correos")->get();

            $datos=(object)["datos"=>(object)[
                    "tipo"=>"SEDE",
                    "filtro"=>[
                        ["detalle_entrada_contables.fecha_entrada",">=",explode(" ",$fecha3)[0]." 00:00:00"],
                        ["detalle_entrada_contables.fecha_entrada","<=",$fecha3]
                    ],
                    "sede"=>$value->id,
                    "fecha"=>explode(" ",$fecha3)[0],
                ],
                "hora_cliente"=>$fecha3,
                
                ];
            //consulta para obtener los productos vendidos en el corte solo se debe hacer al final del dia
            $adjunto=false;    
            $ff=explode(":",explode(" ",  $fecha3)[1]);
            //var_dump(explode(" ",$fecha3));
            //echo explode(" ",$fecha3)[0]." 00:00:00</br>";
            //echo explode(" ",$fecha3)[0]." 23:59:59";
            $ruta_Archivo="";
            if($ff[0].":"=="13:"){
                //datos pendientes
                $datos_venta=(object)["datos"=>(object)[
                    "tipo"=>"SEDE",
                    "filtro"=>[
                        ["sedes.id","=",$value->id],
                        ["facturas.registro_factura",">=",explode(" ",$fecha3)[0]." 00:00:00"],
                        ["facturas.registro_factura","<=",explode(" ",$fecha3)[0]." 23:59:59"]
                    ],
                    "sede"=>$value->id,
                    "fecha"=>explode(" ",$fecha3)[0]
                ],
                "hora_cliente"=>$fecha3];    
                $dd=$rep->reporte_ventas_por_periodo($datos_venta);
                
                //crear archivo excel para enviar
                if(isset($dd["datos"])){
                        Excel::create("vendidos_hasta_la_fecha_".$value->nombre_sede."_".explode(" ",$fecha3)[0], function($excel) use($dd){
                                //var_dump(gettype($dd));                                                                   
                                    $excel->sheet('vendidos',function($sheet) use($dd){
                                        if(isset($dd["datos"])){
                                            $sheet->fromArray($dd["datos"]);
                                        }
                                        
                                    });
                         })->store('xls', substr(base_path(),0,-8)."archivos/exportacion/excel");


                        $adjunto=true;
                        $ruta_Archivo=$url."archivos/exportacion/excel/vendidos_hasta_la_fecha_".$value->nombre_sede."_".explode(" ",$fecha3)[0].".xls";
                }else{
                    $ruta_Archivo="";
                }
                
            }    
            


            

            //var_dump($datos);
            
            $arr[$i]["reporte"]=(array)$rep->reporte_corte_diario($datos);
            $arr[$i]["sede"]=$value->nombre_sede;    
           

            $i++;
            //var_dump(explode(",",$destinos[0]->correos));
            //var_dump($sede);
            //var_dump($fecha2);
            var_dump($ruta_Archivo);
            Mail::send('email.corte_diario',["datos_corte"=>$arr,"fecha"=>$fecha2,"anio"=>$anio,"ruta_Archivo"=>$ruta_Archivo],function($m)use($destinos,$fecha2,$value){
                //
               
               $m->from("erp@asopharma.com","ERP ASOPHARMA")
               
               ->to(explode(",", $destinos[0]->correos))->subject($value->nombre_sede.", reporte "." corte  del dia, ".$fecha2."");
           });
            $i=0;

        }
        //var_dump($destinos[0]->correos);
        //var_dump(explode(",", $destinos[0]->correos));
        //var_dump($arr);
        
    	
    }
}
