<?php

namespace App\Http\Middleware;

use App\Support\Legacy\LegacyAuth;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireAdminEmailWorkflowAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! (bool) config('admin_email_workflow.enabled', true)) {
            return redirect()->route('admin.dashboard.php')
                ->with('error', 'Fitur email pengajuan dinonaktifkan oleh konfigurasi sistem.');
        }

        $admin = LegacyAuth::user();
        if (! $admin) {
            return redirect()->route('admin.login.php')->with('error', 'Silakan masuk sebagai administrator.');
        }

        $allowedUserIds = array_values(array_filter(
            (array) config('admin_email_workflow.allowed_user_ids', []),
            static fn (mixed $value): bool => (int) $value > 0
        ));
        $allowedEmails = array_values(array_filter(
            array_map(static fn (mixed $value): string => strtolower(trim((string) $value)), (array) config('admin_email_workflow.allowed_emails', [])),
            static fn (string $value): bool => $value !== ''
        ));

        $currentUserId = (int) ($admin['id'] ?? 0);
        $currentEmail = strtolower(trim((string) ($admin['email'] ?? '')));

        // Keep behavior consistent with admin monitoring route:
        // if allow-list is empty, all authenticated admins can access.
        if ($allowedUserIds === [] && $allowedEmails === []) {
            return $next($request);
        }

        $allowedById = $currentUserId > 0 && in_array($currentUserId, $allowedUserIds, true);
        $allowedByEmail = $currentEmail !== '' && in_array($currentEmail, $allowedEmails, true);

        if (! $allowedById && ! $allowedByEmail) {
            return redirect()->route('admin.dashboard.php')
                ->with('error', 'Anda tidak memiliki akses ke modul email pengajuan SIMPER.');
        }

        return $next($request);
    }
}
