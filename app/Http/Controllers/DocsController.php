<?php

namespace App\Http\Controllers;

class DocsController extends Controller
{
    public function sistemaIndex()
    {
        return view('docs.sistema.index');
    }

    public function batchStatsSync()
    {
        return view('docs.sistema.batch-stats-sync');
    }
}
