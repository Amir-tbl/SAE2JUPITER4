<?php

namespace App\Http\Controllers;

use App\Models\Log;
use Illuminate\View\View;

class LogController extends BaseController
{
    public function index(): View
    {
        $logs = Log::with(['author', 'order'])->orderByDesc('created_at')->limit(200)->get();

        $totalLogs = Log::count();
        $todayLogs = Log::whereDate('created_at', today())->count();
        $types = Log::select('type')->distinct()->pluck('type')->filter()->values();

        return view('logs.index', compact('logs', 'totalLogs', 'todayLogs', 'types'));
    }
}
