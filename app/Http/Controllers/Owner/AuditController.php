<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditController extends Controller
{
    public function index(Request $request): View
    {
        $logs = AuditLog::with('user')
            ->when($request->filled('action'), fn ($query) => $query->where('action', $request->string('action')))
            ->latest('created_at')
            ->paginate(25)
            ->withQueryString()
            ->through(fn (AuditLog $log) => (object) [
                'id' => $log->audit_id,
                'action' => $log->action,
                'description' => $log->description,
                'by' => $log->user->full_name ?? '—',
                'created_at' => $log->created_at,
            ]);

        return view('owner.audit.index', compact('logs'));
    }
}
