<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use DB;

class LimpiarProductosReservados extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'limpiar_productos_reservados';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Comando para limpiar volver a cero las unidades reservadas al final del dia';

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
            DB::table("detalle_inventarios")
            ->where("unidades_reservadas",">","0")
             ->update(["unidades_reservadas"=>"0"]);   
    }
}
