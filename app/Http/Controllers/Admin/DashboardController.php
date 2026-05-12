<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Submission;
use App\Support\Legacy\LegacyAuth;
use App\Support\Legacy\LegacyRepository;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $simperStats = [
            'total' => Submission::count(),
            'pending' => Submission::whereNotIn('status', ['approved', 'rejected'])->count(),
            'approved' => Submission::where('status', 'approved')->count(),
            'recent' => Submission::orderBy('created_at', 'desc')->take(5)->get(),
        ];

        $admin = LegacyAuth::user();
        $userRole = DB::table('users')->where('id', $admin['id'] ?? 0)->value('role') ?? 'admin';

        return view('admin.dashboard', [
            'admin' => $admin,
            'userRole' => $userRole,
            'stats' => LegacyRepository::adminGetDashboardStats(),
            'dailyTrend' => LegacyRepository::adminGetDashboardDailyTrend(7),
            'weeklyTrend' => LegacyRepository::adminGetDashboardWeeklyTrend(8),
            'expiringForms' => LegacyRepository::adminGetExpiringForms(7, 8),
            'conflictHeatmap' => LegacyRepository::adminGetConflictHeatmapByCategory(30),
            'simperStats' => $simperStats,
        ]);
    }
}
