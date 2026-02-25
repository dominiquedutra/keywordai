<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

class ApiDocsController extends Controller
{
    /**
     * Display the public API documentation.
     */
    public function index()
    {
        return view('api.docs.index');
    }
}
