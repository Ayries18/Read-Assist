<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class TunnelService
{
    protected string $urlFile;
    protected string $pidFile;
    protected int $port;
    protected string $sshPath;

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

        $psCommand = sprintf(
            "Start-Process -NoNewWindow -PassThru -FilePath '%s' -ArgumentList '-o', 'StrictHostKeyChecking=no', '-o', 'ServerAliveInterval=30', '-R', '80:127.0.0.1:%d', 'nokey@localhost.run' -RedirectStandardOutput '%s' -RedirectStandardError '%s' | Select-Object -ExpandProperty Id",
            $this->sshPath,
            $this->port,
            $this->urlFile,
            $errFile
        );

        $pid = shell_exec("powershell -NoProfile -Command \"{$psCommand}\"");
        $pid = trim($pid ?? '');

        if ($pid && is_numeric($pid)) {
            file_put_contents($this->pidFile, $pid);
        } else {
            $errorOutput = file_exists($errFile) ? trim(file_get_contents($errFile)) : '';
            Log::error('SSH tunnel failed to start', [
                'pid' => $pid,
                'error' => $errorOutput,
                'port' => $this->port,
            ]);
        }

        for ($i = 0; $i < 50; $i++) {
            usleep(200000);
            $url = $this->getUrl();
            if ($url !== null) {
                @unlink($errFile);
                return $url;
            }
        }

        @unlink($errFile);
        return null;
    }

    public function stop(): void
    {
        $pid = $this->getPid();
        if ($pid) {
            shell_exec("taskkill /PID {$pid} /F 2>NUL");
        }

        shell_exec('taskkill /F /FI "WINDOWTITLE eq ssh*" /IM ssh.exe 2>NUL');

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

        if (preg_match_all('/https:\/\/[a-zA-Z0-9\-\.]+(?:\.lhr\.life|\.localhost\.run)/', $content, $matches)) {
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
        return $output && stripos($output, 'ssh.exe') !== false;
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
            $ctx = stream_context_create(['http' => ['timeout' => 3]]);
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
