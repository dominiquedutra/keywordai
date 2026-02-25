<?php

namespace App\Http\Controllers;

use App\Models\SearchTerm;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Retorna os dados para o gráfico de novos termos por dia.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getNewTermsChartData(Request $request)
    {
        // Validar os parâmetros de entrada
        $request->validate([
            'month' => 'nullable|integer|min:1|max:12',
            'year' => 'nullable|integer|min:2000|max:2100',
        ]);

        // Se não forem fornecidos, usar o mês e ano atuais
        $month = $request->input('month', Carbon::now()->month);
        $year = $request->input('year', Carbon::now()->year);

        // Criar datas de início e fim para o mês selecionado
        $startDate = Carbon::createFromDate($year, $month, 1)->startOfDay();
        $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth()->endOfDay();

        // Buscar os dados agrupados por dia e status
        $data = SearchTerm::select(
                DB::raw('DATE(first_seen_at) as date'),
                'status',
                DB::raw('COUNT(*) as count')
            )
            ->whereBetween('first_seen_at', [$startDate, $endDate])
            ->groupBy(DB::raw('DATE(first_seen_at)'), 'status')
            ->orderBy(DB::raw('DATE(first_seen_at)'))
            ->get();

        // Preparar os dados para o gráfico
        $chartData = $this->prepareChartData($data, $startDate, $endDate);

        return response()->json($chartData);
    }

    /**
     * Prepara os dados para o formato esperado pelo Chart.js.
     *
     * @param  \Illuminate\Support\Collection  $data
     * @param  \Carbon\Carbon  $startDate
     * @param  \Carbon\Carbon  $endDate
     * @return array
     */
    private function prepareChartData($data, $startDate, $endDate)
    {
        // Criar um array com todos os dias do mês
        $daysInMonth = $endDate->day;
        $labels = [];
        $datasets = [
            'NONE' => array_fill(0, $daysInMonth, 0),
            'EXCLUDED' => array_fill(0, $daysInMonth, 0),
            'ADDED' => array_fill(0, $daysInMonth, 0),
            'ADDED_EXCLUDED' => array_fill(0, $daysInMonth, 0),
        ];

        // Preencher os labels com os dias do mês
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $labels[] = $day;
        }

        // Preencher os datasets com os dados reais
        foreach ($data as $item) {
            $date = Carbon::parse($item->date);
            $day = $date->day - 1; // Índice 0-based
            $status = $item->status;
            
            // Garantir que o status existe no array de datasets
            if (isset($datasets[$status])) {
                $datasets[$status][$day] = $item->count;
            }
        }

        // Formatar os dados para o Chart.js
        $formattedDatasets = [
            [
                'label' => 'Nenhum (NONE)',
                'data' => $datasets['NONE'],
                'backgroundColor' => 'rgba(128, 128, 128, 0.7)', // Cinza
                'borderColor' => 'rgba(128, 128, 128, 1)',
                'borderWidth' => 1
            ],
            [
                'label' => 'Excluído (EXCLUDED)',
                'data' => $datasets['EXCLUDED'],
                'backgroundColor' => 'rgba(220, 53, 69, 0.7)', // Vermelho
                'borderColor' => 'rgba(220, 53, 69, 1)',
                'borderWidth' => 1
            ],
            [
                'label' => 'Adicionado (ADDED)',
                'data' => $datasets['ADDED'],
                'backgroundColor' => 'rgba(40, 167, 69, 0.7)', // Verde
                'borderColor' => 'rgba(40, 167, 69, 1)',
                'borderWidth' => 1
            ],
            [
                'label' => 'Adicionado e Excluído (ADDED_EXCLUDED)',
                'data' => $datasets['ADDED_EXCLUDED'],
                'backgroundColor' => 'rgba(255, 193, 7, 0.7)', // Amarelo
                'borderColor' => 'rgba(255, 193, 7, 1)',
                'borderWidth' => 1
            ]
        ];

        return [
            'labels' => $labels,
            'datasets' => $formattedDatasets,
            'month' => $startDate->format('m'),
            'year' => $startDate->format('Y'),
            'monthName' => $startDate->translatedFormat('F'),
        ];
    }
}
