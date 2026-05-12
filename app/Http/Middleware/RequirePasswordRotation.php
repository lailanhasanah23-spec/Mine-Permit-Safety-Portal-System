<?php

namespace App\Http\Middleware;

use App\Support\Legacy\LegacyAuth;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequirePasswordRotation
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! LegacyAuth::requiresPasswordChange()) {
            return $next($request);
        }

        if ($request->routeIs('admin.change-password') || $request->routeIs('admin.change-password.php') || $request->routeIs('admin.logout') || $request->routeIs('admin.logout.php')) {
            return $next($request);
        }

        return redirect()->route('admin.change-password.php')->with('error', 'Untuk keamanan akun, Anda wajib mengganti kata sandi sementara sebelum melanjutkan.');
    }
}
