<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Legacy\LegacyAuth;
use App\Support\Legacy\LegacyRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class DocumentController extends Controller
{
    private const DOCUMENT_CODE = 'ktp-ohs-102-mine-permit-simper';

    private const DEFAULT_TITLE = 'KTP-OHS-102 Mine Permit & SIMPER';

    private const DEFAULT_DESCRIPTION = 'Dokumen standar operasional KTP-OHS-102 untuk tata kelola Mine Permit dan SIMPER. Pastikan seluruh personel membaca revisi terbaru.';

    private function ensureDocumentExists(?int $adminUserId): ?array
    {
        $document = LegacyRepository::adminGetPolicyDocumentByCode(self::DOCUMENT_CODE);
        if ($document) {
            return $document;
        }

        LegacyRepository::adminUpsertPolicyDocumentMeta(
            self::DOCUMENT_CODE,
            self::DEFAULT_TITLE,
            self::DEFAULT_DESCRIPTION,
            true,
            $adminUserId
        );

        return LegacyRepository::adminGetPolicyDocumentByCode(self::DOCUMENT_CODE);
    }

    public function index()
    {
        $admin = LegacyAuth::user();

        try {
            $document = $this->ensureDocumentExists($admin ? (int) $admin['id'] : null);
            $revisions = $document
                ? LegacyRepository::adminGetPolicyDocumentRevisions((int) $document['id'], 50)
                : [];
        } catch (Throwable $e) {
            return redirect()->route('admin.dashboard.php')->with('error', 'Modul dokumen belum siap. Jalankan migrasi terbaru terlebih dahulu.');
        }

        $userRole = DB::table('users')->where('id', $admin['id'] ?? 0)->value('role') ?? 'admin';

        return view('admin.documents', [
            'admin' => $admin,
            'userRole' => $userRole,
            'document' => $document,
            'revisions' => $revisions,
        ]);
    }

    public function updateMeta(Request $request)
    {
        $admin = LegacyAuth::user();
        if (! $admin) {
            return redirect()->route('admin.login.php')->with('error', 'Silakan masuk sebagai administrator.');
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:190'],
            'description' => ['nullable', 'string', 'max:5000'],
            'is_public' => ['nullable', 'boolean'],
        ]);

        $beforeState = LegacyRepository::adminGetPolicyDocumentByCode(self::DOCUMENT_CODE);

        $documentId = LegacyRepository::adminUpsertPolicyDocumentMeta(
            self::DOCUMENT_CODE,
            (string) $validated['title'],
            isset($validated['description']) ? (string) $validated['description'] : null,
            $request->boolean('is_public'),
            (int) $admin['id']
        );

        $afterState = LegacyRepository::adminGetPolicyDocumentByCode(self::DOCUMENT_CODE);

        try {
            LegacyRepository::adminWriteAuditLog(
                (int) $admin['id'],
                'policy_document.meta_update',
                'policy_documents',
                $documentId,
                $beforeState,
                $afterState,
                (string) $request->ip(),
                (string) $request->userAgent()
            );
        } catch (Throwable $e) {
            // Keep flow alive even when audit write fails.
        }

        return redirect()->route('admin.documents.php')->with('success', 'Metadata dokumen berhasil diperbarui.');
    }

    public function uploadRevision(Request $request)
    {
        $admin = LegacyAuth::user();
        if (! $admin) {
            return redirect()->route('admin.login.php')->with('error', 'Silakan masuk sebagai administrator.');
        }

        $validated = $request->validate([
            'revision_label' => ['required', 'string', 'max:80'],
            'revision_notes' => ['nullable', 'string', 'max:5000'],
            'document_file' => ['required', 'file', 'mimes:pdf', 'max:20480'],
        ]);

        $beforeState = $this->ensureDocumentExists((int) $admin['id']);
        if (! $beforeState) {
            return redirect()->route('admin.documents.php')->with('error', 'Dokumen master tidak ditemukan.');
        }

        $documentId = (int) ($beforeState['id'] ?? 0);
        if ($documentId < 1) {
            return redirect()->route('admin.documents.php')->with('error', 'ID dokumen tidak valid.');
        }

        $file = $request->file('document_file');
        if (! $file || ! $file->isValid()) {
            return redirect()->route('admin.documents.php')->with('error', 'Upload dokumen gagal. Pastikan file PDF valid.');
        }

        try {
            $safeRevision = preg_replace('/[^A-Za-z0-9._-]+/', '-', strtolower((string) $validated['revision_label'])) ?: 'revision';
            $storedDir = 'private/policy-documents/'.self::DOCUMENT_CODE;
            $storedName = date('YmdHis').'-'.trim($safeRevision, '-').'-'.bin2hex(random_bytes(4)).'.pdf';

            $storedPath = $file->storeAs($storedDir, $storedName, ['disk' => 'local']);
            if (! is_string($storedPath) || $storedPath === '') {
                return redirect()->route('admin.documents.php')->with('error', 'File tidak dapat disimpan ke storage.');
            }

            $absolutePath = storage_path('app/'.$storedPath);

            LegacyRepository::adminAddPolicyDocumentRevision($documentId, [
                'revision_label' => (string) $validated['revision_label'],
                'original_filename' => (string) $file->getClientOriginalName(),
                'stored_path' => $storedPath,
                'mime_type' => (string) ($file->getMimeType() ?: 'application/pdf'),
                'file_size' => (int) ($file->getSize() ?: 0),
                'checksum_sha256' => (string) (hash_file('sha256', $absolutePath) ?: str_repeat('0', 64)),
                'notes' => isset($validated['revision_notes']) ? (string) $validated['revision_notes'] : null,
            ], (int) $admin['id']);
        } catch (Throwable $e) {
            return redirect()->route('admin.documents.php')->with('error', 'Terjadi kesalahan saat memproses upload revisi dokumen.');
        }

        $afterState = LegacyRepository::adminGetPolicyDocumentByCode(self::DOCUMENT_CODE);

        try {
            LegacyRepository::adminWriteAuditLog(
                (int) $admin['id'],
                'policy_document.revision_upload',
                'policy_documents',
                $documentId,
                $beforeState,
                $afterState,
                (string) $request->ip(),
                (string) $request->userAgent()
            );
        } catch (Throwable $e) {
            // Keep flow alive even when audit write fails.
        }

        return redirect()->route('admin.documents.php')->with('success', 'Revisi dokumen berhasil diunggah.');
    }
}
