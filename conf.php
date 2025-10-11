<?php
// Site Information
$conf['site_name']   = 'DukaHub';
$conf['site_url']    = 'http://localhost/DUKAHUB';
$conf['admin_email'] = 'admin@dukahub.com';

// Email Configuration
$conf = [
    'site_name' => 'Dukahub',

    // SMTP settings
    'smtp_host' => 'smtp.gmail.com',
    'smtp_user' => 'user email',
    'smtp_pass' => '', // Gmail app password
    'smtp_port' => 465
];
// Database Configuration
$conf['db_type'] = 'pdo';
$conf['db_host'] = 'localhost';

$conf['db_user'] = 'root';
$conf['db_pass'] = 'your_password';
$conf['db_name'] = 'dukahub';

// Database Connection
try {
    $dsn = "mysql:host={$conf['db_host']};dbname={$conf['db_name']};charset=utf8mb4";
    $conn = new PDO($dsn, $conf['db_user'], $conf['db_pass']);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}


// Site Language
$conf['site_lang'] = 'en';
