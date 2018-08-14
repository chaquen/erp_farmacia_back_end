<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use DB;

use Mail;

use App\Reportes;

use Carbon\Carbon;

use DateTimeZone; 

use Jenssegers\Date\Date;

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
        $fecha=Carbon::now(new DateTimeZone('America/Bogota'));
        $anio=explode("-", $fecha)[0];

             
        $fecha_formato=Date::now(new DateTimeZone('America/Bogota'));
        $fecha2=$fecha_formato->format("l j F Y H:i");
        $fecha3=$fecha_formato->format("Y-m-d H:i:s");

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
            //var_dump($datos);
            
            $arr[$i]["reporte"]=(array)$rep->reporte_corte_diario($datos);
            $arr[$i]["sede"]=$value->nombre_sede;    

            $i++;
            Mail::send('email.corte_diario',["datos_corte"=>$arr,"fecha"=>$fecha2,"anio"=>$anio],function($m)use($destinos,$fecha2,$sede){
            //var_dump($destinos[0]);
               $m->from("erp@asopharma.com","ERP ASOPHARMA")
               ->to(explode(",", $destinos[0]->correos))->subject($sede.", reporte "." corte  del dia, ".$fecha2."");
           });
            $i=0;

        }
        //var_dump($destinos[0]->correos);
        //var_dump(explode(",", $destinos[0]->correos));
        //var_dump($arr);
        
    	
    }
}
