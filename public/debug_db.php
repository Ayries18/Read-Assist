<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$books = \App\Models\AudioBuku::all();
$out = "";
foreach ($books as $b) {
    $out .= sprintf("ID: %d, Judul: %s, Token: %s (length: %d)\n", $b->id, $b->judul, $b->qr_token, strlen($b->qr_token));
}
file_put_contents(__DIR__ . '/../db_debug.txt', $out);
echo "Done\n";
