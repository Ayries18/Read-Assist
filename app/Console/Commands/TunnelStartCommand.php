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
            return;
        }

        $port = $tunnel->getPort();
        $this->line("Port lokal: {$port}");

        $url = $tunnel->start();

        if ($url) {
            $this->info("✅ Tunnel berhasil! URL publik:");
            $this->line("   {$url}");
            $this->newLine();
            $this->line("Gunakan URL ini untuk QR code atau bagikan ke HP.");
            $this->line("Tunnel akan tetap berjalan sampai dihentikan (tunnel:stop).");
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
