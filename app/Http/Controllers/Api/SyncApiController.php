<?php

namespace App\Http\Controllers\Api;

use App\Jobs\SyncAdsEntitiesJob;
use App\Jobs\SyncSearchTermsForDateJob;
use App\Models\SearchTermSyncDate;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;

class SyncApiController extends BaseApiController
{
    /**
     * Sincronizar termos de pesquisa para uma data específica.
     */
    public function syncSearchTerms(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'date' => 'required|date_format:Y-m-d',
        ]);

        $date = Carbon::createFromFormat('Y-m-d', $validated['date']);

        try {
            $job = SyncSearchTermsForDateJob::dispatch($date);

            return $this->successResponse([
                'job_id' => $job,
                'date' => $validated['date'],
                'message' => 'Sincronização de termos de pesquisa iniciada.',
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao iniciar sincronização: ' . $e->getMessage());
            return $this->errorResponse('Erro ao iniciar sincronização: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Sincronizar termos de pesquisa para um range de datas.
     */
    public function syncSearchTermsRange(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'date_from' => 'required|date_format:Y-m-d',
            'date_to' => 'required|date_format:Y-m-d|after_or_equal:date_from',
        ]);

        $startDate = Carbon::createFromFormat('Y-m-d', $validated['date_from']);
        $endDate = Carbon::createFromFormat('Y-m-d', $validated['date_to']);

        $jobs = [];
        $currentDate = $endDate->copy();

        while ($currentDate >= $startDate) {
            $jobs[] = new SyncSearchTermsForDateJob($currentDate->copy());
            $currentDate->subDay();
        }

        try {
            Bus::batch($jobs)
                ->onQueue('default')
                ->dispatch();

            return $this->successResponse([
                'dates_count' => count($jobs),
                'date_from' => $validated['date_from'],
                'date_to' => $validated['date_to'],
                'message' => count($jobs) . ' job(s) de sincronização criado(s).',
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao criar batch de sincronização: ' . $e->getMessage());
            return $this->errorResponse('Erro ao criar batch: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Sincronizar campanhas e grupos de anúncios.
     */
    public function syncEntities(): JsonResponse
    {
        try {
            $job = SyncAdsEntitiesJob::dispatch();

            return $this->successResponse([
                'job_id' => $job,
                'message' => 'Sincronização de entidades iniciada.',
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao sincronizar entidades: ' . $e->getMessage());
            return $this->errorResponse('Erro ao sincronizar entidades: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Obter status das sincronizações.
     */
    public function syncStatus(Request $request): JsonResponse
    {
        $query = SearchTermSyncDate::query();

        if ($request->filled('date_from')) {
            $query->whereDate('sync_date', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('sync_date', '<=', $request->input('date_to'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $perPage = $request->input('per_page', 30);
        $perPage = min(max((int) $perPage, 1), 100);

        $statuses = $query->orderByDesc('sync_date')->paginate($perPage);

        $data = [
            'data' => $statuses->map(function ($item) {
                return [
                    'id' => $item->id,
                    'sync_date' => $item->sync_date->format('Y-m-d'),
                    'status' => $item->status,
                    'attempts' => $item->attempts,
                    'error_message' => $item->error_message,
                    'job_id' => $item->job_id,
                    'started_at' => $item->started_at?->format('Y-m-d H:i:s'),
                    'completed_at' => $item->completed_at?->format('Y-m-d H:i:s'),
                    'created_at' => $item->created_at->format('Y-m-d H:i:s'),
                ];
            }),
            'pagination' => [
                'current_page' => $statuses->currentPage(),
                'last_page' => $statuses->lastPage(),
                'per_page' => $statuses->perPage(),
                'total' => $statuses->total(),
            ],
            'summary' => [
                'pending' => SearchTermSyncDate::where('status', 'pending')->count(),
                'processing' => SearchTermSyncDate::where('status', 'processing')->count(),
                'completed' => SearchTermSyncDate::where('status', 'completed')->count(),
                'failed' => SearchTermSyncDate::where('status', 'failed')->count(),
            ],
        ];

        return $this->successResponse($data);
    }

    /**
     * Obter status das filas.
     */
    public function queueStatus(): JsonResponse
    {
        try {
            $connection = config('queue.default');
            $queueName = 'default';

            // Tentar obter tamanho da fila
            $pendingJobs = \DB::table('jobs')->count();
            $failedJobs = \DB::table('failed_jobs')->count();

            $stats = [
                'connection' => $connection,
                'pending_jobs' => $pendingJobs,
                'failed_jobs' => $failedJobs,
                'recent_jobs' => \DB::table('jobs')
                    ->orderBy('id', 'desc')
                    ->limit(10)
                    ->get(['id', 'queue', 'attempts', 'created_at']),
            ];

            return $this->successResponse($stats);
        } catch (\Exception $e) {
            return $this->errorResponse('Erro ao obter status das filas: ' . $e->getMessage(), 500);
        }
    }
}
