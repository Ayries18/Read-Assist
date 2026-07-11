<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class TunnelService
{
    protected string $urlFile;
    protected string $pidFile;
    protected int $port;
    protected string $sshPath;
    protected $process;
    protected array $pipes = [];

    public function __construct()
    {
        $this->urlFile = storage_path('app/tunnel_url.txt');
        $this->pidFile = storage_path('app/tunnel_pid.txt');
        $this->port = (int) env('TUNNEL_PORT', 8000);
        $this->sshPath = $this->resolveSshPath();
    }

    protected function resolveSshPath(): string
    {
        $sshPath = trim(shell_exec('powershell -NoProfile -Command "Get-Command ssh | Select-Object -ExpandProperty Source"') ?: '');
        return $sshPath ?: 'ssh';
    }

    public function start(): ?string
    {
        $this->stop();

        $errFile = storage_path('app/tunnel_err.txt');

        // Pastikan file temporary dibersihkan dulu
        @unlink($this->urlFile);
        @unlink($errFile);

        // Gunakan path absolut ssh yang terdeteksi
        $cmd = sprintf(
            '"%s" -o StrictHostKeyChecking=no -o ServerAliveInterval=30 -R 80:127.0.0.1:%d nokey@localhost.run',
            $this->sshPath,
            $this->port
        );

        $descriptorspec = [
            0 => ['pipe', 'r'],                  // stdin
            1 => ['pipe', 'w'],                  // stdout (pipe untuk menghindari buffering file OS)
            2 => ['file', $errFile, 'w']         // stderr
        ];

        // bypass_shell sangat penting di Windows agar memanggil executable secara langsung tanpa dibungkus cmd.exe
        $options = [];
        if (strncasecmp(PHP_OS, 'WIN', 3) === 0) {
            $options = ['bypass_shell' => true];
        }

        $process = proc_open($cmd, $descriptorspec, $pipes, null, null, $options);

        if (is_resource($process)) {
            $status = proc_get_status($process);
            $pid = $status['pid'];
            $this->process = $process;
            $this->pipes = $pipes;
            file_put_contents($this->pidFile, $pid);
        } else {
            Log::error('SSH tunnel failed to start via proc_open');
            return null;
        }

        // Set pipe stdout agar non-blocking agar pembacaan fgets/fread tidak menahan eksekusi
        stream_set_blocking($pipes[1], false);

        $stdoutAccumulator = '';
        $detectedUrl = null;

        // Loop pengecekan URL publik hingga 30 detik (150 * 200ms)
        for ($i = 0; $i < 150; $i++) {
            usleep(200000);

            // Baca output real-time dari stream
            $data = fread($pipes[1], 8192);
            if ($data !== false && $data !== '') {
                $stdoutAccumulator .= $data;

                $content = $stdoutAccumulator;
                if (str_contains($content, "\0")) {
                    $content = mb_convert_encoding($content, 'UTF-8', 'UTF-16');
                }

                // Coba cocokkan URL tunnel subdomain dari output yang terkumpul
                if (preg_match('/https:\/\/[a-zA-Z0-9\-]+\.(?:lhr\.life|localhost\.run)/', $content, $match)) {
                    $detectedUrl = $match[0];
                    break;
                }
            }

            // Jika proses SSH ternyata sudah mati di tengah jalan, hentikan loop
            $status = proc_get_status($process);
            if (!$status['running']) {
                break;
            }
        }

        if ($detectedUrl) {
            // Tulis URL ke file agar bisa diakses oleh request web lain
            file_put_contents($this->urlFile, $detectedUrl);
            @unlink($errFile);
            return $detectedUrl;
        }

        // Jika gagal mendapatkan URL setelah 30 detik, stop tunnel agar proses tidak menggantung,
        // tapi JANGAN hapus $errFile agar command bisa menampilkan detail errornya kepada user.
        $this->stop();
        return null;
    }

    public function stop(): void
    {
        $pid = $this->getPid();
        if ($pid) {
            shell_exec("taskkill /PID {$pid} /F 2>NUL");
        }

        // Paksa matikan semua ssh.exe di Windows untuk mencegah proses yatim (orphan)
        shell_exec('taskkill /F /IM ssh.exe 2>NUL');

        foreach ($this->pipes as $pipe) {
            if (is_resource($pipe)) {
                @fclose($pipe);
            }
        }
        $this->pipes = [];

        if (is_resource($this->process)) {
            @proc_close($this->process);
            $this->process = null;
        }

        @unlink($this->pidFile);
        @unlink($this->urlFile);
    }

    public function getUrl(): ?string
    {
        if (!file_exists($this->urlFile) || filesize($this->urlFile) === 0) {
            return null;
        }

        $content = file_get_contents($this->urlFile);
        if ($content === false || $content === '') {
            return null;
        }

        if (str_contains($content, "\0")) {
            $content = mb_convert_encoding($content, 'UTF-8', 'UTF-16');
        }

        // Hanya cocokkan URL dengan subdomain aktif (misal xxxx.lhr.life atau xxxx.localhost.run)
        // Mengecualikan URL dokumentasi localhost.run (karena tidak memiliki subdomain tambahan)
        if (preg_match_all('/https:\/\/[a-zA-Z0-9\-]+\.(?:lhr\.life|localhost\.run)/', $content, $matches)) {
            return end($matches[0]);
        }

        return null;
    }

    public function isRunning(): bool
    {
        $pid = $this->getPid();
        if (!$pid) {
            return false;
        }

        $output = shell_exec("tasklist /NH /FI \"PID eq {$pid}\" 2>NUL");
        if (!$output) {
            return false;
        }

        // Jika proses sudah mati, tasklist Windows akan mengembalikan pesan berawalan "INFO:" (misal: "INFO: No tasks..." atau "INFO: Tidak ada tugas...")
        // Jadi jika tidak ada kata "INFO:", berarti proses tersebut masih berjalan aktif.
        return strpos($output, 'INFO:') === false;
    }

    public function getPid(): ?string
    {
        if (!file_exists($this->pidFile)) {
            return null;
        }
        $pid = trim(file_get_contents($this->pidFile) ?: '');
        return $pid ?: null;
    }

    public function getPort(): int
    {
        return $this->port;
    }

    public function getLocalIp(): ?string
    {
        $output = shell_exec('ipconfig 2>NUL');
        if (!$output) return null;

        $lines = explode("\n", $output);
        $ip = null;
        foreach ($lines as $line) {
            $line = trim($line);
            if (preg_match('/IPv4.*:\s*(\d+\.\d+\.\d+\.\d+)/', $line, $m)) {
                $ip = $m[1];
                if (!str_contains($line, 'VirtualBox') && !str_contains($line, 'VMware') && !str_contains($line, 'WSL')) {
                    break;
                }
            }
        }
        return $ip;
    }

    public function getStoredUrl(): ?string
    {
        if ($this->isRunning()) {
            return $this->getUrl();
        }

        $ngrokUrl = $this->getNgrokUrl();
        if ($ngrokUrl) {
            return $ngrokUrl;
        }

        return null;
    }

    public function getNgrokUrl(): ?string
    {
        try {
            // Fast check (0.1s timeout) to see if Ngrok is even listening
            $connection = @fsockopen('127.0.0.1', 4040, $errno, $errstr, 0.1);
            if (!$connection) {
                return null;
            }
            fclose($connection);

            $ctx = stream_context_create(['http' => ['timeout' => 1.0]]);
            $result = @file_get_contents('http://127.0.0.1:4040/api/tunnels', false, $ctx);
            if ($result === false) {
                return null;
            }
            $data = json_decode($result, true);
            if (!$data || !isset($data['tunnels'])) {
                return null;
            }
            foreach ($data['tunnels'] as $tunnel) {
                if (!empty($tunnel['public_url'])) {
                    return $tunnel['public_url'];
                }
            }
        } catch (\Exception $e) {
            return null;
        }
        return null;
    }
}
