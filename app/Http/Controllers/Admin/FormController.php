<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Legacy\LegacyAuth;
use App\Support\Legacy\LegacyFormRules;
use App\Support\Legacy\LegacyRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PDOException;
use Throwable;

class FormController extends Controller
{
    public function index()
    {
        $forms = LegacyRepository::adminGetFormsForManagement();

        $activeFormsCount = 0;
        $categoryMonitoringMap = [];
        foreach ($forms as $formRow) {
            $isActive = (int) ($formRow['is_active'] ?? 0) === 1;
            if ($isActive) {
                $activeFormsCount++;
            }

            $categoryId = (int) ($formRow['category_id'] ?? 0);
            if ($categoryId < 1) {
                continue;
            }

            if (! isset($categoryMonitoringMap[$categoryId])) {
                $categoryMonitoringMap[$categoryId] = [
                    'has_active_pengajuan' => false,
                    'has_active_monitoring' => false,
                ];
            }

            if (! $isActive) {
                continue;
            }

            $purpose = (string) ($formRow['purpose'] ?? '');
            if ($purpose === 'monitoring') {
                $categoryMonitoringMap[$categoryId]['has_active_monitoring'] = true;

                continue;
            }

            if ($purpose === 'pengajuan') {
                $categoryMonitoringMap[$categoryId]['has_active_pengajuan'] = true;
            }
        }

        $categoriesWithActivePengajuan = 0;
        $categoriesMonitoringChecklistReady = 0;
        foreach ($categoryMonitoringMap as $monitoringState) {
            if (! (bool) ($monitoringState['has_active_pengajuan'] ?? false)) {
                continue;
            }

            $categoriesWithActivePengajuan++;
            if ((bool) ($monitoringState['has_active_monitoring'] ?? false)) {
                $categoriesMonitoringChecklistReady++;
            }
        }

        $categoriesMonitoringChecklistMissing = max(0, $categoriesWithActivePengajuan - $categoriesMonitoringChecklistReady);

        $admin = LegacyAuth::user();
        $userRole = DB::table('users')->where('id', $admin['id'] ?? 0)->value('role') ?? 'admin';

        return view('admin.forms', [
            'admin' => $admin,
            'userRole' => $userRole,
            'categories' => LegacyRepository::adminGetActiveCategories(),
            'forms' => $forms,
            'totalFormsCount' => count($forms),
            'activeFormsCount' => $activeFormsCount,
            'inactiveFormsCount' => count($forms) - $activeFormsCount,
            'categoryMonitoringMap' => $categoryMonitoringMap,
            'categoriesWithActivePengajuan' => $categoriesWithActivePengajuan,
            'categoriesMonitoringChecklistReady' => $categoriesMonitoringChecklistReady,
            'categoriesMonitoringChecklistMissing' => $categoriesMonitoringChecklistMissing,
            'todayDate' => date('Y-m-d'),
        ]);
    }

    public function store(Request $request)
    {
        $admin = LegacyAuth::user();
        if (! $admin) {
            return redirect()->route('admin.login.php')->with('error', 'Silakan masuk sebagai administrator.');
        }

        $action = (string) $request->input('action', 'create');
        if ($action === 'update') {
            return $this->update($request, (int) $request->input('id', 0));
        }

        if ($action === 'delete') {
            return $this->archive($request, (int) $request->input('id', 0));
        }

        $result = LegacyFormRules::validateCreate($request->all());
        if (! $result['ok']) {
            return redirect()->route('admin.forms.php')->with('error', implode(' ', $result['errors']));
        }

        if (! LegacyRepository::adminCategoryIsActive((int) $result['data']['category_id'])) {
            return redirect()->route('admin.forms.php')->with('error', 'Kategori tidak tersedia atau sudah dinonaktifkan.');
        }

        if (LegacyRepository::adminFormExistsByIdentity(
            (int) $result['data']['category_id'],
            (string) $result['data']['title'],
            (string) $result['data']['purpose']
        )) {
            return redirect()->route('admin.forms.php')->with('error', 'Formulir dengan kategori, judul, dan tujuan yang sama sudah tersedia.');
        }

        if (LegacyRepository::adminFormExistsByUrl(
            (int) $result['data']['category_id'],
            (string) $result['data']['purpose'],
            (string) $result['data']['form_url']
        )) {
            return redirect()->route('admin.forms.php')->with('error', 'URL formulir yang sama untuk kategori dan tujuan ini sudah tersedia.');
        }

        $createConflicts = [];
        if ($this->hasScheduleWindow($result['data']['effective_start'] ?? null, $result['data']['effective_end'] ?? null)) {
            $createConflicts = LegacyRepository::adminGetActiveScheduleConflicts(
                (int) $result['data']['category_id'],
                (string) $result['data']['purpose'],
                $result['data']['effective_start'],
                $result['data']['effective_end'],
                null,
                25
            );
        }

        if ($createConflicts !== []) {
            try {
                LegacyRepository::adminWriteAuditLog(
                    (int) $admin['id'],
                    'form.create_conflict_prevented',
                    'forms',
                    null,
                    null,
                    [
                        'reason' => 'schedule_conflict',
                        'attempted_data' => $result['data'],
                        'conflict_form_ids' => $this->extractConflictIds($createConflicts),
                        'conflict_count' => count($createConflicts),
                    ],
                    (string) $request->ip(),
                    (string) $request->userAgent()
                );
            } catch (Throwable $e) {
                // Keep flow alive even when audit write fails.
            }

            return $this->redirectWithScheduleConflict(
                'Pembuatan formulir diblokir: jadwal bertabrakan dengan formulir aktif lain. Detail konflik ditampilkan agar mudah ditelusuri.',
                $result['data'],
                $createConflicts
            );
        }

        try {
            $newFormId = LegacyRepository::adminCreateForm($result['data'], (int) $admin['id']);
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                return redirect()->route('admin.forms.php')->with('error', 'Formulir serupa sudah tersedia. Gunakan judul berbeda atau perbarui data yang ada.');
            }
            throw $e;
        }

