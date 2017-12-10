<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use Mail;

class SaludoNotificacionCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'SaludoNotificacionCommand';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        ini_set('date.timezone','America/Bogota'); 
         $fecha=date("Y-m-d H:i:s");
         Mail::send('email.saludo',["hora"=>$fecha],function($m){
               $m->from("erp@asopharma.com","ERP ASOPHARMA")
               ->to("edgar.guzman21@gmail.com")->subject("SALUDO PEZ ");
           });
    }
}
