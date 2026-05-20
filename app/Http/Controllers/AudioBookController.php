<?php

namespace App\Http\Controllers;

use App\Models\AudioBuku;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AudioBookController extends Controller
{
    public function landing()
    {
        $bookCount = AudioBuku::count();
        
        $totalChars = AudioBuku::sum(\DB::raw('LENGTH(deskripsi)'));
        if ($totalChars >= 1000000) {
            $charCount = round($totalChars / 1000000, 1) . 'M+';
        } elseif ($totalChars >= 1000) {
            $charCount = round($totalChars / 1000, 1) . 'K+';
        } else {
            $charCount = $totalChars;
        }

        // Optimasi: Gunakan estimasi panjang karakter dibagi 6 untuk menghindari pemuatan seluruh teks buku ke memori PHP yang dapat membuat server crash/hang.
        $totalWords = (int) ($totalChars / 6);
        $readDuration = ceil($totalWords / 150) . ' Mins';

        return view('home', compact('bookCount', 'charCount', 'readDuration'));
    }

    public function index(Request $request)
    {
        $search = $request->query('search');

        $audioBooks = AudioBuku::query()
            ->when($search, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('judul', 'like', "%{$search}%")
                        ->orWhere('penulis', 'like', "%{$search}%")
                        ->orWhere('kategori', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(5)
            ->withQueryString();

        return view('audio-books.index', compact('audioBooks', 'search'));
    }

    public function create()
    {
        if (! in_array(session('auth_role'), ['admin', 'user'], true)) {
            return redirect()->route('login')->withErrors(['email' => 'Silakan login terlebih dahulu.']);
        }

        return view('audio-books.create');
    }

    public function store(Request $request)
    {
        if (! in_array(session('auth_role'), ['admin', 'user'], true)) {
            return redirect()->route('login')->withErrors(['email' => 'Silakan login terlebih dahulu.']);
        }

        $validated = $request->validate([
            'book_file' => ['required', 'file', 'max:51200'],
            'title' => ['nullable', 'string', 'max:255'],
        ]);

        $bookFile = $request->file('book_file');
        $extension = strtolower($bookFile->getClientOriginalExtension());

        if (! in_array($extension, ['pdf', 'epub'], true)) {
            return back()
                ->withErrors(['book_file' => 'File buku harus berformat PDF atau EPUB.'])
                ->withInput();
        }

        $bookPath = $bookFile->store('file-buku', 'public');
        $fullBookPath = Storage::disk('public')->path($bookPath);
        $bookText = $this->extractBookText($fullBookPath, $extension);

        if (trim($bookText) === '') {
            Storage::disk('public')->delete($bookPath);

            return back()
                ->withErrors(['book_file' => 'Isi file buku belum bisa dibaca. Coba gunakan PDF/EPUB yang teksnya bisa diseleksi, bukan hasil scan gambar.'])
                ->withInput();
        }

        $description = $this->makeDescription($bookText);
        $title = ($validated['title'] ?? null) ?: pathinfo($bookFile->getClientOriginalName(), PATHINFO_FILENAME);

        $audioBook = AudioBuku::create([
            'user_id' => session('auth_role') === 'user' ? session('auth_id') : null,
            'admin_id' => session('auth_role') === 'admin' ? session('auth_id') : null,
            'judul' => $title,
            'cover' => null,
            'penulis' => 'Penulis Tidak Diketahui',
            'kategori' => 'Umum',
            'deskripsi' => $description,
            'file_buku' => $bookPath,
            'file_audio' => 'tts',
            'qr_token' => Str::uuid(),
        ]);

        // Redirect to show route to display the generated QR code
        return redirect()
            ->route('audio-books.show', $audioBook)
            ->with('success', 'Buku berhasil ditambahkan.');
    }

    public function show(AudioBuku $audioBook)
    {
        $localIps = self::getLocalIps();
        $detectedIp = self::getDetectedIp();
        $tunnelUrl = self::getTunnelUrl();

        $path = route('audio-books.play', $audioBook->qr_token, false);
        if ($tunnelUrl) {
            $qrUrl = rtrim($tunnelUrl, '/') . $path;
        } else {
            $scheme = request()->getScheme() ?: 'http';
            $port = request()->getPort();
            $host = $detectedIp && $detectedIp !== '127.0.0.1' ? $detectedIp : request()->getHost();
            $portPart = in_array($port, [80, 443], true) ? '' : ':' . $port;
            $qrUrl = "{$scheme}://{$host}{$portPart}{$path}";
        }

        return view('audio-books.show', compact('audioBook', 'qrUrl', 'detectedIp', 'localIps', 'tunnelUrl'));
    }

    public static function getLocalIps(): array
    {
        $ips = [];
        
        // 1. Parse Windows ipconfig command output to find all physical IPv4s
        try {
            $output = shell_exec('ipconfig');
            if ($output) {
                // Normalize newlines
                $output = str_replace("\r\n", "\n", $output);
                // Split by double newline to get each adapter block
                $blocks = explode("\n\n", $output);
                foreach ($blocks as $block) {
                    $lines = explode("\n", trim($block));
                    if (empty($lines)) continue;
                    
                    $adapterName = trim($lines[0], " \t\n\r\0\x0B:");
                    if (empty($adapterName) || str_contains(strtolower($adapterName), 'windows ip configuration')) {
                        continue;
                    }

                    // Abaikan adapter virtual agar HP tidak salah membaca IP VirtualBox/VMware/WSL
                    $lowerName = strtolower($adapterName);
                    if (str_contains($lowerName, 'virtualbox') || 
                        str_contains($lowerName, 'vmware') || 
                        str_contains($lowerName, 'wsl') || 
                        str_contains($lowerName, 'vethernet') || 
                        str_contains($lowerName, 'host-only') ||
                        str_contains($lowerName, 'loopback') ||
                        str_contains($lowerName, 'hyper-v')) {
                        continue;
                    }
                    
                    foreach ($lines as $line) {
                        if (preg_match('/IPv4 Address[\.\s]+:\s+([0-9\.]+)/i', $line, $match)) {
                            $ip = trim($match[1]);
                            if ($ip !== '127.0.0.1' && !in_array($ip, $ips, true)) {
                                $ips[$adapterName] = $ip;
                            }
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            // Fallback
        }
        
        // 2. DNS Hostname fallback (hanya jika IP fisik tidak ditemukan)
        if (empty($ips)) {
            $hostIp = gethostbyname(gethostname());
            if ($hostIp && $hostIp !== '127.0.0.1' && $hostIp !== '::1') {
                $ips['Host DNS'] = $hostIp;
            }
        }
        
        if (empty($ips)) {
            $ips['Localhost'] = '127.0.0.1';
        }
        
        return $ips;
    }

    public static function getDetectedIp(): string
    {
        $localIps = self::getLocalIps();
        
        $detectedIp = '127.0.0.1';
        
        // 1. Try to prioritize Wi-Fi or Wireless adapter
        foreach ($localIps as $name => $ip) {
            if (stripos($name, 'wi-fi') !== false || stripos($name, 'wireless') !== false) {
                $detectedIp = $ip;
                break;
            }
        }
        
        // 2. Fallback to standard local ranges if Wi-Fi wasn't explicitly matched
        if ($detectedIp === '127.0.0.1') {
            foreach ($localIps as $name => $ip) {
                if (str_starts_with($ip, '192.168.') || str_starts_with($ip, '10.')) {
                    $detectedIp = $ip;
                    break;
                }
            }
        }
        
        // 3. Fallback to the first detected IP
        if ($detectedIp === '127.0.0.1' && !empty($localIps)) {
            $detectedIp = reset($localIps);
        }

        return $detectedIp;
    }

    public static function getTunnelUrl(): ?string
    {
        $tempPath = storage_path('app/localtunnel_temp.txt');
        $errPath = storage_path('app/localtunnel_err.txt');
        
        $port = request()->getPort() ?: 8000;

        // Check if ssh.exe is running in Windows tasklist
        $isRunning = false;
        try {
            $tasklist = shell_exec('tasklist /NH /FI "IMAGENAME eq ssh.exe"');
            if ($tasklist && stripos($tasklist, 'ssh.exe') !== false) {
                $isRunning = true;
            }
        } catch (\Exception $e) {
            // Fallback
        }

        $hasUrl = false;
        if (file_exists($tempPath)) {
            $content = file_get_contents($tempPath);
            if (preg_match('/https:\/\/[a-zA-Z0-9\-\.]+(\.lhr\.life)/', $content, $match)) {
                $hasUrl = true;
            }
        }

        // If not running or we don't have a valid tunnel URL file, kill existing ssh.exe and start a new one
        if (!$isRunning || !$hasUrl) {
            if ($isRunning) {
                try {
                    shell_exec('taskkill /F /IM ssh.exe');
                } catch (\Exception $e) {}
            }
            
            if (file_exists($tempPath)) {
                @unlink($tempPath);
            }
            if (file_exists($errPath)) {
                @unlink($errPath);
            }

            try {
                $command = 'powershell -Command "Start-Process -NoNewWindow -FilePath \'ssh\' -ArgumentList \'-o\', \'StrictHostKeyChecking=no\', \'-R\', \'80:127.0.0.1:' . $port . '\', \'nokey@localhost.run\' -RedirectStandardOutput \'' . $tempPath . '\' -RedirectStandardError \'' . $errPath . '\'"';
                pclose(popen($command, 'r'));
                
                // Wait briefly for the tunnel to initialize and write the URL
                for ($i = 0; $i < 10; $i++) {
                    usleep(200000); // 0.2 seconds
                    if (file_exists($tempPath)) {
                        $content = file_get_contents($tempPath);
                        if (preg_match('/https:\/\/[a-zA-Z0-9\-\.]+(\.lhr\.life)/', $content, $match)) {
                            break;
                        }
                    }
                }
            } catch (\Exception $e) {
                // Failed to start
            }
        }

        // Read active tunnel URL
        if (file_exists($tempPath)) {
            $content = file_get_contents($tempPath);
            if (preg_match('/https:\/\/[a-zA-Z0-9\-\.]+(\.lhr\.life)/', $content, $match)) {
                return $match[0];
            }
        }

        return null;
    }

    public function edit(AudioBuku $audioBook)
    {
        if (! $this->canManageBook($audioBook)) {
            return redirect()->route('audio-books.show', $audioBook)
                ->withErrors(['audio' => 'Hanya admin atau pemilik buku yang dapat mengedit buku.']);
        }

        return view('audio-books.edit', compact('audioBook'));
    }

    public function update(Request $request, AudioBuku $audioBook)
    {
        if (! $this->canManageBook($audioBook)) {
            return redirect()->route('audio-books.show', $audioBook)
                ->withErrors(['audio' => 'Hanya admin atau pemilik buku yang dapat memperbarui buku.']);
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $updateData = [
            'judul' => $validated['title'],
            'deskripsi' => $validated['description'] ?? null,
        ];

        $audioBook->update($updateData);

        return redirect()
            ->route('audio-books.show', $audioBook)
            ->with('success', 'Buku berhasil diperbarui.');
    }

    public function destroy(AudioBuku $audioBook)
    {
        if (! $this->canManageBook($audioBook)) {
            return redirect()->route('audio-books.show', $audioBook)
                ->withErrors(['audio' => 'Hanya admin atau pemilik buku yang dapat menghapus buku.']);
        }

        if ($audioBook->cover) {
            Storage::disk('public')->delete($audioBook->cover);
        }

        if ($audioBook->file_buku) {
            Storage::disk('public')->delete($audioBook->file_buku);
        }

        if ($audioBook->file_audio && $audioBook->file_audio !== 'tts') {
            Storage::disk('public')->delete($audioBook->file_audio);
        }

        $audioBook->delete();

        return redirect()
            ->route('audio-books.index')
            ->with('success', 'Buku berhasil dihapus.');
    }

    public function play(string $token)
    {
        $audioBook = AudioBuku::where('qr_token', $token)->firstOrFail();

        // Mark the current session as restricted to this QR token for guest users
        session(['qr_restricted_token' => $token]);

        return view('audio-books.play', compact('audioBook'));
    }

    private function canManageBook(AudioBuku $audioBook): bool
    {
        return session('auth_role') === 'admin';
    }

    private function extractBookText(string $path, string $extension): string
    {
        return match ($extension) {
            'pdf' => $this->extractPdfText($path),
            'epub' => $this->extractEpubText($path),
            default => '',
        };
    }

    private function extractPdfText(string $path): string
    {
        $tempDirectory = storage_path('app/temp');

        if (! is_dir($tempDirectory)) {
            mkdir($tempDirectory, 0777, true);
        }

        $outputPath = $tempDirectory . DIRECTORY_SEPARATOR . Str::uuid() . '.txt';
        $command = 'pdftotext -enc UTF-8 -layout ' . escapeshellarg($path) . ' ' . escapeshellarg($outputPath);
        exec($command, $output, $exitCode);

        if ($exitCode !== 0 || ! file_exists($outputPath)) {
            return '';
        }

        $text = file_get_contents($outputPath) ?: '';
        @unlink($outputPath);

        return $this->cleanExtractedText($text);
    }

    private function extractEpubText(string $path): string
    {
        if (! class_exists(\ZipArchive::class)) {
            return '';
        }

        $zip = new \ZipArchive();

        if ($zip->open($path) !== true) {
            return '';
        }

        $text = '';

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            $lowerName = strtolower($name);

            if (! str_ends_with($lowerName, '.html') && ! str_ends_with($lowerName, '.xhtml')) {
                continue;
            }

            $content = $zip->getFromIndex($i);

            if ($content === false) {
                continue;
            }

            $text .= ' ' . strip_tags($content);
        }

        $zip->close();

        return $this->cleanExtractedText($text);
    }

    private function cleanExtractedText(string $text): string
    {
        // Ensure string is valid UTF-8
        if (! mb_check_encoding($text, 'UTF-8')) {
            $text = mb_convert_encoding($text, 'UTF-8', 'ISO-8859-1');
        }

        // Clean any residual invalid UTF-8 bytes to prevent DB errors
        $text = iconv('UTF-8', 'UTF-8//IGNORE', $text) ?: $text;

        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/[ \t]+/', ' ', $text) ?? $text;
        $text = preg_replace('/\R{3,}/', "\n\n", $text) ?? $text;

        return trim($text);
    }

    private function makeDescription(string $text): string
    {
        return $this->cleanExtractedText($text);
    }
}
