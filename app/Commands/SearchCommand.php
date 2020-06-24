<?php

namespace App\Commands;

use Exception;
use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class SearchCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    #protected $signature = 'command:name';
    protected $signature = 'search';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Faz uma busca nos bancos de dados do servidor escolhido';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {


        $consulta = "SELECT MAX(id_permissao) FROM sis_permissoes";

        $this->comment('[' . date('H:i:s') . '] Iniciando...');
        $this->line($consulta);

        $databases = DB::table('client_databases')->limit(10)->get();


        foreach ($databases as $database) {
            DB::purge('mysql');

            $config = Config::get('database.connections.mysql');
            $config['database'] = $database->name;
            Config::set('database.connections.mysql', $config);

            DB::reconnect('mysql');
            $this->line('Reconectou');

            try {
                $resultados = DB::select($consulta);
                $this->line('Consultou');

                $this->comment($database->name);
                foreach ($resultados as $resultado) {
                    $resultado = array_values(get_object_vars($resultado));
                    #dd($resultado, $resultado[0]);
                    $this->line($resultado[0]);
                }
            } catch (Exception $e) {
                $mensagem = "Erro ao executar função. Database: $database->name ->  " . $e->getMessage();
                $this->error($mensagem);
            }
        }
        $this->comment('[' . date('H:i:s') . '] Finalizado.');
    }

    /**
     * Define the command's schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->everyMinute();
    }
}
