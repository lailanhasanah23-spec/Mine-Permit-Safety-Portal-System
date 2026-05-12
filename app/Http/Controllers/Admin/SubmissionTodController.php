<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Submission;
use App\Support\Legacy\LegacyAuth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SubmissionTodController extends Controller
{
    public function upload(Request $request, int $id)
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

        if (! in_array($userRole, ['tod', 'admin'])) {
            return redirect()->back()->with('error', 'Hanya Departemen TOD yang dapat mengunggah hasil tes.');
        }

        $submission = Submission::findOrFail($id);

        if ($submission->status === 'approved') {
            return redirect()->back()->with('error', 'Pengajuan sudah disetujui, berkas tidak dapat diubah.');
        }

        if (! in_array($userRole, ['admin', 'she']) && ! in_array($submission->status, ['pending_hrga', 'pending_paramedic', 'pending_tod', 'pending_she', 'rejected'])) {
            return redirect()->back()->with('error', 'Status pengajuan saat ini tidak mengizinkan unggah berkas TOD.');
        }

        $request->validate([
            'item_identifier' => 'nullable|string|max:100',
            'files' => 'required|array',
            'files.*' => 'file|mimes:pdf,jpg,jpeg,png,gif,webp,bmp,tif,tiff,doc,docx,xls,xlsx,ppt,pptx,txt,csv|max:10240',
        ]);

        return DB::transaction(function () use ($request, $submission, $userId): RedirectResponse|JsonResponse {
            // Pessimistic lock
            $submission = Submission::lockForUpdate()->find($submission->id);

            // Update item identifier from TOD
            if ($request->has('item_identifier')) {
                $submission->update(['item_identifier' => $request->item_identifier]);
            }

            $uploadedCount = 0;
            if ($request->hasFile('files')) {
                foreach ($request->file('files') as $file) {
                    $path = $file->store('submissions/'.$submission->id, 'local');

                    $submission->files()->create([
                        'uploader_role' => 'tod',
                        'file_type' => 'hasil_verifikasi_tod',
                        'file_path' => $path,
                        'file_name' => $file->getClientOriginalName(),
                        'uploaded_by' => $userId,
                    ]);
                    $uploadedCount++;
                }
            }

            // Check if TOD files are present to move to SHE
            $todFilesCount = $submission->files()->where('uploader_role', 'tod')->count();
            if ($todFilesCount > 0 && $submission->status === 'pending_tod') {
                $submission->update(['status' => 'pending_she']);

                Log::info("TOD files uploaded for Submission ID: {$submission->id}. Moving to pending_she.");

                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Berkas TOD berhasil diunggah. Diteruskan ke SHE.',
                        'next_status' => 'pending_she',
                    ]);
                }

                return redirect()->route('admin.submissions.show', $submission->id)->with('success', 'Berkas TOD berhasil diunggah. Diteruskan ke SHE.');
            }

            Log::info("TOD uploaded {$uploadedCount} files for Submission ID: {$submission->id} by User ID: {$userId}");

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $uploadedCount.' berkas TOD berhasil diunggah.',
                    'next_status' => $submission->status,
                ]);
            }

            return redirect()->route('admin.submissions.show', $submission->id)->with('success', $uploadedCount.' berkas TOD berhasil diunggah.');
        });
    }
}
