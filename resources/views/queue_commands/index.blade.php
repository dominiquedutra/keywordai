@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold mb-6 text-gray-900 dark:text-white">Fila e Comandos</h1>

    <!-- Mensagens de Feedback -->
    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    @if (session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Estatísticas da Fila -->
        <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
            <h2 class="text-xl font-semibold mb-4 text-gray-900 dark:text-white">Estatísticas da Fila</h2>
            
            @if (isset($queueStats['error']))
                <div class="text-red-600 dark:text-red-400 mb-4">
                    {{ $queueStats['error'] }}
                </div>
            @endif
            
            @if (isset($queueStats['message']))
                <div class="text-yellow-600 dark:text-yellow-400 mb-4">
                    {{ $queueStats['message'] }}
                </div>
            @endif
            
            <div class="text-sm text-gray-600 dark:text-gray-300 mb-4">
                Driver atual: <span class="font-semibold">{{ $queueStats['driver'] }}</span>
            </div>
            
            <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-{{ $queueStats['driver'] === 'beanstalkd' ? '3' : '2' }} gap-4 mb-6">
                <div class="bg-blue-50 dark:bg-blue-900 p-4 rounded-lg">
                    <div class="text-sm text-blue-600 dark:text-blue-300">Total de Jobs</div>
                    <div class="text-2xl font-bold text-blue-700 dark:text-blue-200">{{ $queueStats['total'] }}</div>
                </div>
                <div class="bg-green-50 dark:bg-green-900 p-4 rounded-lg">
                    <div class="text-sm text-green-600 dark:text-green-300">Jobs Prontos</div>
                    <div class="text-2xl font-bold text-green-700 dark:text-green-200">{{ $queueStats['ready'] }}</div>
                </div>
                <div class="bg-yellow-50 dark:bg-yellow-900 p-4 rounded-lg">
                    <div class="text-sm text-yellow-600 dark:text-yellow-300">Jobs Reservados</div>
                    <div class="text-2xl font-bold text-yellow-700 dark:text-yellow-200">{{ $queueStats['reserved'] }}</div>
                </div>
                
                @if ($queueStats['driver'] === 'beanstalkd')
                    <div class="bg-purple-50 dark:bg-purple-900 p-4 rounded-lg">
                        <div class="text-sm text-purple-600 dark:text-purple-300">Jobs Adiados</div>
                        <div class="text-2xl font-bold text-purple-700 dark:text-purple-200">{{ $queueStats['delayed'] }}</div>
                    </div>
                    <div class="bg-orange-50 dark:bg-orange-900 p-4 rounded-lg">
                        <div class="text-sm text-orange-600 dark:text-orange-300">Jobs Enterrados</div>
                        <div class="text-2xl font-bold text-orange-700 dark:text-orange-200">{{ $queueStats['buried'] }}</div>
                    </div>
                @endif
                
                <div class="bg-red-50 dark:bg-red-900 p-4 rounded-lg">
                    <div class="text-sm text-red-600 dark:text-red-300">Jobs Falhos</div>
                    <div class="text-2xl font-bold text-red-700 dark:text-red-200">{{ $queueStats['failed'] }}</div>
                </div>
            </div>

            @if (count($queueStats['by_queue']) > 0)
                <h3 class="text-lg font-semibold mb-2 text-gray-900 dark:text-white">Jobs por Fila</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white dark:bg-gray-700 rounded-lg overflow-hidden">
                        <thead>
                            <tr class="bg-gray-100 dark:bg-gray-600">
                                <th class="py-2 px-4 border-b border-gray-200 dark:border-gray-500 text-left text-xs font-semibold text-gray-600 dark:text-gray-200 uppercase tracking-wider">
                                    Fila
                                </th>
                                <th class="py-2 px-4 border-b border-gray-200 dark:border-gray-500 text-left text-xs font-semibold text-gray-600 dark:text-gray-200 uppercase tracking-wider">
                                    Total
                                </th>
                                
                                @if ($queueStats['driver'] === 'database' || $queueStats['driver'] === 'beanstalkd')
                                    <th class="py-2 px-4 border-b border-gray-200 dark:border-gray-500 text-left text-xs font-semibold text-gray-600 dark:text-gray-200 uppercase tracking-wider">
                                        Prontos
                                    </th>
                                    <th class="py-2 px-4 border-b border-gray-200 dark:border-gray-500 text-left text-xs font-semibold text-gray-600 dark:text-gray-200 uppercase tracking-wider">
                                        Reservados
                                    </th>
                                @endif
                                
                                @if ($queueStats['driver'] === 'beanstalkd')
                                    <th class="py-2 px-4 border-b border-gray-200 dark:border-gray-500 text-left text-xs font-semibold text-gray-600 dark:text-gray-200 uppercase tracking-wider">
                                        Adiados
                                    </th>
                                    <th class="py-2 px-4 border-b border-gray-200 dark:border-gray-500 text-left text-xs font-semibold text-gray-600 dark:text-gray-200 uppercase tracking-wider">
                                        Enterrados
                                    </th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($queueStats['by_queue'] as $queue => $stats)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-600">
                                    <td class="py-2 px-4 border-b border-gray-200 dark:border-gray-500 text-gray-900 dark:text-gray-200">{{ $queue }}</td>
                                    
                                    @if (is_array($stats))
                                        <td class="py-2 px-4 border-b border-gray-200 dark:border-gray-500 text-gray-900 dark:text-gray-200">{{ $stats['total'] }}</td>
                                        
                                        @if ($queueStats['driver'] === 'database' || $queueStats['driver'] === 'beanstalkd')
                                            <td class="py-2 px-4 border-b border-gray-200 dark:border-gray-500 text-gray-900 dark:text-gray-200">{{ $stats['ready'] ?? 0 }}</td>
                                            <td class="py-2 px-4 border-b border-gray-200 dark:border-gray-500 text-gray-900 dark:text-gray-200">{{ $stats['reserved'] ?? 0 }}</td>
                                        @endif
                                        
                                        @if ($queueStats['driver'] === 'beanstalkd')
                                            <td class="py-2 px-4 border-b border-gray-200 dark:border-gray-500 text-gray-900 dark:text-gray-200">{{ $stats['delayed'] ?? 0 }}</td>
                                            <td class="py-2 px-4 border-b border-gray-200 dark:border-gray-500 text-gray-900 dark:text-gray-200">{{ $stats['buried'] ?? 0 }}</td>
                                        @endif
                                    @else
                                        <td class="py-2 px-4 border-b border-gray-200 dark:border-gray-500 text-gray-900 dark:text-gray-200">{{ $stats }}</td>
                                        
                                        @if ($queueStats['driver'] === 'database' || $queueStats['driver'] === 'beanstalkd')
                                            <td class="py-2 px-4 border-b border-gray-200 dark:border-gray-500 text-gray-900 dark:text-gray-200">-</td>
                                            <td class="py-2 px-4 border-b border-gray-200 dark:border-gray-500 text-gray-900 dark:text-gray-200">-</td>
                                        @endif
                                        
                                        @if ($queueStats['driver'] === 'beanstalkd')
                                            <td class="py-2 px-4 border-b border-gray-200 dark:border-gray-500 text-gray-900 dark:text-gray-200">-</td>
                                            <td class="py-2 px-4 border-b border-gray-200 dark:border-gray-500 text-gray-900 dark:text-gray-200">-</td>
                                        @endif
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <!-- Comandos Disponíveis -->
        <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
            <h2 class="text-xl font-semibold mb-4 text-gray-900 dark:text-white">Comandos Disponíveis</h2>
            
            @foreach ($availableCommands as $command)
                <div class="mb-6 p-4 border border-gray-200 dark:border-gray-600 rounded-lg">
                    <h3 class="text-lg font-semibold mb-2 text-gray-900 dark:text-white">{{ $command['name'] }}</h3>
                    <p class="text-gray-600 dark:text-gray-300 mb-4">{{ $command['description'] }}</p>
                    
                    <form action="{{ route('queue-commands.execute') }}" method="POST">
                        @csrf
                        <input type="hidden" name="command_id" value="{{ $command['id'] }}">
                        
                        @foreach ($command['options'] as $option)
                            <div class="mb-4">
                                <label for="{{ $option['name'] }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    {{ $option['description'] }}
                                    @if ($option['required'])
                                        <span class="text-red-500">*</span>
                                    @endif
                                </label>
                                
                                @if ($option['type'] === 'date')
                                    <input type="date" 
                                           id="{{ $option['name'] }}" 
                                           name="{{ $option['name'] }}" 
                                           value="{{ $option['default'] ?? '' }}"
                                           class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                           @if ($option['required']) required @endif>
                                @elseif ($option['type'] === 'text')
                                    <input type="text" 
                                           id="{{ $option['name'] }}" 
                                           name="{{ $option['name'] }}" 
                                           value="{{ $option['default'] ?? '' }}"
                                           class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                           @if ($option['required']) required @endif>
                                @endif
                            </div>
                        @endforeach
                        
                        <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Executar Comando
                        </button>
                    </form>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
