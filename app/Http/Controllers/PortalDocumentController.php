<?php

namespace App\Http\Controllers;

use App\Support\Legacy\LegacyRepository;
use Illuminate\Support\Facades\Storage;

class PortalDocumentController extends Controller
{
    public function show(string $code)
    {
        $document = LegacyRepository::portalGetPublicPolicyDocumentByCode($code);

        if (! $document) {
            abort(404);
        }

        $revisions = LegacyRepository::portalGetPublicPolicyDocumentRevisions((int) $document['id'], 12);

        return view('portal.document', [
            'document' => $document,
            'revisions' => $revisions,
        ]);
    }

    public function file(string $code)
    {
        $document = LegacyRepository::portalGetPublicPolicyDocumentByCode($code);

        if (! $document) {
            abort(404);
        }

        $requestedRevisionId = max(0, (int) request()->query('revision', 0));
        if ($requestedRevisionId > 0) {
            $revision = LegacyRepository::portalGetPublicPolicyDocumentRevisionById((int) $document['id'], $requestedRevisionId);
            if ($revision) {
                $document['stored_path'] = $revision['stored_path'] ?? $document['stored_path'];
                $document['mime_type'] = $revision['mime_type'] ?? $document['mime_type'];
                $document['file_size'] = $revision['file_size'] ?? $document['file_size'];
                $document['original_filename'] = $revision['original_filename'] ?? $document['original_filename'];
            }
        }

        $storedPath = trim((string) ($document['stored_path'] ?? ''));
        if ($storedPath === '' || ! Storage::disk('local')->exists($storedPath)) {
            abort(404);
        }

        $stream = Storage::disk('local')->readStream($storedPath);
        if (! is_resource($stream)) {
            abort(404);
        }

        $mimeType = trim((string) ($document['mime_type'] ?? 'application/pdf'));
        if ($mimeType === '') {
            $mimeType = 'application/pdf';
        }

        $filename = trim((string) ($document['original_filename'] ?? 'document.pdf'));
        if ($filename === '') {
            $filename = 'document.pdf';
        }

        $safeFilename = preg_replace('/[^A-Za-z0-9._-]+/', '_', $filename) ?: 'document.pdf';

        return response()->stream(function () use ($stream): void {
            fpassthru($stream);
            if (is_resource($stream)) {
                fclose($stream);
            }
        }, 200, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="'.$safeFilename.'"',
            'Content-Length' => (string) max(0, (int) ($document['file_size'] ?? 0)),
            'Cache-Control' => 'public, max-age=300, must-revalidate',
            'X-Robots-Tag' => 'noindex, nofollow, noarchive',
        ]);
    }
}
