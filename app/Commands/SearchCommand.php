<?php

namespace App\Commands;

use Exception;
use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use App\Models\ClientDatabase;
use App\Resources\Encryption;
use PDO;

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
        #$consulta = "SELECT MAX(id_permissao) FROM sis_permissoes";
        #$consulta = "SELECT * FROM tbconcursos LIMIT 3";
        $consulta = "SELECT MAX(id_tipocampo) FROM tbformularios_campos_tipos";

        $time_start = microtime(true);
        $this->comment('[' . date('H:i:s') . '] Iniciando consulta. Aguarde...');
        $this->line($consulta);
        $this->line('');

        try {
            $this->warn('Consultando db superadmin');
            $databases = new ClientDatabase();
            $databases = $databases->limit(3)->get();

            #$databases = DB::table('client_databases')->limit(3)->pluck('name');
        } catch (Exception $e) {
            $mensagem = "Erro ao selecionar databases: " . $e->getMessage();
            $this->error($mensagem);
            die;
        }

        foreach ($databases as $database) {
            $this->line('Consulta ' . $database->name);

            DB::purge('mysql');
            $config = Config::get('database.connections.mysql');
            $config['host'] = env('DB_HOST');
            $config['database'] = $database->name;
            $config['username'] = env('DB_USERNAME');
            $config['password'] = env('DB_PASSWORD');
            $config['charset'] = $database->charset;
            $config['collation'] = $database->collation;
            Config::set('database.connections.mysql', $config);
            DB::reconnect('mysql');

            try {
                $resultados = DB::select($consulta);

                #$this->comment('[' . date('H:i:s') . '] Resultado database ' . $database);
                foreach ($resultados as $linha) {

                    $linha = json_encode($linha);
                    $this->line($linha);
                    $this->line('');
                }
            } catch (Exception $e) {
                $mensagem = "Erro ao executar função. Database: $database ->  " . $e->getMessage();
                $this->error($mensagem);
                die;
            }
        }

        $time_end = microtime(true);
        $time = round($time_end - $time_start, 3);

        $this->comment('[' . date('H:i:s') . '] Finalizado.');
        echo "Executado em " .  $time . "s";
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
