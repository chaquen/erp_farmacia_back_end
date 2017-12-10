<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        // Commands\Inspire::class,
        Commands\CorteDiario::class,
        Commands\PedidoAutomatizado::class,   
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')
        //          ->hourly();
        $schedule->command('notificacion:corte_diario')
                    ->weekdays()
                    ->dailyAt('12:24')                 
                    ->timezone('America/Bogota');

         $schedule->command('notificacion:corte_diario')
                    ->weekdays()
                    ->dailyAt('18:00')                 
                    ->timezone('America/Bogota');
         
        $schedule->command('notificacion:corte_diario')
                    ->weekdays()
                    ->dailyAt('22:20')                 
                    ->timezone('America/Bogota');

        $schedule->command('pedido_automatizado')
                    ->weekdays()
                    ->dailyAt('22:21')                 
                    ->timezone('America/Bogota');  
                    
        $schedule->command('limpiar_productos_reservados')
                    ->weekdays()
                    ->dailyAt('22:19')                 
                    ->timezone('America/Bogota');            
          

        
    }
}
