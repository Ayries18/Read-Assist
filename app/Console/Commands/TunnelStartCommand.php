<?php

namespace App\Console\Commands;

use App\Services\TunnelService;
use Illuminate\Console\Command;

class TunnelStartCommand extends Command
{
    protected $signature = 'tunnel:start|start:tunnel';
    protected $description = 'Mulai SSH tunnel (localhost.run) agar QR bisa discan dari jaringan luar';

    public function handle(TunnelService $tunnel): void
    {
        $this->info('Memulai tunnel ke localhost.run...');

        if ($tunnel->isRunning()) {
            $url = $tunnel->getUrl();
            $this->warn("Tunnel sudah berjalan: {$url}");

            if ($url) {
                config(['app.url' => $url]);
                $this->info('🔄 Regenerasi QR code untuk tunnel yang sudah aktif...');
                $this->call('qr:regenerate');
                $this->info('✅ QR code berhasil diregenerate menggunakan URL tunnel saat ini.');
            }

            return;
        }

        $port = $tunnel->getPort();
        $this->line("Port lokal: {$port}");

        $url = $tunnel->start();

        if ($url) {
            $this->info("✅ Tunnel berhasil! URL publik:");
            $this->line("   {$url}");
            $this->newLine();

            // Jangan update file .env secara fisik untuk menghindari crash restart otomatis pada Windows (php artisan serve).
            // Kita cukup melakukan set config secara in-memory agar perintah internal (seperti qr:regenerate) mendeteksi URL tunnel.
            // Sistem akan mendeteksi URL tunnel secara dinamis melalui TunnelService.
            config(['app.url' => $url]);

            $this->info("🔄 Menggunakan URL tunnel (in-memory config): {$url}");

            // Regenerate all QR codes immediately
            $this->call('qr:regenerate');

            $this->newLine();
            $this->line("Tunnel akan tetap berjalan sampai dihentikan (Tekan Ctrl+C untuk keluar).");

            // Registrasi shutdown function untuk mematikan tunnel saat script selesai/Ctrl+C
            register_shutdown_function(function () use ($tunnel) {
                $tunnel->stop();
            });

            // Biarkan perintah berjalan di foreground memantau tunnel
            while ($tunnel->isRunning()) {
                sleep(1);
            }
        } else {
            $this->error('❌ Gagal membuat tunnel. Pastikan SSH sudah terinstall.');
            $this->line('');
            $errFile = storage_path('app/tunnel_err.txt');
            if (file_exists($errFile)) {
                $errorOutput = trim(file_get_contents($errFile));
                if ($errorOutput !== '') {
                    $this->line('Detail error:');
                    $this->line($errorOutput);
                    $this->line('');
                }
            }
            $this->line('Coba manual: ssh -o StrictHostKeyChecking=no -R 80:127.0.0.1:' . $port . ' nokey@localhost.run');
        }
    }
}
