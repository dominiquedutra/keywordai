<?php

namespace App\Http\Controllers;

use App\Models\AiAnalysisLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AiAnalysisLogController extends Controller
{
    public function index(Request $request): View
    {
        $query = AiAnalysisLog::with('user')->orderBy('created_at', 'desc');

        $query->when($request->input('model'), fn($q, $v) => $q->where('model', $v));
        $query->when($request->input('source'), fn($q, $v) => $q->where('source', $v));

        if ($request->has('success') && $request->input('success') !== '') {
            $query->where('success', $request->boolean('success'));
        }

        if ($request->input('start_date')) {
            $query->whereDate('created_at', '>=', $request->input('start_date'));
        }
        if ($request->input('end_date')) {
            $query->whereDate('created_at', '<=', $request->input('end_date'));
        }

        $logs = $query->paginate(50);

        return view('ai_analysis_logs.index', [
            'logs' => $logs,
            'filters' => $request->only(['model', 'source', 'success', 'start_date', 'end_date']),
        ]);
    }

    public function show(AiAnalysisLog $aiAnalysisLog): View
    {
        $aiAnalysisLog->load('user');

        return view('ai_analysis_logs.show', [
            'log' => $aiAnalysisLog,
        ]);
    }
}
