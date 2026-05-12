<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Legacy\LegacyAuth;
use App\Support\Legacy\LegacyFormRules;
use App\Support\Legacy\LegacyRepository;
use App\Support\Monitoring\SpreadsheetChecklistService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class MonitoringController extends Controller
{
    public function __construct(private readonly SpreadsheetChecklistService $spreadsheetChecklistService) {}

    public function index()
    {
        $categories = LegacyRepository::adminGetMonitoringCategoryStats();
        $spreadsheetByCategoryId = [];

        foreach ($categories as $category) {
            $categoryId = (int) ($category['id'] ?? 0);
            if ($categoryId < 1) {
                continue;
            }

            $spreadsheetByCategoryId[$categoryId] = $this->spreadsheetChecklistService
                ->summarizeForCategoryCode((string) ($category['code'] ?? ''));
        }

        $admin = LegacyAuth::user();
        $userRole = DB::table('users')->where('id', $admin['id'] ?? 0)->value('role') ?? 'admin';

        return view('admin.monitoring', [
            'admin' => $admin,
            'userRole' => $userRole,
            'categories' => $categories,
            'spreadsheetByCategoryId' => $spreadsheetByCategoryId,
        ]);
    }

    public function openMonitoringForm(int $id): RedirectResponse
    {
        $form = LegacyRepository::adminFindActiveMonitoringFormById($id);
        if (! $form) {
            return redirect()->route('admin.monitoring.php')
                ->with('error', 'Form monitoring aktif tidak ditemukan atau sudah nonaktif.');
        }

        $rawUrl = (string) ($form['form_url'] ?? '');
        $linkScope = (string) ($form['link_scope'] ?? 'public');
        $normalizedUrl = LegacyFormRules::canonicalizeFormUrl($rawUrl);

        if (! LegacyFormRules::isAllowedFormUrl($normalizedUrl, $linkScope, 'monitoring')) {
            return redirect()->route('admin.monitoring.php')
                ->with('error', 'URL form monitoring tidak valid. Periksa kembali di menu Kelola Formulir.');
        }

        return redirect()->away($normalizedUrl);
    }
}
