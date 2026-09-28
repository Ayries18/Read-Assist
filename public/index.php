<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

/*
|--------------------------------------------------------------------------
| Document Root Resolution
|--------------------------------------------------------------------------
|
| File ini harus bekerja pada dua layout sekaligus, karena folder aplikasi
| dan document root tidak selalu berada di lokasi yang sama:
|
|   1. Pengembangan lokal  : document root = Read-Assist/public
|                            sehingga folder aplikasi ada di __DIR__/..
|
|   2. Hosting cPanel      : document root = ~/public_html
|                            sedangkan folder aplikasi adalah TETANGGA
|                            document root, yaitu ~/Read-Assist,
|                            jadi jalurnya dirname(__DIR__).'/Read-Assist'
|
| Perhatikan bedanya. Di layout cPanel folder aplikasi bukan anak dari
| document root, melainkan saudara sejajar, sehingga memakai __DIR__ akan
| mengarah ke public_html/Read-Assist yang isinya kosong.
|
| Karena itu repo harus memuat SATU file yang benar untuk keduanya, supaya
| scripts/deploy-public.sh bisa menyalin apa adanya tanpa divergensi.
|
*/

$candidates = [
    __DIR__.'/..',                        // lokal: public -> Read-Assist
    dirname(__DIR__).'/Read-Assist',      // cPanel: public_html -> Read-Assist
    dirname(__DIR__),                     // cadangan: docroot = folder aplikasi
];

$appBase = null;

foreach ($candidates as $candidate) {
    if (is_file($candidate.'/vendor/autoload.php') && is_file($candidate.'/bootstrap/app.php')) {
        $appBase = rtrim($candidate, '/');

        break;
    }
}

if ($appBase === null) {
    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    echo "Read-Assist: folder aplikasi tidak ditemukan.\n";
    echo 'Document root: '.__DIR__."\n";
    echo "Lokasi yang diperiksa:\n";
    foreach ($candidates as $candidate) {
        echo '  - '.$candidate
            .(is_dir($candidate) ? ' (ada, tapi tidak lengkap)' : ' (tidak ada)')."\n";
    }
    echo 'Periksa document root, lalu jalankan scripts/deploy-public.sh --verify.';
    exit;
}

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = $appBase.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require $appBase.'/vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once $appBase.'/bootstrap/app.php';

$app->handleRequest(Request::capture());
