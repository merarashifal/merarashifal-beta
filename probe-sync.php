<?php
header('Content-Type: text/plain');
$beta_dir = __DIR__;
$public_html = realpath(__DIR__ . '/../public_html');

echo "Beta Dir: " . $beta_dir . "\n";
echo "Public HTML: " . ($public_html ?: 'NOT FOUND') . "\n";

if ($public_html) {
    echo "Is Dir: " . (is_dir($public_html) ? 'YES' : 'NO') . "\n";
    echo "Is Writable: " . (is_writable($public_html) ? 'YES' : 'NO') . "\n";
    
    $test_file = $public_html . '/probe_test.txt';
    $write_res = @file_put_contents($test_file, 'test_' . time());
    if ($write_res !== false) {
        echo "Write Test: SUCCESS\n";
        @unlink($test_file);
    } else {
        echo "Write Test: FAILED (Permissions or open_basedir)\n";
    }
}
