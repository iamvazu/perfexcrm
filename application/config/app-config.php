<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
* --------------------------------------------------------------------------
* Heroku Environment Configuration
* --------------------------------------------------------------------------
*/

// Parse JAWSDB_URL or CLEARDB_DATABASE_URL or DATABASE_URL
$db_url_string = getenv('JAWSDB_URL') ?: getenv('CLEARDB_DATABASE_URL') ?: getenv('DATABASE_URL');
$db_url = $db_url_string ? parse_url($db_url_string) : [];

// Base Site URL
// Base Site URL
if (getenv('APP_BASE_URL')) {
    define('APP_BASE_URL', getenv('APP_BASE_URL'));
} else {
    // Dynamic detection for Heroku or other environments
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    define('APP_BASE_URL', $protocol . '://' . $host . '/');
}

// Encryption Key
define('APP_ENC_KEY', getenv('APP_ENC_KEY') ?: 'random_key_if_not_set');

// Database Credentials
define('APP_DB_HOSTNAME', isset($db_url['host']) ? $db_url['host'] : (getenv('APP_DB_HOSTNAME') ?: 'localhost'));
define('APP_DB_USERNAME', isset($db_url['user']) ? $db_url['user'] : (getenv('APP_DB_USERNAME') ?: 'root'));
define('APP_DB_PASSWORD', isset($db_url['pass']) ? $db_url['pass'] : (getenv('APP_DB_PASSWORD') ?: ''));
define('APP_DB_NAME', isset($db_url['path']) ? ltrim($db_url['path'], '/') : (getenv('APP_DB_NAME') ?: 'perfex_crm'));

// Database Charset/Collation
define('APP_DB_CHARSET', getenv('APP_DB_CHARSET') ?: 'utf8mb4');
define('APP_DB_COLLATION', getenv('APP_DB_COLLATION') ?: 'utf8mb4_unicode_ci');

// Session
define('SESS_DRIVER', getenv('SESS_DRIVER') ?: 'database');
define('SESS_SAVE_PATH', getenv('SESS_SAVE_PATH') ?: 'sessions');
define('APP_SESSION_COOKIE_SAME_SITE', getenv('APP_SESSION_COOKIE_SAME_SITE') ?: 'Lax');

// CSRF
define('APP_CSRF_PROTECTION', getenv('APP_CSRF_PROTECTION') === 'true');
