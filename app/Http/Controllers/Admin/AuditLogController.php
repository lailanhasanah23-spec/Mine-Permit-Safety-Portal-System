<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Legacy\LegacyAuth;
use App\Support\Legacy\LegacyRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $filters = [
            'action' => trim((string) $request->query('action', '')),
            'entity_type' => trim((string) $request->query('entity_type', '')),
            'q' => trim((string) $request->query('q', '')),
        ];

        $admin = LegacyAuth::user();
        $userRole = DB::table('users')->where('id', $admin['id'] ?? 0)->value('role') ?? 'admin';

        return view('admin.audit-log', [
            'admin' => $admin,
            'userRole' => $userRole,
            'filters' => $filters,
            'options' => LegacyRepository::adminGetAuditFilterOptions(),
            'logs' => LegacyRepository::adminGetAuditLogs($filters, 200),
        ]);
    }
}
