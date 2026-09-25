<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CronLog;
use App\Models\User;
use Illuminate\View\View;

class CronLogController extends Controller
{
    public function index(): View
    {
        $this->authorize('manage', User::class);

        return view('admin.cron-logs.index', ['cronLogs' => CronLog::latest('started_at')->get()]);
    }
}
