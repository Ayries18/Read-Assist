<?php

namespace App\Providers;

use App\Services\TunnelService;
use Illuminate\Support\Facades\Cache;
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

    public function boot(): void
    {
        try {
            // Detect public URL from tunnel or request host if available
            $publicUrl = $this->detectPublicUrl();

            if ($publicUrl) {
                config(['app.url' => $publicUrl]);
            }
        } catch (\Exception $e) {
            // Silently fail to not block boot
        }
    }

    private function detectPublicUrl(): ?string
    {
        // 1. Detect from incoming request hostname if it's a public domain
        try {
            $request = request();
            if ($request) {
                $host = $request->getHost();
                if ($host && $host !== 'localhost' && $host !== '127.0.0.1' && $host !== '0.0.0.0') {
                    $isLocalIp = preg_match('/^(10\.|172\.(1[6-9]|2\d|3[01])\.|192\.168\.)/', $host);
                    if (! $isLocalIp && ! filter_var($host, FILTER_VALIDATE_IP)) {
                        $scheme = $request->isSecure() ? 'https' : 'http';
                        $port = $request->getPort();
                        $portSuffix = ($port && $port != 80 && $port != 443) ? ":{$port}" : '';

                        return "{$scheme}://{$host}{$portSuffix}";
                    }
                }
            }
        } catch (\Exception $e) {
        }

        // 2. Detect from running localhost.run SSH tunnel.
        //    Pakai getStoredUrl() (bukan getUrl()) agar URL tunnel yang basi /
        //    sudah mati TIDAK dipakai sebagai APP_URL. getStoredUrl() memastikan
        //    proses ssh.exe benar-benar hidup sebelum mengembalikan URL.
        //    Hanya berlaku di environment non-production (tunnel = fasilitas dev).
        try {
            if (class_exists(TunnelService::class) && ! app()->environment('production')) {
                $tunnelService = new TunnelService;
                $tunnelUrl = $tunnelService->getStoredUrl();
                if ($tunnelUrl) {
                    return rtrim($tunnelUrl, '/');
                }
            }
        } catch (\Exception $e) {
        }

        // 3. Tidak ada fallback dari cache untuk APP_URL — cache bisa menyimpan
        //    URL tunnel basi dan meracuni QR. Biarkan APP_URL dari .env dipakai.

        return null;
    }
}
