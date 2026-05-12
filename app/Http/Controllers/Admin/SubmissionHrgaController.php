<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Submission;
use App\Support\Legacy\LegacyAuth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SubmissionHrgaController extends Controller
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

        if (! in_array($userRole, ['hrga', 'subcon', 'admin', 'she'])) {
            return redirect()->back()->with('error', 'Hanya Departemen HRGA, SHE, atau Subcon yang dapat mengunggah berkas identitas.');
        }

        $submission = Submission::findOrFail($id);
        $vendorId = LegacyAuth::vendorId();
        $isVendor = LegacyAuth::isVendor();

        // HRGA cannot upload to vendor submissions
        if (! $isVendor && $userRole === 'hrga') {
            if ($submission->submitted_by_vendor || $submission->vendor_id) {
                return redirect()->back()->with('error', 'HRGA tidak dapat mengupload ke pengajuan vendor. Vendor harus mengupload berkas mereka sendiri.');
            }
        }

        // Vendor can only upload to own submissions
        if ($isVendor && $submission->vendor_id !== $vendorId) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses ke pengajuan ini.');
        }

        // Allow Admin/SHE to upload always unless approved.
        // Allow HRGA/Subcon to upload during initial stages or if not yet approved.
        if ($submission->status === 'approved') {
            return redirect()->back()->with('error', 'Pengajuan sudah disetujui, berkas tidak dapat diubah.');
        }

        if (! in_array($userRole, ['admin', 'she']) && ! in_array($submission->status, ['pending_hrga', 'pending_paramedic', 'pending_tod', 'pending_she', 'rejected'])) {
            return redirect()->back()->with('error', 'Status pengajuan saat ini tidak mengizinkan unggah berkas HRGA.');
        }

        $request->validate([
            'type' => 'nullable|string|in:ktp,mcu,sim,foto_diri',
            'file' => 'nullable|file|mimes:pdf,jpg,jpeg,png,gif,webp,bmp,tif,tiff,doc,docx,xls,xlsx,ppt,pptx,txt,csv|max:10240',
            'ktp' => 'nullable|file|mimes:pdf,jpg,jpeg,png,gif,webp,bmp,tif,tiff,doc,docx,xls,xlsx,ppt,pptx,txt,csv|max:10240',
            'mcu' => 'nullable|file|mimes:pdf,jpg,jpeg,png,gif,webp,bmp,tif,tiff,doc,docx,xls,xlsx,ppt,pptx,txt,csv|max:10240',
            'sim' => 'nullable|file|mimes:pdf,jpg,jpeg,png,gif,webp,bmp,tif,tiff,doc,docx,xls,xlsx,ppt,pptx,txt,csv|max:10240',
            'foto_diri' => 'nullable|file|mimes:pdf,jpg,jpeg,png,gif,webp,bmp,tif,tiff,doc,docx,xls,xlsx,ppt,pptx,txt,csv|max:10240',
        ]);

        $filesToProcess = ['ktp', 'mcu', 'sim', 'foto_diri'];

        return DB::transaction(function () use ($request, $submission, $filesToProcess, $userId): RedirectResponse|JsonResponse {
            // Pessimistic lock to prevent race conditions on status updates
            $submission = Submission::lockForUpdate()->find($submission->id);

            $uploadedCount = 0;

            // Handle single file upload from AJAX form (name="file", hidden type field)
            if ($request->hasFile('file') && $request->has('type')) {
                $fileType = $request->input('type');
                if (in_array($fileType, $filesToProcess)) {
                    $file = $request->file('file');
                    $path = $file->store('submissions/'.$submission->id, 'local');

                    // Delete old file if exists
                    $existing = $submission->files()->where('file_type', $fileType)->first();
                    if ($existing) {
                        if (! str_starts_with($existing->file_path, 'http')) {
                            Storage::disk('local')->delete($existing->file_path);
                        }
                        $existing->delete();
                    }

                    $submission->files()->create([
                        'uploader_role' => 'hrga',
                        'file_type' => $fileType,
                        'file_path' => $path,
                        'file_name' => $file->getClientOriginalName(),
                        'uploaded_by' => $userId,
                    ]);
                    $uploadedCount++;
                }
            }

            // Handle multi-file upload (standard format)
            foreach ($filesToProcess as $fileType) {
                if ($request->hasFile($fileType)) {
                    $file = $request->file($fileType);
                    $path = $file->store('submissions/'.$submission->id, 'local');

                    // Hapus file lama jika ada
                    $existing = $submission->files()->where('file_type', $fileType)->first();
                    if ($existing) {
                        if (! str_starts_with($existing->file_path, 'http')) {
                            Storage::disk('local')->delete($existing->file_path);
                        }
                        $existing->delete();
                    }

                    $submission->files()->create([
                        'uploader_role' => 'hrga',
                        'file_type' => $fileType,
                        'file_path' => $path,
                        'file_name' => $file->getClientOriginalName(),
                        'uploaded_by' => $userId,
                    ]);
                    $uploadedCount++;
                }
            }

            // Cek apakah semua 4 file HRGA sudah lengkap atau dipaksa teruskan (force_forward)
            $hrgaFilesCount = $submission->files()->where('uploader_role', 'hrga')->count();
            $shouldForward = ($hrgaFilesCount >= 4) || $request->has('force_forward');

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $shouldForward ? 'Berkas berhasil diproses. Tahap dilanjutkan.' : $uploadedCount.' berkas berhasil diunggah.',
                    'next_status' => $submission->status,
                ]);
            }

            if ($shouldForward) {
                // ... existing redirect logic ...
                $msg = match ($submission->status) {
                    'pending_tod' => 'Diteruskan ke TOD untuk Tes.',
                    'pending_paramedic' => 'Diteruskan ke Paramedic untuk Review MCU.',
                    'pending_she' => 'Diteruskan ke SHE.',
                    default => 'Diteruskan ke tahap berikutnya.'
                };

                return redirect()->route('admin.submissions.show', $submission->id)->with('success', 'Berkas HRGA berhasil diproses. '.$msg);
            }

            return redirect()->route('admin.submissions.show', $submission->id)->with('success', $uploadedCount.' berkas HRGA berhasil diunggah.');
        });
    }
}
