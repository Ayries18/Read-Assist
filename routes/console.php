<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
 * Worker dijadwalkan tiap menit oleh cron `php artisan schedule:run`, bukan
 * daemon permanen, karena shared hosting tidak cocok untuk proses yang
 * berjalan terus-menerus.
 *
 * -d memory_limit dipakai karena worker adalah proses CLI: .user.ini tidak
 * terbaca, sehingga batasnya hanya 128M, sedangkan parser PDF PHP murni butuh
 * ruang lebih longgar.
 *
 * --timeout 600 wajib lebih kecil dari DB_QUEUE_RETRY_AFTER (900). Jika tidak,
 * job dilepas ulang worker lain selagi masih berjalan dan audio tergenerate
 * dua kali.
 */
Schedule::exec(sprintf(
    '%s -d memory_limit=512M %s queue:work --queue=%s --stop-when-empty --sleep=3 --tries=1 --timeout=600 --memory=450',
    PHP_BINARY,
    base_path('artisan'),
    config('queue.connections.database.queue', 'default'),
))
    ->everyMinute()
    ->withoutOverlapping(10)
    ->appendOutputTo(storage_path('logs/queue-worker.log'))
    ->description('Proses antrian GenerateBookAudio');
