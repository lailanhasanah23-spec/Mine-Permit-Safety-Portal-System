<?php

namespace App\Http\Middleware;

use App\Support\Legacy\LegacyAuth;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class RequireSimperRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $admin = LegacyAuth::user();
        if (! $admin) {
            return redirect()->route('admin.login.php')->with('error', 'Silakan masuk sebagai administrator.');
        }

        if (LegacyAuth::isVendor()) {
            $userRole = 'subcon';
        } else {
            $userId = (int) ($admin['id'] ?? 0);
            $userRow = DB::table('users')->where('id', $userId)->first();
            $userRole = $userRow->role ?? 'admin';
        }

        // Add 'admin' to user session if needed or just use DB role.
        if (empty($roles) || $userRole === 'admin') {
            return $next($request);
        }

        if (! in_array($userRole, $roles, true)) {
            return redirect()->route('admin.submissions.index')
                ->with('error', 'Anda tidak memiliki izin (role) untuk melakukan aksi ini.');
        }

        return $next($request);
    }
}
