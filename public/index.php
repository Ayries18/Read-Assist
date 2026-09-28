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
|                            sedangkan folder aplikasi berada di
|                            ~/Read-Assist, yaitu __DIR__/Read-Assist
|
| Tanpa deteksi di bawah, file ini akan mencari autoloader di lokasi yang
| salah pada salah satu layout dan situs akan gagal total dengan HTTP 500.
| Karena itu repo harus memuat SATU file yang benar untuk keduanya, supaya
| scripts/deploy-public.sh bisa menyalin apa adanya tanpa divergensi.
|
*/

$appBase = null;

foreach ([__DIR__.'/..', __DIR__.'/Read-Assist', dirname(__DIR__)] as $candidate) {
    if (is_file($candidate.'/vendor/autoload.php') && is_file($candidate.'/bootstrap/app.php')) {
        $appBase = rtrim($candidate, '/');

        break;
    }
}

if ($appBase === null) {
    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    exit('Read-Assist: lokasi folder aplikasi tidak ditemukan. '
        .'Periksa document root dan isi scripts/deploy-public.sh.');
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
