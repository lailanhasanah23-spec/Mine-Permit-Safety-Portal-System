<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Submission;
use App\Services\GoogleDriveService;
use App\Support\Legacy\LegacyAuth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SubmissionController extends Controller
{
    public function index(Request $request)
    {
        $admin = LegacyAuth::user();
        $userId = $admin['id'] ?? null;

        if (LegacyAuth::isVendor()) {
            $userRole = 'subcon';
            $isVendor = true;
            $userId = null;
        } else {
            $userRow = DB::table('users')->where('id', (int) $userId)->first();
            $userRole = $userRow->role ?? 'admin';
            $isVendor = false;
        }

        $vendorId = LegacyAuth::vendorId();

        $query = Submission::with(['creator', 'category', 'vendor'])->latest();

        // HRGA filtering: only see staff-created submissions (not vendor submissions)
        if (! $isVendor && $userRole === 'hrga') {
            $query->where(function ($q) {
                $q->where('submitted_by_vendor', false)
                    ->orWhereNull('vendor_id');
            });
        }

        // Vendor filtering: subcon/vendor only see their own submissions
        if ($isVendor && $vendorId) {
            $query->where('vendor_id', $vendorId);
        }

        if ($request->has('category_id') && $request->category_id !== '') {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('search') && $request->search !== '') {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('applicant_name', 'like', "%{$s}%");
            });
        }

        $submissions = $query->paginate(20);
        $categories = Category::where('is_active', 1)->where('code', 'SIMPER_PERMIT')->get();

        return view('admin.submissions.index', [
            'submissions' => $submissions,
            'categories' => $categories,
            'userRole' => $userRole,
            'admin' => $admin,
            'isVendor' => $isVendor,
            'vendorId' => $vendorId,
        ]);
    }

    public function create()
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

        if (! in_array($userRole, ['hrga', 'subcon', 'admin'])) {
            return redirect()->route('admin.submissions.index')->with('error', 'Hanya HRGA atau Mitra/Subcon yang dapat membuat pengajuan baru.');
        }

        $categories = Category::where('is_active', 1)->where('code', 'SIMPER_PERMIT')->get();

        return view('admin.submissions.create', [
            'admin' => $admin,
            'categories' => $categories,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'applicant_name' => 'nullable|string|max:190',
            'item_type' => 'nullable|string|max:100',
            'item_identifier' => 'nullable|string|max:100',
        ]);

        $admin = LegacyAuth::user();
        $userId = $admin['id'] ?? null;
        $isVendor = LegacyAuth::isVendor();
        $vendorId = LegacyAuth::vendorId();

        if ($isVendor) {
            $userRole = 'subcon';
            $userId = null;
        } else {
            $userRow = DB::table('users')->where('id', (int) $userId)->first();
            $userRole = $userRow->role ?? 'admin';
        }

        if (! in_array($userRole, ['hrga', 'subcon', 'admin'])) {
            return redirect()->route('admin.submissions.index')->with('error', 'Hanya HRGA atau Mitra/Subcon yang dapat membuat pengajuan baru.');
        }

        $category = Category::findOrFail($request->category_id);

        $initialStatus = 'pending_she';
        if ($category->code === 'SIMPER_PERMIT') {
            $initialStatus = 'pending_hrga';
        }

        return DB::transaction(function () use ($request, $userId, $initialStatus, $userRole, $isVendor, $vendorId): RedirectResponse {
            $submission = Submission::create([
                'category_id' => $request->category_id,
                'applicant_name' => $request->applicant_name,
                'item_type' => $request->item_type,
                'item_identifier' => $request->item_identifier,
                'status' => $initialStatus,
                'created_by' => $userId,
                'vendor_id' => $isVendor ? $vendorId : null,
                'submitted_by_vendor' => $isVendor ? true : false,
            ]);

            Log::info("Submission initialized ID: {$submission->id} by User ID: ".($userId ?? 'Vendor')." with Role: {$userRole} | Vendor: {$vendorId}");

            return redirect()->route('admin.submissions.show', $submission->id)->with('success', 'Inisialisasi berhasil. Silakan unggah berkas dari perangkat atau tautkan dari Google Drive Anda di halaman ini.');
        });
    }

    public function show(int $id)
    {
        $submission = Submission::with(['files.uploader', 'creator', 'category', 'vendor'])->findOrFail($id);

        $admin = LegacyAuth::user();
        $userId = $admin['id'] ?? null;

        if (LegacyAuth::isVendor()) {
            $userRole = 'subcon';
            $isVendor = true;
        } else {
            $userRow = DB::table('users')->where('id', (int) $userId)->first();
            $userRole = $userRow->role ?? 'admin';
            $isVendor = false;
        }

        $vendorId = LegacyAuth::vendorId();

        // HRGA authorization: cannot see vendor submissions
        if (! $isVendor && $userRole === 'hrga') {
            if ($submission->submitted_by_vendor || $submission->vendor_id) {
                abort(403, 'Anda tidak memiliki akses ke pengajuan vendor. Pengajuan ini adalah milik mitra/subcon.');
            }
        }

        // Vendor authorization: only see their own submissions
        if ($isVendor && $submission->vendor_id !== $vendorId) {
            abort(403, 'Anda tidak memiliki akses ke pengajuan ini.');
        }

        return view('admin.submissions.show', [
            'submission' => $submission,
            'userRole' => $userRole,
            'admin' => $admin,
            'isVendor' => $isVendor,
        ]);
    }

    public function update(Request $request, int $id)
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

        if (! in_array($userRole, ['hrga', 'subcon'])) {
            return redirect()->back()->with('error', 'Hanya HRGA atau Mitra/Subcon yang dapat mengubah informasi pemohon.');
        }

        $submission = Submission::findOrFail($id);
        $vendorId = LegacyAuth::vendorId();
        $isVendor = LegacyAuth::isVendor();

        // HRGA cannot modify vendor submissions
        if (! $isVendor && $userRole === 'hrga') {
            if ($submission->submitted_by_vendor || $submission->vendor_id) {
                return redirect()->back()->with('error', 'Anda tidak memiliki akses ke pengajuan vendor.');
            }
        }

        // Vendor can only modify own submissions
        if ($isVendor && $submission->vendor_id !== $vendorId) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses ke pengajuan ini.');
        }

        $request->validate([
            'applicant_name' => 'nullable|string|max:190',
            'item_type' => 'nullable|string|max:100',
        ]);

        $submission = Submission::findOrFail($id);
        $submission->update($request->only([
            'applicant_name',
            'item_type',
        ]));

        return redirect()->back()->with('success', 'Data pengajuan berhasil diperbarui.');
    }

    public function approve(Request $request, int $id)
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

        if (! in_array($userRole, ['she', 'admin'])) {
            return redirect()->back()->with('error', 'Hanya Departemen SHE yang dapat menyetujui pengajuan.');
        }

        return DB::transaction(function () use ($id, $userId): RedirectResponse {
            $submission = Submission::lockForUpdate()->findOrFail($id);

            if (! in_array($submission->status, ['pending_she', 'rejected'])) {
                return redirect()->back()->with('error', 'Status tidak valid untuk disetujui (mungkin sudah diproses sebelumnya).');
            }

            $submission->update([
                'status' => 'approved',
                'approved_at' => now(),
                'she_notes' => null,
            ]);

            Log::info("Submission approved ID: {$id} by User ID: {$userId}");

            return redirect()->route('admin.submissions.index')->with('success', 'Pengajuan berhasil disetujui oleh SHE.');
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

        if (! in_array($userRole, ['she', 'admin'])) {
            return redirect()->back()->with('error', 'Hanya Departemen SHE yang dapat menolak/meminta pengajuan ulang.');
        }

        $request->validate([
            'she_notes' => 'required|string|max:1000',
        ]);

        return DB::transaction(function () use ($request, $id, $userId): RedirectResponse {
            $submission = Submission::lockForUpdate()->findOrFail($id);

            if ($submission->status !== 'pending_she') {
                return redirect()->back()->with('error', 'Status tidak valid untuk ditolak (mungkin sudah diproses sebelumnya).');
            }

            $submission->update([
                'status' => 'rejected', // Final status as requested
                'she_notes' => $request->she_notes,
                'rejected_at' => now(),
            ]);

            Log::warning("Submission rejected ID: {$id} by User ID: {$userId}. Reason: {$request->she_notes}");

            return redirect()->route('admin.submissions.index')->with('error', 'Pengajuan ditolak oleh SHE. Status dikembalikan untuk Pengajuan Ulang / Perbaikan Berkas.');
        });
    }

    public function downloadFile(int $id, int $fileId)
    {
        $admin = LegacyAuth::user();
        $userId = $admin['id'] ?? null;
        $isVendor = LegacyAuth::isVendor();
        $vendorId = LegacyAuth::vendorId();

        if (LegacyAuth::isVendor()) {
            $userRole = 'subcon';
            $userId = null;
        } else {
            $userRow = DB::table('users')->where('id', (int) $userId)->first();
            $userRole = $userRow->role ?? 'admin';
        }

        $submission = Submission::findOrFail($id);

        // HRGA cannot download from vendor submissions
        if (! $isVendor && $userRole === 'hrga') {
            if ($submission->submitted_by_vendor || $submission->vendor_id) {
                abort(403, 'Anda tidak memiliki akses ke file vendor.');
            }
        }

        // Vendor can only download from own submissions
        if ($isVendor && $submission->vendor_id !== $vendorId) {
            abort(403, 'Anda tidak memiliki akses ke file ini.');
        }

        $file = $submission->files()->findOrFail($fileId);

        // If it's a URL (GDrive), redirect to it
        if (str_starts_with($file->file_path, 'http')) {
            return redirect()->away($file->file_path);
        }

        if (! Storage::disk('local')->exists($file->file_path)) {
            abort(404, 'File not found');
        }

        $absolutePath = Storage::disk('local')->path($file->file_path);
        $mimeType = Storage::disk('local')->mimeType($file->file_path) ?: 'application/octet-stream';
        $safeName = preg_replace('/[^A-Za-z0-9._-]/', '_', $file->file_name ?: basename($file->file_path));

        // Render inline preview in browser (not forced attachment download)
        return response()->file($absolutePath, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="'.$safeName.'"',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    /**
     * Delete an entire submission (admin or SHE).
     */
    public function destroy(int $id)
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

        if (! in_array($userRole, ['admin', 'she'])) {
            return redirect()->back()->with('error', 'Hanya admin atau SHE yang dapat menghapus pengajuan.');
        }

        return DB::transaction(function () use ($id, $userId) {
            $submission = Submission::with('files')->findOrFail($id);

            foreach ($submission->files as $file) {
                if (! str_starts_with($file->file_path, 'http')) {
                    Storage::disk('local')->delete($file->file_path);
                }
                $file->delete();
            }

            $submission->delete();
            Log::warning("Submission ID {$id} deleted by User ID {$userId}");

            return redirect()->route('admin.submissions.index')->with('success', 'Pengajuan berhasil dihapus.');
        });
    }

    /**
     * Delete a specific file from a submission. Accessible by uploader role, admin, or SHE.
     */
    public function deleteFile(int $submissionId, int $fileId)
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

        $submission = Submission::findOrFail($submissionId);
        $vendorId = LegacyAuth::vendorId();
        $isVendor = LegacyAuth::isVendor();

        // HRGA cannot delete from vendor submissions
        if (! $isVendor && $userRole === 'hrga') {
            if ($submission->submitted_by_vendor || $submission->vendor_id) {
                return redirect()->back()->with('error', 'Anda tidak memiliki izin menghapus berkas vendor.');
            }
        }

        // Vendor can only delete from own submissions
        if ($isVendor && $submission->vendor_id !== $vendorId) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses ke pengajuan ini.');
        }

        $file = $submission->files()->where('id', $fileId)->firstOrFail();

        $canDelete = in_array($userRole, ['admin', 'she']) ||
                     $file->uploader_role === $userRole ||
                     ($userRole === 'subcon' && $file->uploader_role === 'hrga');

        if (! $canDelete) {
            return redirect()->back()->with('error', 'Anda tidak memiliki izin menghapus berkas ini.');
        }

        if (! str_starts_with($file->file_path, 'http')) {
            Storage::disk('local')->delete($file->file_path);
        }
        $file->delete();
        Log::info("File ID {$fileId} from Submission {$submissionId} deleted by User ID {$userId}");

        return redirect()->back()->with('success', 'Berkas berhasil dihapus.');
    }

    public function linkDriveFile(Request $request, int $id)
    {
        $request->validate([
            'file_type' => 'required|string',
            'file_url' => 'required|url',
            'file_name' => 'required|string|max:255',
        ]);

        $admin = LegacyAuth::user();
        $userId = $admin['id'] ?? null;

        if (LegacyAuth::isVendor()) {
            $userRole = 'subcon';
            $userId = null;
        } else {
            $userRow = DB::table('users')->where('id', (int) ($userId ?? 0))->first();
            $userRole = $userRow->role ?? 'admin';
        }

        return DB::transaction(function () use ($request, $id, $userId, $userRole): RedirectResponse {
            $submission = Submission::lockForUpdate()->findOrFail($id);
            $vendorId = LegacyAuth::vendorId();
            $isVendor = LegacyAuth::isVendor();

            // HRGA cannot link to vendor submissions
            if (! $isVendor && $userRole === 'hrga') {
                if ($submission->submitted_by_vendor || $submission->vendor_id) {
                    return redirect()->back()->with('error', 'HRGA tidak dapat menautkan berkas ke pengajuan vendor.');
                }
            }

            // Vendor can only link to own submissions
            if ($isVendor && $submission->vendor_id !== $vendorId) {
                return redirect()->back()->with('error', 'Anda tidak memiliki akses ke pengajuan ini.');
            }

            $type = $request->file_type;

            // RBAC Enforcement based on status and role
            $canLink = false;
            if (in_array($userRole, ['admin', 'she'])) {
                $canLink = true;
            } elseif ($submission->status === 'pending_hrga' && in_array($userRole, ['hrga', 'subcon'])) {
                $canLink = in_array($type, ['ktp', 'mcu', 'sim', 'foto_diri']);
            } elseif (in_array($submission->status, ['pending_hrga', 'pending_tod']) && $userRole === 'tod') {
                $canLink = $type === 'hasil_verifikasi_tod';
            }

            if (! $canLink) {
                return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk menautkan berkas ini pada tahap pengajuan saat ini.');
            }

            // Hapus file lama jika ada (termasuk hapus fisik jika local)
            // Khusus TOD (hasil_verifikasi_tod) diperbolehkan multi-file, jadi tidak dihapus
            if ($type !== 'hasil_verifikasi_tod') {
                $existing = $submission->files()->where('file_type', $type)->first();
                if ($existing) {
                    if (! str_starts_with($existing->file_path, 'http')) {
                        Storage::disk('local')->delete($existing->file_path);
                    }
                    $existing->delete();
                }
            }

            // Determine bucket based on file type to maintain UI consistency
            $assignedRole = $userRole;
            if ($type === 'hasil_verifikasi_tod') {
                $assignedRole = 'tod';
            } elseif (in_array($type, ['ktp', 'mcu', 'sim', 'foto_diri'])) {
                $assignedRole = 'hrga';
            }

            // Simpan tautan GDrive baru via Eloquent
            $submission->files()->create([
                'file_type' => $type,
                'file_name' => $request->file_name,
                'file_path' => $request->file_url,
                'uploader_role' => $assignedRole,
                'uploaded_by' => $userId,
            ]);

            // Workflow advancement logic
            if ($submission->status === 'pending_hrga') {
                $hrgaFilesCount = $submission->files()->whereIn('file_type', ['ktp', 'mcu', 'sim', 'foto_diri'])->count();
                if ($hrgaFilesCount >= 4) {
                    $nextStatus = 'pending_she';
                    if ($submission->category->code === 'SIMPER_PERMIT') {
                        $nextStatus = 'pending_paramedic';
                    }
                    $submission->update(['status' => $nextStatus]);
                }
            } elseif ($submission->status === 'pending_tod') {
                $todFilesCount = $submission->files()->where('file_type', 'hasil_verifikasi_tod')->count();
                if ($todFilesCount >= 1) {
                    $submission->update(['status' => 'pending_she']);
                }
            }

            Log::info("Drive file linked to submission ID: {$id} | Type: {$type} by User ID: {$userId}");

            return redirect()->back()->with('success', 'Berkas berhasil ditautkan dari Google Drive.');
        });
    }

    public function syncDriveFolder(GoogleDriveService $driveService, int $id)
    {
        $admin = LegacyAuth::user();
        $userId = $admin['id'] ?? null;

        if (LegacyAuth::isVendor()) {
            $userRole = 'subcon';
        } else {
            $userRow = DB::table('users')->where('id', (int) $userId)->first();
            $userRole = $userRow->role ?? 'admin';
        }

        if (! in_array($userRole, ['admin', 'she'])) {
            return redirect()->back()->with('error', 'Hanya admin atau SHE yang dapat menyinkronkan folder Google Drive pengajuan.');
        }

        return DB::transaction(function () use ($driveService, $id, $userId): RedirectResponse {
            $submission = Submission::with(['vendor', 'creator'])->lockForUpdate()->findOrFail($id);

            if (! empty($submission->gdrive_folder_id)) {
                return redirect()->back()->with('success', 'Folder Drive pengajuan ini sudah tersinkron.');
            }

            $result = $driveService->ensureSubmissionFolder($submission, $userId);

            $submission->update([
                'gdrive_folder_id' => $result['folder_id'],
            ]);

            Log::info("Submission Drive folder synced for ID {$id} by User ID {$userId} | Path: ".implode('/', $result['segments']));

            return redirect()->back()->with('success', 'Folder Drive berhasil disinkronkan: '.implode('/', $result['segments']));
        });
    }

    public function forwardHrga(Request $request, int $id)
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

        if (! in_array($userRole, ['hrga', 'subcon', 'admin'])) {
            return redirect()->back()->with('error', 'Anda tidak memiliki izin untuk melanjutkan pengajuan.');
        }

        return DB::transaction(function () use ($request, $id, $userRole): RedirectResponse {
            $submission = Submission::lockForUpdate()->findOrFail($id);

            // Allow forward from pending_hrga or from rejected status
            if ($submission->status !== 'pending_hrga' && $submission->status !== 'rejected') {
                return redirect()->back()->with('error', 'Status tidak valid untuk melanjutkan.');
            }

            // If coming from rejected status, reset to pending_hrga instead of moving forward
            if ($submission->status === 'rejected' && $request->has('from_rejected')) {
                $submission->update(['status' => 'pending_hrga', 'she_notes' => null]);
                Log::info("Submission ID {$id} re-submitted by role {$userRole} after rejection");

                return redirect()->back()->with('success', 'Pengajuan berhasil diajukan ulang ke HRGA.');
            }

            // Normal forward from pending_hrga
            $nextStatus = 'pending_she';
            if ($submission->category->code === 'SIMPER_PERMIT') {
                $nextStatus = 'pending_paramedic';
            }

            $submission->update(['status' => $nextStatus]);

            Log::info("Submission ID {$id} forwarded from HRGA to {$nextStatus} by role {$userRole}");

            return redirect()->back()->with('success', 'Pengajuan berhasil dilanjutkan.');
        });
    }

    public function forwardTod(Request $request, int $id)
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
            return redirect()->back()->with('error', 'Anda tidak memiliki izin untuk melanjutkan pengajuan.');
        }

        return DB::transaction(function () use ($id): RedirectResponse {
            $submission = Submission::lockForUpdate()->findOrFail($id);

            if ($submission->status !== 'pending_tod') {
                return redirect()->back()->with('error', 'Status tidak valid untuk melanjutkan.');
            }

            $submission->update(['status' => 'pending_she']);

            Log::info("Submission ID {$id} forwarded from TOD to pending_she");

            return redirect()->back()->with('success', 'Pengajuan berhasil dilanjutkan ke SHE.');
        });
    }
}