        $afterState = LegacyRepository::adminFindFormById($newFormId);

        try {
            LegacyRepository::adminWriteAuditLog(
                (int) $admin['id'],
                'form.create',
                'forms',
                $newFormId,
                null,
                $afterState,
                (string) $request->ip(),
                (string) $request->userAgent()
            );
        } catch (Throwable $e) {
            // Keep flow alive even when audit write fails.
        }

        return redirect()->route('admin.forms.php')
            ->with('success', 'Formulir baru berhasil ditambahkan. URL Google Form telah divalidasi dan disimpan dengan aman.')
            ->with('created_form_id', $newFormId)
            ->with('created_form_url', (string) ($afterState['form_url'] ?? ''));
    }

    public function update(Request $request, int $id)
    {
        $admin = LegacyAuth::user();
        if (! $admin) {
            return redirect()->route('admin.login.php')->with('error', 'Silakan masuk sebagai administrator.');
        }

        if ($id < 1) {
            return redirect()->route('admin.forms.php')->with('error', 'ID formulir tidak valid.');
        }

        $beforeState = LegacyRepository::adminFindFormById($id);
        if (! $beforeState) {
            return redirect()->route('admin.forms.php')->with('error', 'Formulir tidak ditemukan.');
        }

        $updateInput = $request->all();
        $updateInput['category_id'] = (string) $beforeState['category_id'];
        $result = LegacyFormRules::validateUpdate($updateInput);

        if (! $result['ok']) {
            return redirect()->route('admin.forms.php')->with('error', implode(' ', $result['errors']));
        }

        if (LegacyRepository::adminFormExistsByIdentity(
            (int) $beforeState['category_id'],
            (string) $result['data']['title'],
            (string) $result['data']['purpose'],
            $id
        )) {
            return redirect()->route('admin.forms.php')->with('error', 'Formulir dengan kategori, judul, dan tujuan yang sama sudah tersedia.');
        }

        if (LegacyRepository::adminFormExistsByUrl(
            (int) $beforeState['category_id'],
            (string) $result['data']['purpose'],
            (string) $result['data']['form_url'],
            $id
        )) {
            return redirect()->route('admin.forms.php')->with('error', 'URL formulir yang sama untuk kategori dan tujuan ini sudah digunakan oleh entri lain.');
        }

        if ((int) $result['data']['is_active'] === 1 && $this->hasScheduleWindow($result['data']['effective_start'] ?? null, $result['data']['effective_end'] ?? null)) {
            $proposedConflicts = LegacyRepository::adminGetActiveScheduleConflicts(
                (int) $beforeState['category_id'],
                (string) $result['data']['purpose'],
                $result['data']['effective_start'],
                $result['data']['effective_end'],
                $id,
                50
            );

            $newConflicts = $proposedConflicts;
            if ((int) ($beforeState['is_active'] ?? 0) === 1) {
                $baselineConflicts = LegacyRepository::adminGetActiveScheduleConflicts(
                    (int) $beforeState['category_id'],
                    (string) ($beforeState['purpose'] ?? ''),
                    $this->normalizeNullableDate($beforeState['effective_start'] ?? null),
                    $this->normalizeNullableDate($beforeState['effective_end'] ?? null),
                    $id,
                    50
                );

                $baselineConflictIds = $this->extractConflictIds($baselineConflicts);
                if ($baselineConflictIds !== []) {
                    $newConflicts = array_values(array_filter(
                        $proposedConflicts,
                        static fn (array $row): bool => ! in_array((int) ($row['id'] ?? 0), $baselineConflictIds, true)
                    ));
                }
            }

            if ($newConflicts !== []) {
                try {
                    LegacyRepository::adminWriteAuditLog(
                        (int) $admin['id'],
                        'form.update_conflict_prevented',
                        'forms',
                        $id,
                        $beforeState,
                        [
                            'reason' => 'schedule_conflict',
                            'attempted_data' => $result['data'],
                            'conflict_form_ids' => $this->extractConflictIds($newConflicts),
                            'conflict_count' => count($newConflicts),
                        ],
                        (string) $request->ip(),
                        (string) $request->userAgent()
                    );
                } catch (Throwable $e) {
                    // Keep flow alive even when audit write fails.
                }

                return $this->redirectWithScheduleConflict(
                    'Pembaruan memicu bentrok jadwal baru dengan formulir aktif lain. Detail konflik ditampilkan agar cepat ditelusuri.',
                    $result['data'],
                    $newConflicts
                );
            }
        }

        try {
            LegacyRepository::adminUpdateForm($id, $result['data'], (int) $admin['id']);
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                return redirect()->route('admin.forms.php')->with('error', 'Pembaruan gagal karena kombinasi data formulir duplikat.');
            }
            throw $e;
        }

        $afterState = LegacyRepository::adminFindFormById($id);

        try {
            LegacyRepository::adminWriteAuditLog(
                (int) $admin['id'],
                'form.update',
                'forms',
                $id,
                $beforeState,
                $afterState,
                (string) $request->ip(),
                (string) $request->userAgent()
            );
        } catch (Throwable $e) {
            // Keep flow alive even when audit write fails.
        }

        return redirect()->route('admin.forms.php')
            ->with('success', 'Formulir berhasil diperbarui. URL Google Form telah divalidasi dan disimpan dengan aman.')
            ->with('edited_form_id', $id)
            ->with('edited_form_url', (string) ($afterState['form_url'] ?? ''));
    }

    public function archive(Request $request, int $id)
    {
        $admin = LegacyAuth::user();
        if (! $admin) {
            return redirect()->route('admin.login.php')->with('error', 'Silakan masuk sebagai administrator.');
        }

        if ($id > 0) {
            $beforeState = LegacyRepository::adminFindFormById($id);
            LegacyRepository::adminArchiveForm($id, (int) $admin['id']);
            $afterState = LegacyRepository::adminFindFormById($id);

            try {
                LegacyRepository::adminWriteAuditLog(
                    (int) $admin['id'],
                    'form.archive',
                    'forms',
                    $id,
                    $beforeState,
                    $afterState,
                    (string) $request->ip(),
                    (string) $request->userAgent()
                );
            } catch (Throwable $e) {
                // Keep flow alive even when audit write fails.
            }
        }

        return redirect()->route('admin.forms.php')->with('success', 'Formulir berhasil diarsipkan (dinonaktifkan) untuk menjaga jejak audit.');
    }

    private function normalizeNullableDate(mixed $value): ?string
    {
        $normalized = trim((string) $value);

        return $normalized === '' ? null : $normalized;
    }

    private function hasScheduleWindow(mixed $start, mixed $end): bool
    {
        return $this->normalizeNullableDate($start) !== null || $this->normalizeNullableDate($end) !== null;
    }

    private function extractConflictIds(array $conflicts): array
    {
        $ids = [];
        foreach ($conflicts as $row) {
            $id = (int) ($row['id'] ?? 0);
            if ($id > 0) {
                $ids[] = $id;
            }
        }

        return array_values(array_unique($ids));
    }

    private function toConflictFlashRows(array $conflicts): array
    {
        $rows = [];
        foreach ($conflicts as $row) {
            $id = (int) ($row['id'] ?? 0);
            if ($id < 1) {
                continue;
            }

            $rows[] = [
                'id' => $id,
                'title' => (string) ($row['title'] ?? ''),
                'category_name' => (string) ($row['category_name'] ?? ''),
                'purpose' => (string) ($row['purpose'] ?? ''),
                'link_scope' => (string) ($row['link_scope'] ?? 'public'),
                'effective_start' => $this->normalizeNullableDate($row['effective_start'] ?? null),
                'effective_end' => $this->normalizeNullableDate($row['effective_end'] ?? null),
                'updated_at' => (string) ($row['updated_at'] ?? ''),
                'form_url' => (string) ($row['form_url'] ?? ''),
            ];
        }

        return $rows;
    }

    private function redirectWithScheduleConflict(string $message, array $attemptedData, array $conflicts)
    {
        return redirect()->route('admin.forms.php')
            ->with('error', $message)
            ->with('schedule_conflict_context', [
                'purpose' => (string) ($attemptedData['purpose'] ?? ''),
                'effective_start' => $this->normalizeNullableDate($attemptedData['effective_start'] ?? null),
                'effective_end' => $this->normalizeNullableDate($attemptedData['effective_end'] ?? null),
                'conflict_count' => count($conflicts),
            ])
            ->with('schedule_conflict_details', $this->toConflictFlashRows($conflicts));
    }
}
