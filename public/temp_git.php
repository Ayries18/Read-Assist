<?php
header('Content-Type: text/plain');

echo "=== GIT HISTORY ===\n";
echo shell_exec('git log -n 5 --oneline public/logo-horizontal.svg 2>&1');

echo "\n=== ORIGINAL SVG CONTENT ===\n";
echo shell_exec('git show HEAD~1:public/logo-horizontal.svg 2>&1');

echo "\n=== CURRENT SVG CONTENT ===\n";
echo file_get_contents(__DIR__ . '/logo-horizontal.svg');

echo "\n=== GD ANALYSIS OF LOGO.PNG ===\n";
$imagePath = __DIR__ . '/logo.png';
if (file_exists($imagePath)) {
    $info = getimagesize($imagePath);
    echo "Dimensions: {$info[0]}x{$info[1]}\n";
    echo "MIME: {$info['mime']}\n";
    $im = imagecreatefrompng($imagePath);
    if ($im) {
        $colors = [];
        $width = $info[0];
        $height = $info[1];
        for ($x = 0; $x < $width; $x += max(1, intval($width / 50))) {
            for ($y = 0; $y < $height; $y += max(1, intval($height / 50))) {
                $rgb = imagecolorat($im, $x, $y);
                $rgba = imagecolorsforindex($im, $rgb);
                if ($rgba['alpha'] >= 127) continue;
                $hex = sprintf("#%02x%02x%02x", $rgba['red'], $rgba['green'], $rgba['blue']);
                if (!isset($colors[$hex])) $colors[$hex] = 0;
                $colors[$hex]++;
            }
        }
        arsort($colors);
        echo "Top colors:\n";
        foreach (array_slice($colors, 0, 15, true) as $hex => $count) {
            echo "  {$hex} => {$count}\n";
        }
    } else {
        echo "Failed to load PNG with GD\n";
    }
} else {
    echo "logo.png not found\n";
}
