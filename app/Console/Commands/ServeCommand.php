<?php

namespace App\Console\Commands;

use Illuminate\Foundation\Console\ServeCommand as BaseServeCommand;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'serve')]
class ServeCommand extends BaseServeCommand
{
    /**
     * Override host agar bind ke 0.0.0.0 supaya bisa diakses dari perangkat lain di jaringan LAN
     * (misal HP saat scan QR code).
     */
    protected function host(): string
    {
        return '0.0.0.0';
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // 1. Ambil port dan host yang akan digunakan
        $port = $this->port();
        $host = $this->host();

        // Tampilkan 127.0.0.1 di console agar user bisa klik link langsung
        $displayHost = '127.0.0.1';

        $this->components->info("Menjadwalkan pembukaan browser otomatis ke http://{$displayHost}:{$port}...");

        if (strncasecmp(PHP_OS, 'WIN', 3) === 0) {
            // Jalankan secara asynchronous menggunakan 'start /B' agar TIDAK memblokir artisan serve.
            // ping -n 3 memberikan jeda sekitar 2 detik agar server Laravel sempat booting terlebih dahulu.
            pclose(popen("start /B cmd /C \"ping 127.0.0.1 -n 3 >nul && start http://{$displayHost}:{$port}\"", "r"));
        } else {
            // Unix/Mac: jalankan di background menggunakan '&'
            $openCmd = stripos(PHP_OS, 'DARWIN') !== false ? 'open' : 'xdg-open';
            pclose(popen("sleep 2 && {$openCmd} http://{$displayHost}:{$port} > /dev/null 2>&1 &", "r"));
        }

        // 2. Jalankan server bawaan Laravel
        return parent::handle();
    }
}
