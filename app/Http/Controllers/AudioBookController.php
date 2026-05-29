<?php

namespace App\Http\Controllers;

use App\Jobs\GenerateBookAudio;
use App\Models\AudioBuku;
use App\Models\ListeningProgress;
use App\Services\TunnelService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class AudioBookController extends Controller
{
    protected TunnelService $tunnel;

    public function __construct(TunnelService $tunnel)
    {
        $this->tunnel = $tunnel;
    }
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
        $selectedCategory = $request->query('category');
        $sort = $request->query('sort', 'terbaru');

        $audioBooks = AudioBuku::query()
            ->when($search, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('judul', 'like', "%{$search}%")
                        ->orWhere('penulis', 'like', "%{$search}%")
                        ->orWhere('kategori', 'like', "%{$search}%");
                });
            })
            ->when($selectedCategory, function ($query, $cat) {
                $query->where('kategori', $cat);
            })
            ->when($sort, function ($query, $sort) {
                match ($sort) {
                    'terlama' => $query->oldest(),
                    'judul' => $query->orderBy('judul'),
                    default => $query->latest(),
                };
            })
            ->paginate(5)
            ->withQueryString();

        $categories = AudioBuku::whereNotNull('kategori')
            ->where('kategori', '!=', '')
            ->distinct()
            ->pluck('kategori')
            ->sort()
            ->values();

        return view('audio-books.index', compact('audioBooks', 'search', 'selectedCategory', 'sort', 'categories'));
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
            'audio_status' => 'pending',
            'qr_token' => (string) Str::uuid(),
        ]);

        $this->generateQrFile($audioBook);

        GenerateBookAudio::dispatch($audioBook);

        return redirect()
            ->route('katalog.show', $audioBook->id)
            ->with('success', 'Buku berhasil ditambahkan.');
    }

    public function show($id)
    {
        $book = AudioBuku::findOrFail($id);

        $this->generateQrFile($book);

        if (!session()->has('auth_role')) {
            session(['qr_restricted_token' => $book->qr_token]);
            return redirect()->route('audio-books.play', ['slug' => $book->qr_token]);
        }

        if (request()->has('qr') && request()->query('qr') === $book->qr_token) {
            session(['qr_restricted_token' => $book->qr_token]);
        }

        $qrUrl = $this->buildQrUrl($book);

        $qrFile = 'qr/qr-book-' . $book->id . '.svg';
        $qrFileExists = Storage::disk('public')->exists($qrFile);

        $qrSvg = QrCode::size(300)
            ->margin(2)
            ->errorCorrection('M')
            ->generate($qrUrl);

        return response()
            ->view('katalog.show', compact('book', 'qrUrl', 'qrFile', 'qrFileExists', 'qrSvg'))
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
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

    // ─── Listening Progress ─────────────────────────────
    public function syncProgress(Request $request, AudioBuku $audioBook)
    {
        $validated = $request->validate([
            'sentence_index' => ['required', 'integer', 'min:0'],
            'completed' => ['boolean'],
        ]);

        $userId = session('auth_id');

        if (!$userId) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        ListeningProgress::updateOrCreate(
            [
                'user_id' => $userId,
                'audio_buku_id' => $audioBook->id,
            ],
            [
                'sentence_index' => $validated['sentence_index'],
                'completed' => $validated['completed'] ?? false,
            ]
        );

        return response()->json(['success' => true]);
    }

    public function getProgress(AudioBuku $audioBook)
    {
        $userId = session('auth_id');

        if (!$userId) {
            return response()->json(['sentence_index' => 0, 'completed' => false]);
        }

        $progress = ListeningProgress::where('user_id', $userId)
            ->where('audio_buku_id', $audioBook->id)
            ->first();

        if (!$progress) {
            return response()->json(['sentence_index' => 0, 'completed' => false]);
        }

        return response()->json([
            'sentence_index' => $progress->sentence_index,
            'completed' => $progress->completed,
        ]);
    }

    public function edit(AudioBuku $audioBook)
    {
        if (! $this->canManageBook($audioBook)) {
            return redirect()->route('katalog.show', $audioBook->id)
                ->withErrors(['audio' => 'Hanya admin atau pemilik buku yang dapat mengedit buku.']);
        }

        return view('audio-books.edit', compact('audioBook'));
    }

    public function update(Request $request, AudioBuku $audioBook)
    {
        if (! $this->canManageBook($audioBook)) {
            return redirect()->route('katalog.show', $audioBook->id)
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
            ->route('katalog.show', $audioBook->id)
            ->with('success', 'Buku berhasil diperbarui.');
    }

    public function retryAudio(AudioBuku $audioBook)
    {
        if (! $this->canManageBook($audioBook)) {
            return redirect()->route('katalog.show', $audioBook->id)
                ->withErrors(['audio' => 'Hanya admin yang dapat mengulang generate audio.']);
        }

        // Clean up old audio files
        $audioDir = storage_path("app/public/audio/{$audioBook->id}");
        if (is_dir($audioDir)) {
            array_map('unlink', glob("$audioDir/*.*"));
            rmdir($audioDir);
        }

        $audioBook->update([
            'file_audio' => 'tts',
            'audio_status' => 'pending',
        ]);

        GenerateBookAudio::dispatch($audioBook);

        return redirect()
            ->route('katalog.show', $audioBook->id)
            ->with('success', 'Generate audio diulang. Proses akan berjalan di latar belakang.');
    }

    public function destroy(AudioBuku $audioBook)
    {
        if (! $this->canManageBook($audioBook)) {
            return redirect()->route('katalog.show', $audioBook->id)
                ->withErrors(['audio' => 'Hanya admin atau pemilik buku yang dapat menghapus buku.']);
        }

        if ($audioBook->cover) {
            Storage::disk('public')->delete($audioBook->cover);
        }

        if ($audioBook->file_buku) {
            Storage::disk('public')->delete($audioBook->file_buku);
        }

        if ($audioBook->file_audio) {
            Storage::disk('public')->delete($audioBook->file_audio);
        }

        $audioDir = 'audio/' . $audioBook->id;
        if (Storage::disk('public')->exists($audioDir)) {
            Storage::disk('public')->deleteDirectory($audioDir);
        }

        $qrFile = 'qr/qr-book-' . $audioBook->id . '.svg';
        if (Storage::disk('public')->exists($qrFile)) {
            Storage::disk('public')->delete($qrFile);
        }

        $audioBook->delete();

        return redirect()
            ->route('audio-books.index')
            ->with('success', 'Buku berhasil dihapus.');
    }

    public function streamAudio(AudioBuku $audioBook)
    {
        if ($audioBook->audio_status !== 'completed' || !$audioBook->file_audio || $audioBook->file_audio === 'tts') {
            abort(404);
        }

        $path = storage_path("app/public/{$audioBook->file_audio}");

        if (!file_exists($path)) {
            abort(404);
        }

        return response()->file($path, [
            'Content-Type' => 'audio/mpeg',
            'Content-Disposition' => 'inline',
        ]);
    }

    private function generateQrFile(AudioBuku $audioBook): void
    {
        $qrUrl = $this->buildQrUrl($audioBook);

        $svg = QrCode::size(300)
            ->margin(2)
            ->errorCorrection('M')
            ->generate($qrUrl);

        $qrDir = storage_path('app/public/qr');
        if (! is_dir($qrDir)) {
            mkdir($qrDir, 0755, true);
        }

        file_put_contents($qrDir . '/qr-book-' . $audioBook->id . '.svg', $svg);
    }

    public static function isLocalUrl(string $url): bool
    {
        $host = parse_url($url, PHP_URL_HOST) ?: '';
        if (in_array($host, ['localhost', '127.0.0.1', '::1'], true)) {
            return true;
        }
        if (preg_match('/^(10\.|172\.(1[6-9]|2\d|3[01])\.|192\.168\.)/', $host)) {
            return true;
        }
        return false;
    }

    public function getPublicUrl(): string
    {
        $tunnelUrl = $this->tunnel->getStoredUrl();
        if ($tunnelUrl) {
            return rtrim($tunnelUrl, '/');
        }

        $appUrl = rtrim(config('app.url'), '/');
        if (! self::isLocalUrl($appUrl)) {
            return $appUrl;
        }

        $detectedIp = self::getDetectedIp();
        $port = parse_url($appUrl, PHP_URL_PORT) ?: '8000';
        return "http://{$detectedIp}:{$port}";
    }

    private function buildQrUrl(AudioBuku $audioBook): string
    {
        $baseUrl = $this->getPublicUrl();
        return "{$baseUrl}/scan/book/{$audioBook->qr_token}";
    }

    public function play(string $slug)
    {
        $audioBook = AudioBuku::where('qr_token', $slug)->firstOrFail();

        // Mark the current session as restricted to this QR token for guest users
        session(['qr_restricted_token' => $slug]);

        return view('audio-books.play', compact('audioBook'));
    }

    public function scan(string $qrToken)
    {
        $audioBook = AudioBuku::where('qr_token', $qrToken)->first();

        if (! $audioBook && is_numeric($qrToken)) {
            $audioBook = AudioBuku::find((int) $qrToken);
        }

        if (! $audioBook) {
            return response()
                ->view('errors.scan-invalid', [], 404)
                ->header('Cache-Control', 'no-cache, no-store, must-revalidate');
        }

        session(['qr_restricted_token' => $audioBook->qr_token]);

        return redirect()->route('katalog.show', ['id' => $audioBook->id, 'qr' => $audioBook->qr_token]);
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
