<?php
header('Content-Type: text/plain');
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Testing DB Connection...\n";

$host = 'sdb-r.hosting.stackcp.net';
$user = 'beta_merarashifal-323036a931';
$pass = 'merarashifal_beta';
$name = 'beta_merarashifal-323036a931';

$conn = @new mysqli($host, $user, $pass, $name);

if ($conn->connect_error) {
    echo "FAILED: " . $conn->connect_error . "\n";
} else {
    echo "SUCCESS: Connected to database!\n";
    $res = $conn->query("SHOW TABLES LIKE 'af_%'");
    echo "Tables with prefix 'af_': " . ($res ? $res->num_rows : 0) . "\n";
    
    // Check siteurl
    $res2 = $conn->query("SELECT option_value FROM af_options WHERE option_name = 'siteurl'");
    if ($res2 && $row = $res2->fetch_assoc()) {
        echo "DB siteurl: " . $row['option_value'] . "\n";
    }
}
