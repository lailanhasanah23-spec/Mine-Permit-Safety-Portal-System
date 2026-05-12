<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\GoogleMailService;
use App\Support\Legacy\LegacyAuth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GoogleAuthController extends Controller
{
    protected $googleMailService;

    public function __construct(GoogleMailService $googleMailService)
    {
        $this->googleMailService = $googleMailService;
    }

    public function auth(Request $request)
    {
        $purpose = $request->query('service', 'gmail');
        if (! in_array($purpose, ['gmail', 'drive'])) {
            $purpose = 'gmail';
        }

        if ($request->has('user_auth')) {
            session(['google_user_auth' => true]);
            session(['google_auth_redirect' => $request->query('redirect', route('admin.dashboard.php'))]);
            session(['google_auth_service' => $purpose]);
        } else {
            session()->forget(['google_user_auth', 'google_auth_redirect', 'google_auth_service']);
        }

        return redirect()->away($this->googleMailService->getAuthUrl($purpose));
    }

    public function callback(Request $request)
    {
        $isUserAuth = session('google_user_auth', false);
        $redirectUrl = session('google_auth_redirect', route('admin.email-submissions.php'));
        $purpose = session('google_auth_service', 'gmail');

        if ($request->has('error')) {
            return redirect($redirectUrl)->with('error', 'Google Auth Error: '.$request->error);
        }

        if (! $request->has('code')) {
            return redirect($redirectUrl)->with('error', 'Google Auth Code not found.');
        }

        try {
            $userId = null;
            if ($isUserAuth) {
                $admin = LegacyAuth::user();
                $userId = $admin['id'] ?? null;
            }

            $this->googleMailService->authenticate($request->code, $userId, $purpose);

            session()->forget(['google_user_auth', 'google_auth_redirect', 'google_auth_service']);

            return redirect($redirectUrl)->with('success', $isUserAuth ? 'Google account linked successfully.' : 'Google account linked successfully for email sending.');
        } catch (\Exception $e) {
            Log::error('Google Auth Callback Error: '.$e->getMessage());

            return redirect($redirectUrl)->with('error', 'Failed to link Google account: '.$e->getMessage());
        }
    }
}
