<?php

namespace App\Providers;

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
        // APP_URL diambil dari .env — jangan di-override dengan request host.
        // Override menyebabkan QR code selalu berisi localhost/127.0.0.1/IP lokal
        // yang tidak bisa diakses dari HP Android.
    }
}
