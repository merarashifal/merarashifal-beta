<?php
header('Content-Type: text/plain');
$base = dirname(__DIR__);
echo "Base: $base\n";
if (is_dir($base)) {
    $items = scandir($base);
    foreach ($items as $item) {
        $full = "$base/$item";
        echo "$item (" . (is_dir($full) ? 'DIR' : 'FILE') . ")\n";
    }
}
$pub = "$base/public_html";
if (is_dir($pub)) {
    echo "\npublic_html files sample:\n";
    $p_items = array_slice(scandir($pub), 0, 20);
    foreach ($p_items as $pi) {
        echo "  $pi\n";
    }
}
