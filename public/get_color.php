<?php
header('Content-Type: text/plain');

$imagePath = __DIR__ . '/logo.png';
if (!file_exists($imagePath)) {
    file_put_contents(__DIR__ . '/logo_info.txt', "File logo.png not found.");
    echo "File logo.png not found.";
    exit;
}

$info = getimagesize($imagePath);
$width = $info[0];
$height = $info[1];
$mime = $info['mime'];

$im = imagecreatefrompng($imagePath);
if (!$im) {
    file_put_contents(__DIR__ . '/logo_info.txt', "Failed to load PNG.");
    echo "Failed to load PNG.";
    exit;
}

// Analyze a few pixels to find dominant colors
$colors = [];
for ($x = 0; $x < $width; $x += max(1, intval($width / 30))) {
    for ($y = 0; $y < $height; $y += max(1, intval($height / 30))) {
        $rgb = imagecolorat($im, $x, $y);
        $rgba = imagecolorsforindex($im, $rgb);
        
        // Skip fully transparent pixels
        if ($rgba['alpha'] >= 127) {
            continue;
        }
        
        $hex = sprintf("#%02x%02x%02x", $rgba['red'], $rgba['green'], $rgba['blue']);
        if (!isset($colors[$hex])) {
            $colors[$hex] = 0;
        }
        $colors[$hex]++;
    }
}

arsort($colors);
$topColors = array_slice($colors, 0, 10, true);

$output = "Dimensions: {$width}x{$height}\n";
$output .= "MIME: {$mime}\n";
$output .= "Top colors (hex => count):\n";
foreach ($topColors as $hex => $count) {
    $output .= "  {$hex} => {$count}\n";
}

file_put_contents(__DIR__ . '/logo_info.txt', $output);
echo "Analysis complete. Written to logo_info.txt\n";
echo $output;
