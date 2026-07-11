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

    public function boot(): void
    {
        try {
            // 1. Detect public URL from tunnel or request host (if already running)
            $publicUrl = $this->detectPublicUrl();

            if (!$publicUrl) {
                // Do not auto-fallback to a local IP address. Local network URLs should not overwrite
                // APP_URL because they break QR scanning from external devices.
                return;
            }

            $targetAppUrl = $publicUrl;
            config(['app.url' => $targetAppUrl]);

            // Avoid writing to .env on Windows since that can cause app restarts or permission issues.
            // Use the dynamic runtime URL only for QR generation during the current request.

            // Only regenerate QR codes, skip heavy cache clears
            $books = \App\Models\AudioBuku::all();
            $qrDir = storage_path('app/public/qr');
            if (!is_dir($qrDir)) {
                mkdir($qrDir, 0755, true);
            }

            foreach ($books as $book) {
                $qrUrl = "{$targetAppUrl}/scan/book/{$book->qr_token}";
                $svg = \SimpleSoftwareIO\QrCode\Facades\QrCode::size(300)
                    ->margin(2)
                    ->errorCorrection('M')
                    ->generate($qrUrl);
                file_put_contents($qrDir . '/qr-book-' . $book->id . '.svg', $svg);
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
                    if (!$isLocalIp && !filter_var($host, FILTER_VALIDATE_IP)) {
                        $scheme = $request->isSecure() ? 'https' : 'http';
                        $port = $request->getPort();
                        $portSuffix = ($port && $port != 80 && $port != 443) ? ":{$port}" : '';
                        return "{$scheme}://{$host}{$portSuffix}";
                    }
                }
            }
        } catch (\Exception $e) {
        }

        // 2. Detect from running localhost.run SSH tunnel
        try {
            if (class_exists(\App\Services\TunnelService::class)) {
                $tunnelService = new \App\Services\TunnelService();
                $tunnelUrl = $tunnelService->getUrl();
                if ($tunnelUrl) {
                    return rtrim($tunnelUrl, '/');
                }
            }
        } catch (\Exception $e) {
        }

        // 3. Fallback from cache
        try {
            $cached = \Illuminate\Support\Facades\Cache::get('app_url_fallback');
            if ($cached) return $cached;
        } catch (\Exception $e) {
        }

        return null;
    }
}
