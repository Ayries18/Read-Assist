<?php
$urls = [
    'direct_local' => 'http://127.0.0.1:8000/scan/book/f3fca8bd-7121-44cd-bd18-47ce8b0579bf',
    'tunnel_public' => 'https://522a5b313b4cc0.lhr.life/scan/book/f3fca8bd-7121-44cd-bd18-47ce8b0579bf',
];
$headers = [
    'Host: 522a5b313b4cc0.lhr.life',
    'Accept: */*',
];

foreach ($urls as $label => $url) {
    echo "=== $label ===\n";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    $resp = curl_exec($ch);
    if ($resp === false) {
        echo 'ERROR: ' . curl_error($ch) . "\n";
        echo 'ERRNO: ' . curl_errno($ch) . "\n";
    } else {
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        echo "HTTP_CODE: $code\n";
        echo "HEADER_SIZE: $header_size\n";
        echo "RESPONSE_START:\n";
        echo substr($resp, 0, min(1024, strlen($resp)));
        echo "\n---\n";
    }
    curl_close($ch);
}
