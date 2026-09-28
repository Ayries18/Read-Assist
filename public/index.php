<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

/*
|--------------------------------------------------------------------------
| Document Root
|--------------------------------------------------------------------------
|
| Front controller ini dilayani dari dua lokasi berbeda, jadi lokasi folder
| aplikasi harus ditentukan dulu:
|
|   1. Pengembangan lokal
|      Document root = Read-Assist/public
|      Folder aplikasi = __DIR__.'/..'
|
|   2. Hosting cPanel
|      Document root = ~/public_html
|      Folder aplikasi = ~/Read-Assist
|
| Perhatikan bahwa pada layout cPanel folder aplikasi adalah SAUDARA sejajar
| dari document root, bukan anaknya. Jadi jalurnya dirname(__DIR__).'/Read-Assist'
| dan BUKAN __DIR__.'/Read-Assist' yang menunjuk direktori kosong di dalam
| document root.
|
| Satu berkas ini dipakai untuk kedua layout supaya deploy-public.sh bisa
| menyalin apa adanya tanpa perlu menyunting file di server.
|
*/

$appBase = __DIR__.'/..';

if (! is_file($appBase.'/vendor/autoload.php')) {
    $appBase = dirname(__DIR__).'/Read-Assist';
}

if (! is_file($appBase.'/vendor/autoload.php') || ! is_file($appBase.'/bootstrap/app.php')) {
    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    echo "Read-Assist: folder aplikasi tidak ditemukan di document root ini.\n";
    echo 'Document root : '.__DIR__."\n";
    echo ' Dicoba        : '.__DIR__.'/..'."\n";
    echo ' Dicoba        : '.dirname(__DIR__).'/Read-Assist'."\n";
    echo ' Perbaiki dengan: bash scripts/deploy-public.sh';

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
