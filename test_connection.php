<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Heroku Diagnostic</h1>";

echo "<h2>Environment Variables Check</h2>";
$vars = ['JAWSDB_URL', 'CLEARDB_DATABASE_URL', 'DATABASE_URL', 'APP_BASE_URL', 'CI_ENVIRONMENT'];
foreach ($vars as $var) {
    $val = getenv($var);
    echo "$var: " . ($val ? "SET (Length: " . strlen($val) . ")" : "NOT SET") . "<br>";
}

echo "<h2>Database Connection Test</h2>";
$db_url_string = getenv('JAWSDB_URL') ?: getenv('CLEARDB_DATABASE_URL') ?: getenv('DATABASE_URL');
if ($db_url_string) {
    $db_url = parse_url($db_url_string);
    $host = $db_url['host'];
    $user = $db_url['user'];
    $pass = $db_url['pass'];
    $db   = ltrim($db_url['path'], '/');

    echo "Attempting connection...<br>";
    echo "Host: $host<br>";
    echo "User: $user<br>";
    echo "Database: $db<br>";

    $conn = new mysqli($host, $user, $pass, $db);

    if ($conn->connect_error) {
        echo "<span style='color:red'>Connection Failed: " . $conn->connect_error . "</span>";
    } else {
        echo "<span style='color:green'>Connection Successful!</span><br>";
        echo "Server Info: " . $conn->server_info;
        $conn->close();
    }
} else {
    echo "No database URL found.";
}

echo "<h2>Path Check</h2>";
echo "Current Directory: " . __DIR__ . "<br>";
if (file_exists(__DIR__ . '/application/config/app-config.php')) {
    echo "Application Config File: Found<br>";
} else {
    echo "Application Config File: NOT Found (This is expected if not installed, but checking context)<br>";
}

echo "<h2>File Existence Check</h2>";
$paths = [
    'application/third_party/action_hooks.php',
    'application/vendor/bainternet/php-hooks/php-hooks.php',
    'application/config/hooks.php'
];

foreach ($paths as $path) {
    echo "Checking: " . $path . " -> ";
    if (file_exists(__DIR__ . '/' . $path)) {
        echo "<span style='color:green'>FOUND</span><br>";
    } else {
        echo "<span style='color:red'>NOT FOUND</span><br>";
    }
}
