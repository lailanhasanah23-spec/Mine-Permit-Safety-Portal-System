<?php

namespace App\Providers;

use App\Support\Legacy\LegacyAuth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 1. Deteksi Host asli dan Protokol jika di belakang proxy (ngrok)
        $proxyHost = request()->header('X-Forwarded-Host');
        $proxyProto = request()->header('X-Forwarded-Proto');

        if ($proxyHost) {
            // Paksa Root URL menggunakan domain publik ngrok agar aset tidak lari ke .test
            $scheme = $proxyProto ?: 'https';
            URL::forceRootUrl($scheme.'://'.$proxyHost.request()->getBaseUrl());
            URL::forceScheme($scheme);
        }
        // 2. Fallback untuk deteksi ngrok standar
        elseif (str_contains(request()->getHost(), 'ngrok')) {
            URL::forceScheme('https');
            $rootUrl = request()->getSchemeAndHttpHost().request()->getBaseUrl();
            URL::forceRootUrl($rootUrl);
        }
        // Share current admin and role to all views to make role checks consistent
        try {
            $admin = LegacyAuth::user();
            $userId = (int) ($admin['id'] ?? 0);
            if ($userId > 0) {
                $row = DB::table('users')->where('id', $userId)->first();
                $userRole = (string) ($row->role ?? '');
            } else {
                $userRole = '';
            }
        } catch (\Throwable $e) {
            $admin = null;
            $userRole = '';
        }

        View::share('admin', $admin);
        View::share('userRole', $userRole);
    }
}
