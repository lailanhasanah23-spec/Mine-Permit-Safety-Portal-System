<?php

namespace App\Services;

use App\Models\Submission;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class GoogleDriveService
{
    protected $googleService;

    public function __construct(GoogleMailService $googleService)
    {
        $this->googleService = $googleService;
    }

    public function listFolders($parent = 'root', $userId = null)
    {
        $client = $this->googleService->getClient($userId, 'drive');
        $service = new Drive($client);

        $optParams = [
            'pageSize' => 50,
            'fields' => 'nextPageToken, files(id, name, mimeType, webViewLink)',
            'q' => "mimeType = 'application/vnd.google-apps.folder' and '$parent' in parents and trashed = false",
        ];

        try {
            $results = $service->files->listFiles($optParams);

            return $results->getFiles();
        } catch (\Exception $e) {
            Log::error('Google Drive Folder List Error: '.$e->getMessage());
            throw $e;
        }
    }

    public function listFiles($folderId, $userId = null)
    {
        $client = $this->googleService->getClient($userId, 'drive');
        $service = new Drive($client);

        $optParams = [
            'pageSize' => 100,
            'fields' => 'nextPageToken, files(id, name, mimeType, webViewLink, iconLink, size, createdTime)',
            'q' => "'$folderId' in parents and trashed = false",
        ];

        try {
            $results = $service->files->listFiles($optParams);

            return $results->getFiles();
        } catch (\Exception $e) {
            Log::error('Google Drive File List Error: '.$e->getMessage());
            throw $e;
        }
    }

    public function searchFolders($query, $userId = null)
    {
        $client = $this->googleService->getClient($userId, 'drive');
        $service = new Drive($client);

        $safeQuery = str_replace("'", "\\'", $query);
        $optParams = [
            'pageSize' => 20,
            'fields' => 'files(id, name, webViewLink)',
            'q' => "mimeType = 'application/vnd.google-apps.folder' and name contains '$safeQuery' and trashed = false",
        ];

        try {
            $results = $service->files->listFiles($optParams);

            return $results->getFiles();
        } catch (\Exception $e) {
            Log::error('Google Drive Folder Search Error: '.$e->getMessage());
            throw $e;
        }
    }

    public function getFileMetadata($fileId, $userId = null)
    {
        $client = $this->googleService->getClient($userId, 'drive');
        $service = new Drive($client);

        try {
            return $service->files->get($fileId, ['fields' => 'id, name, mimeType, webViewLink, permissions']);
        } catch (\Exception $e) {
            Log::error('Google Drive File Metadata Error: '.$e->getMessage());
            throw $e;
        }
    }

    public function ensureSubmissionFolder(Submission $submission, $userId = null): array
    {
        if (! empty($submission->gdrive_folder_id)) {
            return [
                'folder_id' => $submission->gdrive_folder_id,
                'segments' => $this->buildSubmissionFolderSegments($submission),
            ];
        }

        $segments = $this->buildSubmissionFolderSegments($submission);
        $folderId = $this->ensureFolderPath($segments, $userId);

        return [
            'folder_id' => $folderId,
            'segments' => $segments,
        ];
    }

    public function buildSubmissionFolderPath(Submission $submission): string
    {
        return implode('/', $this->buildSubmissionFolderSegments($submission));
    }

    public function ensureFolderPath(array $segments, $userId = null, string $parentId = 'root'): string
    {
        $currentParent = $parentId;

        foreach ($segments as $segment) {
            $currentParent = $this->findOrCreateFolder((string) $segment, $currentParent, $userId);
        }

        return $currentParent;
    }

    protected function findOrCreateFolder(string $name, string $parentId = 'root', $userId = null): string
    {
        $existing = $this->findFolderId($name, $parentId, $userId);

        if ($existing) {
            return $existing;
        }

        return $this->createFolder($name, $parentId, $userId);
    }

    protected function findFolderId(string $name, string $parentId = 'root', $userId = null): ?string
    {
        $client = $this->googleService->getClient($userId);
        $service = new Drive($client);
        $safeName = str_replace("'", "\\'", $name);

        $optParams = [
            'pageSize' => 1,
            'fields' => 'files(id, name)',
            'q' => "mimeType = 'application/vnd.google-apps.folder' and name = '$safeName' and '$parentId' in parents and trashed = false",
        ];

        try {
            $results = $service->files->listFiles($optParams);
            $files = $results->getFiles();

            return ! empty($files) ? $files[0]->getId() : null;
        } catch (\Exception $e) {
            Log::error('Google Drive Folder Lookup Error: '.$e->getMessage());
            throw $e;
        }
    }

    protected function createFolder(string $name, string $parentId = 'root', $userId = null): string
    {
        $client = $this->googleService->getClient($userId);
        $service = new Drive($client);

        $folder = new DriveFile;
        $folder->setName($name);
        $folder->setMimeType('application/vnd.google-apps.folder');
        $folder->setParents([$parentId]);

        try {
            $created = $service->files->create($folder, [
                'fields' => 'id, name, webViewLink',
            ]);

            return $created->getId();
        } catch (\Exception $e) {
            Log::error('Google Drive Folder Create Error: '.$e->getMessage());
            throw $e;
        }
    }

    protected function buildSubmissionFolderSegments(Submission $submission): array
    {
        $submission->loadMissing(['vendor', 'creator']);

        $dateSegment = $submission->created_at
            ? $submission->created_at->format('Y-m-d')
            : now()->format('Y-m-d');

        $rootSegment = $submission->submitted_by_vendor ? 'subcon-vendor' : 'hgra';

        $ownerName = $submission->submitted_by_vendor
            ? ($submission->vendor->company_name ?? $submission->applicant_name ?? 'vendor')
            : ($submission->creator->full_name ?? $submission->creator->name ?? $submission->applicant_name ?? 'user');

        $ownerSegment = Str::slug((string) $ownerName);
        if ($ownerSegment === '') {
            $ownerSegment = $submission->submitted_by_vendor ? 'vendor' : 'user';
        }

        return [
            $rootSegment,
            $dateSegment,
            $ownerSegment,
            'pengajuan-'.$submission->id,
        ];
    }
}
