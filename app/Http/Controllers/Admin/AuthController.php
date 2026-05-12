<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Legacy\LegacyAuth;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (LegacyAuth::user()) {
            return redirect()->route('admin.dashboard.php');
        }

        $vendors = \DB::table('internal_companies')
            ->where('group_id', '!=', 58)
            ->orderBy('company_name')
            ->pluck('company_name')
            ->toArray();

        return view('admin.login', ['vendors' => $vendors]);
    }

    public function login(Request $request)
    {
        $type = $request->input('type', 'admin'); // 'admin' or 'vendor'

        if ($type === 'vendor') {
            return $this->vendorLogin($request);
        }

        return $this->adminLogin($request);
    }

    private function adminLogin(Request $request)
    {
        $email = trim((string) $request->input('email', ''));
        $password = (string) $request->input('password', '');

        if (LegacyAuth::isLoginLocked($email)) {
            $seconds = LegacyAuth::lockRemainingSeconds($email);
            $minutes = max(1, (int) ceil($seconds / 60));

            return redirect()->route('admin.login.php')->with('error', 'Terlalu banyak percobaan masuk. Coba kembali dalam '.$minutes.' menit.');
        }

        if (LegacyAuth::attempt($email, $password)) {
            if (LegacyAuth::requiresPasswordChange()) {
                return redirect()->route('admin.change-password.php')->with('error', 'Untuk keamanan akun, Anda wajib mengganti kata sandi sementara sekarang.');
            }

            return redirect()->route('admin.dashboard.php')->with('success', 'Berhasil masuk ke panel administrator.');
        }

        return redirect()->route('admin.login.php')->with('error', 'Email atau kata sandi tidak valid.');
    }

    private function vendorLogin(Request $request)
    {
        $companyName = trim((string) $request->input('company_name', ''));
        $password = (string) $request->input('password', '');
        $passwordless = (bool) config('legacy_auth.vendor_passwordless', true);

        if (empty($companyName) || (! $passwordless && empty($password))) {
            return redirect()->route('admin.login.php')->with('error', $passwordless
                ? 'Nama perusahaan harus diisi.'
                : 'Nama perusahaan dan kata sandi harus diisi.');
        }

        if (LegacyAuth::vendorAttempt($companyName, $password)) {
            return redirect()->route('admin.submissions.index')->with('success', 'Berhasil masuk sebagai vendor '.$companyName.'. Silakan kelola pengajuan Anda.');
        }

        return redirect()->route('admin.login.php')->with('error', $passwordless
            ? 'Nama perusahaan tidak valid.'
            : 'Nama perusahaan atau kata sandi tidak valid.');
    }

    public function autoLogin(Request $request)
    {
        $role = $request->input('role');
        if (LegacyAuth::loginByRole($role)) {
            return redirect()->route('admin.dashboard.php')->with('success', 'Quick Access: Berhasil masuk sebagai '.strtoupper($role));
        }

        return redirect()->route('admin.login.php')->with('error', 'Gagal masuk secara otomatis. Akun untuk role '.strtoupper($role).' tidak ditemukan.');
    }

    public function logout()
    {
        LegacyAuth::logout();

        return redirect()->route('admin.login.php')->with('success', 'Anda telah keluar dari sesi administrator.');
    }
}
