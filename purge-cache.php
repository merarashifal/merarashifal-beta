<?php
if (!isset($_GET['token']) || $_GET['token'] !== 'mr_deploy_2026') {
    http_response_code(403);
    die('Unauthorized');
}

header('Content-Type: text/plain; charset=utf-8');

require_once __DIR__ . '/wp-load.php';

echo "Active Plugins:\n";
print_r(get_option('active_plugins'));

echo "\nMU Plugins / Constants:\n";
$constants = get_defined_constants(true);
if (isset($constants['user'])) {
    foreach ($constants['user'] as $k => $v) {
        if (stripos($k, 'stack') !== false || stripos($k, 'cache') !== false) {
            echo "$k = " . (is_scalar($v) ? $v : json_encode($v)) . "\n";
        }
    }
}

echo "\nClasses:\n";
foreach (get_declared_classes() as $c) {
    if (stripos($c, 'stack') !== false) {
        echo "Class: $c\n";
        print_r(get_class_methods($c));
    }
}
