<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Heroku Diagnostic</h1>";

// Check Environment Variables
echo "<h2>Environment Variables Check</h2>";
$env_vars = ['JAWSDB_URL', 'CLEARDB_DATABASE_URL', 'DATABASE_URL', 'APP_BASE_URL', 'CI_ENVIRONMENT'];
foreach ($env_vars as $var) {
    $val = getenv($var);
    echo "$var: " . ($val ? "SET (Length: " . strlen($val) . ")" : "NOT SET") . "<br>";
}

// Database Connection Test
echo "<h2>Database Connection Test</h2>";

$db_url_string = getenv('JAWSDB_URL') ?: getenv('CLEARDB_DATABASE_URL') ?: getenv('DATABASE_URL');
$db_url = $db_url_string ? parse_url($db_url_string) : [];

$host = isset($db_url['host']) ? $db_url['host'] : (getenv('APP_DB_HOSTNAME') ?: 'localhost');
$user = isset($db_url['user']) ? $db_url['user'] : (getenv('APP_DB_USERNAME') ?: 'root');
$pass = isset($db_url['pass']) ? $db_url['pass'] : (getenv('APP_DB_PASSWORD') ?: '');
$name = isset($db_url['path']) ? ltrim($db_url['path'], '/') : (getenv('APP_DB_NAME') ?: 'perfex_crm');

echo "Attempting connection...<br>";
echo "Host: " . $host . "<br>";
echo "User: " . $user . "<br>";
echo "Database: " . $name . "<br>";

$mysqli = new mysqli($host, $user, $pass, $name);

if ($mysqli->connect_error) {
    echo "<p style='color:red'>Connection Failed: " . $mysqli->connect_error . "</p>";
} else {
    echo "<p style='color:green'>Connection Successful!</p>";
    echo "Server Info: " . $mysqli->server_info;
    $mysqli->close();
}

// Check File Permissions/Paths
echo "<h2>Path Check</h2>";
echo "Current Directory: " . __DIR__ . "<br>";
echo "Application Config File: " . (file_exists('application/config/app-config.php') ? 'Found' : 'Not Found') . "<br>";
?>
