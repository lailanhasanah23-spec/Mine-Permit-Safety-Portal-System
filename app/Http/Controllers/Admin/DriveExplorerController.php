<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GoogleToken;
use App\Services\GoogleDriveService;
use App\Support\Legacy\LegacyAuth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DriveExplorerController extends Controller
{
    protected $driveService;

    public function __construct(GoogleDriveService $driveService)
    {
        $this->driveService = $driveService;
    }

    public function index(Request $request)
    {
        $admin = LegacyAuth::user();
        $userId = $admin['id'] ?? null;
        $folderId = $request->query('folder_id', 'root');

        try {
            // Check if user has token
            $hasToken = GoogleToken::where('service_name', 'drive')
                ->where('user_id', $userId)
                ->exists();

            if (! $hasToken) {
                return view('admin.drive-explorer', [
                    'admin' => $admin,
                    'userRole' => LegacyAuth::isVendor() ? 'subcon' : (DB::table('users')->where('id', $admin['id'] ?? 0)->value('role') ?? 'admin'),
                    'needsAuth' => true,
                    'authUrl' => route('admin.google.auth', [
                        'service' => 'drive',
                        'user_auth' => 1,
                        'redirect' => $request->fullUrl(),
                    ]),
                    'mode' => $request->query('mode'),
                    'target' => $request->query('target'),
                ]);
            }

            $folders = $this->driveService->listFolders($folderId, $userId);
            $files = $this->driveService->listFiles($folderId, $userId);

            // Get current folder info
            $currentFolder = null;
            if ($folderId !== 'root') {
                $currentFolder = $this->driveService->getFileMetadata($folderId, $userId);
            }

            return view('admin.drive-explorer', [
                'admin' => $admin,
                'userRole' => LegacyAuth::isVendor() ? 'subcon' : (DB::table('users')->where('id', $admin['id'] ?? 0)->value('role') ?? 'admin'),
                'folders' => $folders,
                'files' => $files,
                'currentFolder' => $currentFolder,
                'folderId' => $folderId,
                'mode' => $request->query('mode'), // 'pick' or null
                'target' => $request->query('target'), // Target field ID
                'needsAuth' => false,
            ]);
        } catch (\Exception $e) {
            Log::error('Drive Explorer Error: '.$e->getMessage());

            // If token is invalid/expired and refresh failed, re-auth
            if (str_contains($e->getMessage(), 'authorize') || str_contains($e->getMessage(), 'linked')) {
                return view('admin.drive-explorer', [
                    'admin' => $admin,
                    'userRole' => LegacyAuth::isVendor() ? 'subcon' : (DB::table('users')->where('id', $admin['id'] ?? 0)->value('role') ?? 'admin'),
                    'needsAuth' => true,
                    'authUrl' => route('admin.google.auth', [
                        'service' => 'drive',
                        'user_auth' => 1,
                        'redirect' => $request->fullUrl(),
                    ]),
                    'error' => $e->getMessage(),
                    'mode' => $request->query('mode'),
                    'target' => $request->query('target'),
                ]);
            }

            return redirect()->route('admin.submissions.index')->with('error', 'Drive Error: '.$e->getMessage());
        }
    }
}
