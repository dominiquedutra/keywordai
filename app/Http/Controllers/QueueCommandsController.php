<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Carbon\Carbon;
use Pheanstalk\Pheanstalk;

class QueueCommandsController extends Controller
{
    /**
     * Exibe a página principal de filas e comandos.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Obter estatísticas da fila (assumindo que estamos usando o driver database)
        $queueStats = $this->getQueueStats();
        
        // Lista de comandos disponíveis
        $availableCommands = [
            [
                'id' => 'sync-stats',
                'name' => 'Sincronizar Estatísticas de Termos',
                'description' => 'Enfileira jobs para sincronizar estatísticas de todos os termos de pesquisa em um intervalo de datas',
                'command' => 'keywordai:queue-sync-stats',
                'options' => [
                    [
                        'name' => 'start-date',
                        'type' => 'date',
                        'description' => 'Data inicial (YYYY-MM-DD)',
                        'required' => false,
                        'default' => Carbon::now()->subDays(7)->format('Y-m-d')
                    ],
                    [
                        'name' => 'end-date',
                        'type' => 'date',
                        'description' => 'Data final (YYYY-MM-DD)',
                        'required' => false,
                        'default' => Carbon::now()->format('Y-m-d')
                    ],
                    [
                        'name' => 'queue',
                        'type' => 'text',
                        'description' => 'Nome da fila',
                        'required' => false,
                        'default' => 'default'
                    ]
                ]
            ],
            [
                'id' => 'sync-all-stats',
                'name' => 'Sincronizar Estatísticas de Todos os Termos Ativos',
                'description' => 'Enfileira jobs para sincronizar estatísticas de todos os termos de pesquisa ativos (não excluídos)',
                'command' => 'keywordai:sync-all-active-stats',
                'options' => [
                    [
                        'name' => 'queue',
                        'type' => 'text',
                        'description' => 'Nome da fila',
                        'required' => false,
                        'default' => 'default'
                    ],
                    [
                        'name' => 'dry-run',
                        'type' => 'checkbox',
                        'description' => 'Apenas mostrar quantos termos seriam sincronizados',
                        'required' => false,
                        'default' => false
                    ]
                ]
            ]
        ];
        
        return view('queue_commands.index', compact('queueStats', 'availableCommands'));
    }
    
    /**
     * Executa um comando Artisan.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function executeCommand(Request $request)
    {
        $commandId = $request->input('command_id');
        $options = $request->except(['_token', 'command_id']);
        
        // Mapear o ID do comando para o comando real
        $commandMap = [
            'sync-stats' => 'keywordai:queue-sync-stats',
            'sync-all-stats' => 'keywordai:sync-all-active-stats',
        ];
        
        if (!isset($commandMap[$commandId])) {
            return redirect()->route('queue-commands.index')
                ->with('error', 'Comando inválido.');
        }
        
        $command = $commandMap[$commandId];
        
        // Construir os argumentos do comando
        $commandArgs = [];
        foreach ($options as $key => $value) {
            if ($value === 'on' || $value === '1' || $value === true) {
                // Checkbox/boolean flags: pass as true (no value)
                $commandArgs["--{$key}"] = true;
            } elseif (!empty($value) && $value !== '0') {
                $commandArgs["--{$key}"] = $value;
            }
        }
        
        try {
            // Executar o comando em background
            $exitCode = Artisan::call($command, $commandArgs);
            
            // Obter a saída do comando
            $output = Artisan::output();
            
            if ($exitCode === 0) {
                return redirect()->route('queue-commands.index')
                    ->with('success', 'Comando executado com sucesso: ' . $output);
            } else {
                return redirect()->route('queue-commands.index')
                    ->with('error', 'Erro ao executar o comando: ' . $output);
            }
        } catch (\Exception $e) {
            return redirect()->route('queue-commands.index')
                ->with('error', 'Erro ao executar o comando: ' . $e->getMessage());
        }
    }
    
    /**
     * Obtém estatísticas da fila.
     *
     * @return array
     */
    private function getQueueStats()
    {
        $stats = [
            'total' => 0,
            'ready' => 0,
            'reserved' => 0,
            'delayed' => 0,
            'buried' => 0,
            'failed' => 0,
            'by_queue' => [],
            'driver' => config('queue.default')
        ];
        
        $driver = config('queue.default');
        
        // Verificar qual driver estamos usando
        if ($driver === 'database') {
            // Contar jobs na tabela jobs
            $stats['total'] = DB::table('jobs')->count();
            $stats['ready'] = DB::table('jobs')->where('reserved_at', null)->count();
            $stats['reserved'] = DB::table('jobs')->where('reserved_at', '!=', null)->count();
            
            // Contar jobs por fila
            $queueCounts = DB::table('jobs')
                ->select('queue', DB::raw('count(*) as total'))
                ->groupBy('queue')
                ->get();
            
            foreach ($queueCounts as $queueCount) {
                $stats['by_queue'][$queueCount->queue] = [
                    'total' => $queueCount->total,
                    'ready' => DB::table('jobs')->where('queue', $queueCount->queue)->where('reserved_at', null)->count(),
                    'reserved' => DB::table('jobs')->where('queue', $queueCount->queue)->where('reserved_at', '!=', null)->count()
                ];
            }
            
            // Contar jobs falhos
            $stats['failed'] = DB::table('failed_jobs')->count();
            
        } elseif ($driver === 'beanstalkd' || $driver === 'redis' || $driver === 'sqs' || $driver === 'sync') {
            // Para todos os outros drivers, usar Queue::size()
            try {
                // Obter tamanho da fila padrão
                $defaultQueueSize = Queue::size();
                $stats['by_queue']['default'] = [
                    'total' => $defaultQueueSize,
                    'ready' => $defaultQueueSize,
                    'reserved' => 0,
                    'delayed' => 0,
                    'buried' => 0
                ];
                $stats['total'] = $defaultQueueSize;
                $stats['ready'] = $defaultQueueSize; // Assumimos que todos estão prontos
                
                // Tentar obter contagem de jobs falhos
                try {
                    $stats['failed'] = DB::table('failed_jobs')->count();
                } catch (\Exception $e) {
                    $stats['failed'] = 0;
                }
                
                // Adicionar mensagem informativa
                $stats['message'] = "Estatísticas limitadas disponíveis para o driver '{$driver}'. Usando Queue::size().";
            } catch (\Exception $e) {
                $stats['error'] = 'Erro ao obter estatísticas da fila: ' . $e->getMessage();
            }
            
        } else {
            // Para outros drivers, usar Queue::size()
            try {
                // Obter tamanho da fila padrão
                $defaultQueueSize = Queue::size();
                $stats['by_queue']['default'] = [
                    'total' => $defaultQueueSize
                ];
                $stats['total'] = $defaultQueueSize;
                $stats['ready'] = $defaultQueueSize; // Assumimos que todos estão prontos
                
                // Tentar obter contagem de jobs falhos
                try {
                    $stats['failed'] = DB::table('failed_jobs')->count();
                } catch (\Exception $e) {
                    $stats['failed'] = 0;
                }
                
                // Adicionar mensagem informativa
                $stats['message'] = "Estatísticas limitadas disponíveis para o driver '{$driver}'. Usando Queue::size().";
            } catch (\Exception $e) {
                $stats['error'] = 'Erro ao obter estatísticas da fila: ' . $e->getMessage();
            }
        }
        
        return $stats;
    }
}
