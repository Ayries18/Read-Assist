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
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        try {
            // A. Copy both square logo and horizontal logo from uploaded files
            $squareSource = "C:\\Users\\ayrie\\.gemini\\antigravity\\brain\\daf70190-c1c1-4d84-a30a-b1e8aac78311\\media__1780073084242.png";
            $horizontalSource = "C:\\Users\\ayrie\\.gemini\\antigravity\\brain\\daf70190-c1c1-4d84-a30a-b1e8aac78311\\media__1780073259532.png";
            
            if (file_exists($squareSource)) {
                copy($squareSource, public_path('logo.png'));
            }
            if (file_exists($horizontalSource)) {
                copy($horizontalSource, public_path('logo-horizontal.png'));
            }


            // 1. Detect public URL
            $publicUrl = $this->detectPublicUrl();
            
            // 2. If no active public URL and in browser request, auto-boot the free localhost.run SSH tunnel
            if (!$publicUrl && !app()->runningInConsole()) {
                $publicUrl = \Illuminate\Support\Facades\Cache::remember('auto_ssh_tunnel_url', 30, function() {
                    if (class_exists(\App\Services\TunnelService::class)) {
                        $tunnelService = new \App\Services\TunnelService();
                        if (!$tunnelService->isRunning()) {
                            $tunnelService->start();
                        }
                        return $tunnelService->getUrl();
                    }
                    return null;
                });
            }

            if ($publicUrl) {
                $targetAppUrl = $publicUrl;
            } else {
                $detectedIp = \App\Http\Controllers\AudioBukuController::getDetectedIp();
                $targetAppUrl = "http://{$detectedIp}:8000";
            }
            
            $envPath = base_path('.env');
            $forceSync = request()->has('sync_ip');
            
            if (file_exists($envPath)) {
                $envContent = file_get_contents($envPath);
                
                if (preg_match('/^APP_URL=(.*)$/m', $envContent, $matches)) {
                    $currentAppUrl = trim($matches[1]);
                    
                    if ($currentAppUrl !== $targetAppUrl || $forceSync) {
                        // Replace APP_URL in .env
                        $envContent = preg_replace('/^APP_URL=.*$/m', "APP_URL={$targetAppUrl}", $envContent);
                        file_put_contents($envPath, $envContent);
                        
                        // Dynamically update the config helper in memory for this request
                        config(['app.url' => $targetAppUrl]);
                        
                        // Clear caches
                        \Illuminate\Support\Facades\Artisan::call('config:clear');
                        \Illuminate\Support\Facades\Artisan::call('cache:clear');
                        \Illuminate\Support\Facades\Artisan::call('route:clear');
                        \Illuminate\Support\Facades\Artisan::call('view:clear');
                        \Illuminate\Support\Facades\Artisan::call('optimize:clear');
                        
                        // Regenerate all book QR codes
                        $books = \App\Models\AudioBuku::all();
                        $qrDir = storage_path('app/public/qr');
                        if (!is_dir($qrDir)) {
                            mkdir($qrDir, 0755, true);
                        }
                        
                        foreach ($books as $book) {
                            $qrUrl = "{$targetAppUrl}/katalog-audio/{$book->id}";
                            $svg = \SimpleSoftwareIO\QrCode\Facades\QrCode::size(300)
                                ->margin(2)
                                ->errorCorrection('M')
                                ->generate($qrUrl);
                            file_put_contents($qrDir . '/qr-book-' . $book->id . '.svg', $svg);
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            // Silently fail to not block boot in CLI/other edge cases
        }
    }

    /**
     * Detect if a public URL is active (incoming request or SSH Tunnel).
     */
    private function detectPublicUrl(): ?string
    {
        // 1. Detect from incoming request hostname if it's a public domain
        try {
            $request = request();
            if ($request) {
                $host = $request->getHost();
                if ($host && $host !== 'localhost' && $host !== '127.0.0.1' && $host !== '0.0.0.0') {
                    // Check if it's not a local IP address
                    $isLocalIp = preg_match('/^(10\.|172\.(1[6-9]|2\d|3[01])\.|192\.168\.)/', $host);
                    if (!$isLocalIp && !filter_var($host, FILTER_VALIDATE_IP)) {
                        $scheme = $request->isSecure() ? 'https' : 'http';
                        $port = $request->getPort();
                        // Standard ports don't need suffix
                        $portSuffix = ($port && $port != 80 && $port != 443) ? ":{$port}" : '';
                        return "{$scheme}://{$host}{$portSuffix}";
                    }
                }
            }
        } catch (\Exception $e) {
            // Ignore
        }

        // 2. Detect from running localhost.run SSH tunnel
        try {
            if (class_exists(\App\Services\TunnelService::class)) {
                $tunnelService = new \App\Services\TunnelService();
                $tunnelUrl = $tunnelService->getStoredUrl();
                if ($tunnelUrl) {
                    return rtrim($tunnelUrl, '/');
                }
            }
        } catch (\Exception $e) {
            // Ignore
        }

        return null;
    }
}
