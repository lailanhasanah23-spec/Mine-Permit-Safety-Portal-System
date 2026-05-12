<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Submission;
use App\Support\Legacy\LegacyAuth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SubmissionParamedicController extends Controller
{
    public function verify(Request $request, int $id)
    {
        $admin = LegacyAuth::user();
        $userId = $admin['id'] ?? null;

        if (LegacyAuth::isVendor()) {
            $userRole = 'subcon';
            $userId = null;
        } else {
            $userRow = DB::table('users')->where('id', (int) $userId)->first();
            $userRole = $userRow->role ?? 'admin';
        }

        if (! in_array($userRole, ['paramedic', 'admin'])) {
            return redirect()->back()->with('error', 'Hanya Paramedic yang dapat memverifikasi MCU.');
        }

        return DB::transaction(function () use ($id, $request, $userId): RedirectResponse {
            $submission = Submission::lockForUpdate()->findOrFail($id);

            if ($submission->status !== 'pending_paramedic') {
                return redirect()->back()->with('error', 'Status pengajuan bukan menunggu verifikasi Paramedic.');
            }

            $isSimperFlow = $submission->item_type === 'SIMPER';

            $nextStatus = $isSimperFlow ? 'pending_tod' : 'pending_she';

            $submission->update([
                'status' => $nextStatus,
                'paramedic_verified_at' => now(),
                'paramedic_notes' => $request->paramedic_notes,
            ]);

            Log::info("Medical verification SUCCESS for Submission ID: {$id} by User ID: {$userId}");

            $nextStageLabel = $nextStatus === 'pending_tod' ? 'TOD' : 'SHE';

            return redirect()->route('admin.submissions.show', $id)->with('success', "Hasil MCU diverifikasi AMAN. Diteruskan ke {$nextStageLabel}.");
        });
    }

    public function reject(Request $request, int $id)
    {
        $admin = LegacyAuth::user();
        $userId = $admin['id'] ?? null;

        if (LegacyAuth::isVendor()) {
            $userRole = 'subcon';
            $userId = null;
        } else {
            $userRow = DB::table('users')->where('id', (int) $userId)->first();
            $userRole = $userRow->role ?? 'admin';
        }

        if (! in_array($userRole, ['paramedic', 'admin'])) {
            return redirect()->back()->with('error', 'Hanya Paramedic yang dapat menolak hasil MCU.');
        }

        $request->validate([
            'paramedic_notes' => 'nullable|string|max:1000',
        ]);

        return DB::transaction(function () use ($id, $request, $userId): RedirectResponse {
            $submission = Submission::lockForUpdate()->findOrFail($id);

            if ($submission->status !== 'pending_paramedic') {
                return redirect()->back()->with('error', 'Status pengajuan bukan menunggu verifikasi Paramedic.');
            }

            $submission->update([
                'status' => 'pending_hrga', // Back to HRGA/Subcon for revision
                'paramedic_notes' => $request->paramedic_notes,
            ]);

            Log::warning("Medical verification REJECTED for Submission ID: {$id} by User ID: {$userId}. Reason: {$request->paramedic_notes}");

            return redirect()->route('admin.submissions.index')->with('error', 'Hasil MCU terdapat temuan. Status dikembalikan ke HRGA untuk perbaikan.');
        });
    }

    public function saveFeedback(Request $request, int $id)
    {
        $admin = LegacyAuth::user();
        $userId = $admin['id'] ?? null;

        if (LegacyAuth::isVendor()) {
            $userRole = 'subcon';
            $userId = null;
        } else {
            $userRow = DB::table('users')->where('id', (int) $userId)->first();
            $userRole = $userRow->role ?? 'admin';
        }

        if (! in_array($userRole, ['paramedic', 'admin'])) {
            return redirect()->back()->with('error', 'Hanya Paramedic yang dapat memberikan feedback.');
        }

        $request->validate([
            'paramedic_notes' => 'nullable|string|max:1000',
        ]);

        $submission = Submission::findOrFail($id);
        $submission->update([
            'paramedic_notes' => $request->paramedic_notes,
        ]);

        Log::info("Paramedic feedback SAVED for Submission ID: {$id} by User ID: {$userId}");

        return redirect()->back()->with('success', 'Catatan feedback Paramedic berhasil disimpan.');
    }
}
