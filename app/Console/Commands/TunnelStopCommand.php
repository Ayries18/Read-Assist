<?php

namespace App\Console\Commands;

use App\Services\TunnelService;
use Illuminate\Console\Command;

class TunnelStopCommand extends Command
{
    protected $signature = 'tunnel:stop';
    protected $description = 'Hentikan SSH tunnel';

    public function handle(TunnelService $tunnel): void
    {
        if (!$tunnel->isRunning()) {
            $this->warn('Tunnel tidak sedang berjalan.');
            return;
        }

        $tunnel->stop();
        $this->info('✅ Tunnel dihentikan.');
    }
}
