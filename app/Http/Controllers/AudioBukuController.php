<?php

namespace App\Http\Controllers;

use App\Exceptions\BookTextExtractionException;
use App\Jobs\GenerateBookAudio;
use App\Models\AudioBuku;
use App\Models\ListeningProgress;
use App\Services\TunnelService;
use App\Support\Seo\Faq;
use App\Support\Seo\SeoBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Smalot\PdfParser\Parser;

class AudioBukuController extends Controller
{
    /**
     * Batas ukuran PDF untuk ekstraksi teks otomatis.
     * Parser PHP murni menahan seluruh dokumen di memori sehingga PDF besar
     * dapat menghabiskan memory_limit pada shared hosting.
     */
    private const MAX_PDF_EXTRACTION_MEGABYTES = 25;

    public function landing()
    {
        try {
            [$bookCount, $totalChars] = Cache::remember('landing_chars_v1', 3600, function () {
                $bookCount = AudioBuku::count();
                $totalChars = AudioBuku::sum(\DB::raw('LENGTH(deskripsi)'));

                return [$bookCount, $totalChars];
            });
        } catch (\Exception $e) {
            Cache::forget('landing_chars_v1');
            $bookCount = 0;
            $totalChars = 0;
            \Log::warning('Database connection failed on landing page: '.$e->getMessage());
        }

        if ($totalChars >= 1000000) {
            $charCount = round($totalChars / 1000000, 1).'M+';
        } elseif ($totalChars >= 1000) {
            $charCount = round($totalChars / 1000, 1).'K+';
        } else {
            $charCount = $totalChars;
        }

        $totalWords = (int) ($totalChars / 6);
        $readDuration = ceil($totalWords / 150).' Mins';

        // FAQ didefinisikan sekali lalu dipakai untuk markup body DAN JSON-LD,
        // supaya keduanya tidak mungkin berbeda isi.
        $faq = Faq::landing();

        // Gambar hero dilayani Pexels yang sudah bisa mengubah format, jadi
        // WebP bisa dipilih lewat <picture> dan ukuran dikunci lewat srcset.
        // Tanpa ini, ponsel mengunduh berkas 2000px yang tidak pernah dipakai.
        $hero = fn (int $width, int $height, string $format): string => sprintf(
            'https://images.pexels.com/photos/6606144/pexels-photo-6606144.jpeg?auto=compress&cs=tinysrgb&fit=crop&w=%d&h=%d&fm=%s',
            $width,
            $height,
            $format,
        );

        return view('home', [
            'bookCount' => $bookCount,
            'charCount' => $charCount,
            'readDuration' => $readDuration,
            'faq' => $faq,
            'hero480' => $hero(480, 360, 'webp'),
            'hero720' => $hero(720, 540, 'webp'),
            'hero1200' => $hero(1200, 900, 'webp'),
            'hero1800' => $hero(1800, 1350, 'webp'),
            'heroJpeg' => $hero(1200, 900, 'jpg'),
            'seo' => SeoBuilder::landing(route('home'), $faq),
        ]);
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
            ->paginate(6)
            ->withQueryString();

        $categories = AudioBuku::whereNotNull('kategori')
            ->where('kategori', '!=', '')
            ->distinct()
            ->pluck('kategori')
            ->sort()
            ->values();

        $isFiltered = $search !== null || $selectedCategory !== null || $sort !== 'terbaru';

        return view('audio-books.index', compact('audioBooks', 'search', 'selectedCategory', 'sort', 'categories') + [
            'withMiniPlayer' => true,
            'seo' => $isFiltered
                ? SeoBuilder::filteredCatalog(url()->current())
                : SeoBuilder::catalog(route('audio-books.index')),
        ]);
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
            'book_file' => ['required', 'file', 'mimes:pdf,epub', 'max:51200'],
            'title' => ['nullable', 'string', 'max:255'],
        ], [
            'book_file.mimes' => 'File buku harus berformat PDF atau EPUB.',
            'book_file.max' => 'Ukuran file buku maksimal 50 MB.',
        ]);

        $bookFile = $request->file('book_file');
        $extension = strtolower($bookFile->getClientOriginalExtension() ?: $bookFile->guessExtension());

        if (! in_array($extension, ['pdf', 'epub'], true)) {
            return back()
                ->withErrors(['book_file' => 'File buku harus berformat PDF atau EPUB.'])
                ->withInput();
        }

        $bookPath = $bookFile->store('file-buku', 'public');
        $fullBookPath = Storage::disk('public')->path($bookPath);

        try {
            $bookText = $this->extractBookText($fullBookPath, $extension);
        } catch (BookTextExtractionException $e) {
            Storage::disk('public')->delete($bookPath);

            return back()
                ->withErrors(['book_file' => $e->getMessage()])
                ->withInput();
        }

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
            'audio_progress' => 0,
            'audio_message' => 'Menunggu antrian...',
            'qr_token' => (string) Str::uuid(),
        ]);

        // Generate QR code and trigger audio generation automatically
        $this->generateQrFile($audioBook);
        GenerateBookAudio::dispatch($audioBook);
        Cache::forget('landing_chars_v1');

        return redirect()
            ->route('katalog.show', $audioBook->id)
            ->with('success', 'Buku berhasil ditambahkan. Audio sedang diproses di latar belakang dan akan tersedia dalam beberapa menit.');
    }

    public function show($id)
    {
        $book = AudioBuku::findOrFail($id);

        if (request()->has('qr')) {
            session(['qr_restricted_token' => request()->query('qr')]);
        }

        $qrUrl = $this->buildQrUrl($book);

        return response()
            ->view('katalog.show', compact('book', 'qrUrl') + ['seo' => SeoBuilder::book($book)])
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    public static function getLocalIps(): array
    {
        $ips = [];

        try {
            $hostname = gethostname();
            if ($hostname) {
                $hostIp = gethostbyname($hostname);
                if ($hostIp !== $hostname && filter_var($hostIp, FILTER_VALIDATE_IP)) {
                    $ips['Host DNS'] = $hostIp;
                }
            }
        } catch (\Throwable $e) {
            // Hostname resolution failed — continue to socket fallback.
        }

        if (empty($ips)) {
            try {
                $socket = @stream_socket_client('tcp://8.8.8.8:80', $errno, $errstr, 2);
                if ($socket) {
                    $name = stream_socket_get_name($socket, false);
                    if ($name) {
                        $ip = trim(explode(':', $name)[0]);
                        if ($ip && $ip !== '127.0.0.1' && $ip !== '::1') {
                            $ips['Default Route'] = $ip;
                        }
                    }
                    fclose($socket);
                }
            } catch (\Throwable $e) {
                // Socket probe failed — fall through to the server address fallback.
            }
        }

        if (empty($ips)) {
            // Shared hosting sering memblokir probe socket keluar, jadi andalkan
            // alamat yang sudah diikat cPanel ke virtual host.
            $serverAddr = $_SERVER['SERVER_ADDR'] ?? null;
            if (is_string($serverAddr) && filter_var($serverAddr, FILTER_VALIDATE_IP)) {
                $ips['Server Addr'] = $serverAddr;
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

        foreach ($localIps as $name => $ip) {
            if (stripos($name, 'wi-fi') !== false || stripos($name, 'wireless') !== false) {
                $detectedIp = $ip;
                break;
            }
        }

        if ($detectedIp === '127.0.0.1') {
            foreach ($localIps as $name => $ip) {
                if (str_starts_with($ip, '192.168.') || str_starts_with($ip, '10.')) {
                    $detectedIp = $ip;
                    break;
                }
            }
        }

        if ($detectedIp === '127.0.0.1' && ! empty($localIps)) {
            $detectedIp = reset($localIps);
        }

        return $detectedIp;
    }

    public function syncProgress(Request $request, AudioBuku $audioBook)
    {
        $validated = $request->validate([
            'sentence_index' => ['required', 'integer', 'min:0'],
            'completed' => ['boolean'],
        ]);

        $role = session('auth_role');
        $userId = session('auth_id');

        if (! $userId || $role !== 'user') {
            return response()->json(['success' => true, 'persisted' => false]);
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

        return response()->json(['success' => true, 'persisted' => true]);
    }

    public function getProgress(AudioBuku $audioBook)
    {
        $role = session('auth_role');
        $userId = session('auth_id');

        if (! $userId || $role !== 'user') {
            return response()->json(['sentence_index' => 0, 'completed' => false]);
        }

        $progress = ListeningProgress::where('user_id', $userId)
            ->where('audio_buku_id', $audioBook->id)
            ->first();

        if (! $progress) {
            return response()->json(['sentence_index' => 0, 'completed' => false]);
        }

        return response()->json([
            'sentence_index' => $progress->sentence_index,
            'completed' => $progress->completed,
        ]);
    }

    public function audioProgress(AudioBuku $audioBook)
    {
        return response()->json([
            'audio_status' => $audioBook->audio_status,
            'audio_progress' => (int) $audioBook->audio_progress,
            'audio_message' => $audioBook->audio_message,
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
        Cache::forget('landing_chars_v1');

        return redirect()
            ->route('katalog.show', $audioBook->id)
            ->with('success', 'Buku berhasil diperbarui.');
    }

    public function retryAudio(AudioBuku $audioBook)
    {
        if (! $this->canManageBook($audioBook)) {
            return redirect()->route('katalog.show', $audioBook->id)
                ->withErrors(['audio' => 'Hanya admin atau pemilik buku yang dapat mengulang generate audio.']);
        }

        $audioDir = "audio/{$audioBook->id}";
        if (Storage::disk('public')->exists($audioDir)) {
            Storage::disk('public')->deleteDirectory($audioDir);
        }

        $audioBook->update([
            'file_audio' => 'tts',
            'audio_status' => 'pending',
            'audio_progress' => 0,
            'audio_message' => 'Menunggu antrian...',
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

        $audioDir = 'audio/'.$audioBook->id;
        if (Storage::disk('public')->exists($audioDir)) {
            Storage::disk('public')->deleteDirectory($audioDir);
        }

        $qrFile = 'qr/qr-book-'.$audioBook->id.'.svg';
        if (Storage::disk('public')->exists($qrFile)) {
            Storage::disk('public')->delete($qrFile);
        }

        $audioBook->delete();
        Cache::forget('landing_chars_v1');

        return redirect()
            ->route('audio-books.index')
            ->with('success', 'Buku berhasil dihapus.');
    }

    public function streamAudio(AudioBuku $audioBook)
    {
        if (! in_array($audioBook->audio_status, ['completed', 'partial']) || ! $audioBook->file_audio || $audioBook->file_audio === 'tts') {
            abort(404);
        }

        $path = storage_path("app/public/{$audioBook->file_audio}");

        if (! file_exists($path)) {
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

        file_put_contents($qrDir.'/qr-book-'.$audioBook->id.'.svg', $svg);
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

    public static function buildQrUrl(AudioBuku $audioBook): string
    {
        // Production: QR selalu memakai URL publik (APP_URL / host request).
        // IP LAN dan tunnel tidak relevan — pengunjung mengakses dari domain publik mana pun.
        if (app()->environment('production')) {
            $baseUrl = rtrim(self::resolvePublicUrl(), '/');

            return self::finalizeQrUrl($baseUrl, $audioBook);
        }

        $baseUrl = null;

        // Local / development:
        // Priority 1: Tunnel SSH live → akses dari jaringan mana pun (data seluler, luar).
        try {
            $tunnelService = app(TunnelService::class);
            $tunnelUrl = $tunnelService->getStoredUrl();
            if ($tunnelUrl) {
                $baseUrl = rtrim($tunnelUrl, '/');
            }
        } catch (\Exception $e) {
            // Fallback if TunnelService is not available
        }

        // Priority 2: Request datang lewat host publik (IP LAN / domain) → QR harus
        // memakai host tersebut agar perangkat di jaringan yang sama bisa menjangkaunya.
        if (! $baseUrl && request()) {
            $host = request()->getHost();
            $isLoopback = in_array($host, ['localhost', '127.0.0.1', '::1'], true);

            if (! $isLoopback && ! self::isLocalUrl($host)) {
                // Host publik asli (bukan IP LAN) — mis. VPN, domain staging.
                $baseUrl = request()->getSchemeAndHttpHost();
            } elseif ($isLoopback) {
                // Akses via localhost → deteksi IP LAN Wi-Fi supaya HP di jaringan sama bisa connect.
                $detectedIp = self::getDetectedIp();
                if ($detectedIp && $detectedIp !== '127.0.0.1') {
                    $port = request()->getPort();
                    $portStr = ($port && $port != 80 && $port != 443) ? ":{$port}" : '';
                    $baseUrl = "http://{$detectedIp}{$portStr}";
                }
            } elseif (self::isLocalUrl($host)) {
                // Host adalah IP LAN → pakai langsung (perangkat lain di jaringan yang sama).
                $baseUrl = request()->getSchemeAndHttpHost();
            }
        }

        // Priority 3: APP_URL publik yang stabil (bukan tunnel basi / localhost).
        if (! $baseUrl) {
            $configUrl = rtrim((string) config('app.url'), '/');
            if ($configUrl && ! self::isLocalUrl($configUrl)) {
                if (! preg_match('/\.(?:lhr\.life|localhost\.run)/', $configUrl)) {
                    $baseUrl = $configUrl;
                }
            }
        }

        // Ultimate fallback: IP LAN, atau APP_URL jika eksplisit dipilih admin.
        if (! $baseUrl) {
            $detectedIp = self::getDetectedIp();
            $baseUrl = ($detectedIp && $detectedIp !== '127.0.0.1')
                ? "http://{$detectedIp}"
                : rtrim(config('app.url'), '/');
        }

        return self::finalizeQrUrl($baseUrl, $audioBook);
    }

    protected static function resolvePublicUrl(): string
    {
        // Ikuti host request jika merupakan domain publik (bukan localhost / IP LAN).
        // Ini memungkinkan alias domain, VPN, atau nama host staging dipakai otomatis.
        if (request()) {
            $host = request()->getHost();
            if ($host && ! in_array($host, ['localhost', '127.0.0.1', '::1'], true) && ! self::isLocalUrl($host)) {
                return request()->getSchemeAndHttpHost();
            }
        }

        // Fallback: APP_URL publik yang dikonfigurasi admin.
        return rtrim((string) config('app.url'), '/');
    }

    protected static function finalizeQrUrl(string $baseUrl, AudioBuku $audioBook): string
    {
        if (! $baseUrl) {
            $baseUrl = rtrim((string) config('app.url'), '/');
        }

        // Force HTTPS untuk URL publik/tunnel agar Web Speech API aman.
        // IP LAN & localhost tidak dipaksa HTTPS (tanpa sertifikat valid).
        if ($baseUrl && ! self::isLocalUrl($baseUrl)) {
            $baseUrl = preg_replace('/^http:/i', 'https:', $baseUrl);
        }

        return "{$baseUrl}/scan/book/{$audioBook->qr_token}";
    }

    public function play(string $slug)
    {
        $audioBook = AudioBuku::where('qr_token', $slug)->firstOrFail();

        session(['qr_restricted_token' => $slug]);

        return view('audio-books.play', compact('audioBook') + ['seo' => SeoBuilder::book($audioBook)]);
    }

    public function sitemap()
    {
        $now = now()->toAtomString();

        $rows = collect([route('home'), route('audio-books.index')])
            ->map(fn (string $loc): array => ['loc' => $loc, 'lastmod' => $now])
            ->merge(
                AudioBuku::query()
                    ->orderBy('id')
                    ->get(['id', 'updated_at'])
                    ->map(fn (AudioBuku $book): array => [
                        'loc' => route('katalog.show', $book->id),
                        'lastmod' => $book->updated_at ? $book->updated_at->toAtomString() : $now,
                    ])
            );

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n"
            .'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";

        foreach ($rows as $row) {
            $xml .= sprintf(
                "  <url>\n    <loc>%s</loc>\n    <lastmod>%s</lastmod>\n  </url>\n",
                htmlspecialchars($row['loc'], ENT_XML1 | ENT_QUOTES, 'UTF-8'),
                $row['lastmod'],
            );
        }

        $xml .= '</urlset>'."\n";

        return response($xml)
            ->header('Content-Type', 'application/xml; charset=UTF-8')
            ->header('Cache-Control', 'public, max-age=3600');
    }

    public function scan(string $qrToken)
    {
        $book = AudioBuku::where('qr_token', $qrToken)->first();

        if (! $book && is_numeric($qrToken)) {
            $book = AudioBuku::find((int) $qrToken);
        }

        if (! $book) {
            return response()
                ->view('errors.scan-invalid', [], 404)
                ->header('Cache-Control', 'no-cache, no-store, must-revalidate');
        }

        session(['qr_restricted_token' => $book->qr_token]);

        $qrUrl = $this->buildQrUrl($book);

        return response()
            ->view('katalog.show', compact('book', 'qrUrl') + [
                'seo' => array_replace(
                    SeoBuilder::book($book),
                    ['robots' => 'noindex, nofollow']
                ),
            ])
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    private function canManageBook(AudioBuku $audioBook): bool
    {
        $role = session('auth_role');
        $userId = session('auth_id');

        if ($role === 'admin') {
            return true;
        }

        if ($role === 'user' && ! empty($userId)) {
            return (int) $audioBook->user_id === (int) $userId;
        }

        return false;
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
        $this->guardPdfExtractionSize($path);

        try {
            $parser = new Parser;
            $pdf = $parser->parseFile($path);
            $text = $pdf->getText();

            return $this->cleanExtractedText($text);
        } catch (\Throwable $e) {
            \Log::error('PDF text extraction failed: '.$e->getMessage(), [
                'path' => $path,
            ]);

            return '';
        }
    }

    /**
     * Tolak PDF yang terlalu besar sebelum masuk parser, karena parser PHP murni
     * menahan seluruh objek dokumen di memori dan dapat menguras memory_limit.
     *
     * @throws BookTextExtractionException
     */
    private function guardPdfExtractionSize(string $path): void
    {
        if (! is_file($path)) {
            return;
        }

        $sizeMb = round(filesize($path) / 1048576, 1);

        if ($sizeMb <= self::MAX_PDF_EXTRACTION_MEGABYTES) {
            return;
        }

        \Log::error('PDF rejected: exceeds extraction size limit.', [
            'path' => $path,
            'size_mb' => $sizeMb,
            'limit_mb' => self::MAX_PDF_EXTRACTION_MEGABYTES,
        ]);

        throw new BookTextExtractionException(
            "Ukuran PDF {$sizeMb} MB melebihi batas ".self::MAX_PDF_EXTRACTION_MEGABYTES
            .' MB untuk ekstraksi teks otomatis. Kompres atau pecah file menjadi bagian yang lebih kecil.'
        );
    }

    private function extractEpubText(string $path): string
    {
        if (! class_exists(\ZipArchive::class)) {
            return '';
        }

        $zip = new \ZipArchive;

        if ($zip->open($path) !== true) {
            return '';
        }

        $text = '';
        $maxChars = 5000000;

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

            $text .= ' '.strip_tags($content);

            if (strlen($text) > $maxChars) {
                break;
            }
        }

        $zip->close();

        return $this->cleanExtractedText($text);
    }

    private function cleanExtractedText(string $text): string
    {
        if (! mb_check_encoding($text, 'UTF-8')) {
            $text = mb_convert_encoding($text, 'UTF-8', 'ISO-8859-1');
        }

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
