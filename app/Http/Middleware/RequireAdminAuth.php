<?php

namespace App\Http\Middleware;

use App\Support\Legacy\LegacyAuth;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireAdminAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! LegacyAuth::user()) {
            return redirect()->route('admin.login.php')->with('error', 'Silakan masuk sebagai administrator.');
        }

        return $next($request);
    }
}
