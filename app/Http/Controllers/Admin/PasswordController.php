<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Legacy\LegacyAuth;
use App\Support\Legacy\LegacyRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class PasswordController extends Controller
{
    public function edit()
    {
        $admin = LegacyAuth::user();
        $userRole = DB::table('users')->where('id', $admin['id'] ?? 0)->value('role') ?? 'admin';

        return view('admin.change-password', [
            'admin' => $admin,
            'userRole' => $userRole,
            'minLength' => LegacyAuth::minPasswordLength(),
        ]);
    }

    public function update(Request $request)
    {
        $admin = LegacyAuth::user();
        if (! $admin) {
            return redirect()->route('admin.login.php')->with('error', 'Silakan masuk sebagai administrator.');
        }

        $currentPassword = (string) $request->input('current_password', '');
        $newPassword = (string) $request->input('new_password', '');
        $confirmPassword = (string) $request->input('confirm_password', '');

        $errors = [];

        if (! LegacyAuth::verifyUserPassword((int) $admin['id'], $currentPassword)) {
            $errors[] = 'Kata sandi saat ini tidak sesuai.';
        }

        if (! LegacyAuth::passwordMeetsPolicy($newPassword)) {
            $errors[] = 'Kata sandi baru belum memenuhi kebijakan keamanan.';
        }

        if ($newPassword !== $confirmPassword) {
            $errors[] = 'Konfirmasi kata sandi baru tidak sesuai.';
        }

        if ($currentPassword === $newPassword) {
            $errors[] = 'Kata sandi baru harus berbeda dari kata sandi saat ini.';
        }

        if ($errors) {
            return redirect()->route('admin.change-password.php')->with('error', implode(' ', $errors));
        }

        LegacyAuth::updateUserPassword((int) $admin['id'], $newPassword);

        try {
            LegacyRepository::adminWriteAuditLog(
                (int) $admin['id'],
                'user.password_change',
                'users',
                (int) $admin['id'],
                ['must_change_password' => 1],
                ['must_change_password' => 0],
                (string) $request->ip(),
                (string) $request->userAgent()
            );
        } catch (Throwable $e) {
            // Keep flow alive even when audit write fails.
        }

        return redirect()->route('admin.dashboard.php')->with('success', 'Kata sandi berhasil diperbarui.');
    }
}
