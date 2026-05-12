<?php

namespace App\Providers;

use App\Mail\Transport\GmailTransport;
use App\Services\GoogleDriveService;
use App\Services\GoogleMailService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\ServiceProvider;

class GoogleMailServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(GoogleMailService::class, function ($app) {
            return new GoogleMailService;
        });

        $this->app->singleton(GoogleDriveService::class, function ($app) {
            return new GoogleDriveService($app->make(GoogleMailService::class));
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Mail::extend('gmail', function (array $config) {
            return new GmailTransport($this->app->make(GoogleMailService::class));
        });
    }
}
