<?php

namespace App\Http\Controllers;

use App\Models\SystemLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SystemLogController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', SystemLog::class);
        $request->validate(['date_from' => ['nullable', 'date'], 'date_to' => ['nullable', 'date', 'after_or_equal:date_from'], 'user_id' => ['nullable', 'uuid', 'exists:users,id'], 'action' => ['nullable', 'string', 'max:50']]);
        $logs = SystemLog::query()->with('user')->when($request->filled('date_from'), fn ($query) => $query->whereDate('created_at', '>=', $request->date('date_from')))->when($request->filled('date_to'), fn ($query) => $query->whereDate('created_at', '<=', $request->date('date_to')))->when($request->filled('user_id'), fn ($query) => $query->where('user_id', $request->string('user_id')))->when($request->filled('action'), fn ($query) => $query->where('action', $request->string('action')))->latest()->paginate(25)->withQueryString();

        return view('system-logs.index', ['logs' => $logs, 'users' => User::query()->orderBy('name')->get(['id', 'name', 'email'])]);
    }
}
