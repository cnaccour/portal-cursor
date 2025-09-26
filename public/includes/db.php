<?php
// DB config (cPanel) - updated
if (session_status() === PHP_SESSION_NONE) { 
    session_start(); 
}

// cPanel MySQL settings (no .env or socket)
$host = '127.0.0.1';
$dbname = 'portaljjosephsal_salon_portal';
$username = 'portaljjosephsal_portal_user';
$password = 'jjsadmin99';

// Attempt multiple DSNs (cPanel variations)
$dsnCandidates = [
    "mysql:host=127.0.0.1;port=3306;dbname=$dbname;charset=utf8mb4",
    "mysql:host=localhost;dbname=$dbname;charset=utf8mb4",
];

$pdo = null;
$lastError = '';
foreach ($dsnCandidates as $dsn) {
    try {
        $pdo = new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        break;
    } catch (PDOException $e) {
        $lastError = $e->getMessage();
        // log and try next DSN
        error_log('[DB TRY FAIL] ' . $dsn . ' :: ' . $lastError);
        @file_put_contents(__DIR__ . '/../db_debug.log', date('Y-m-d H:i:s') . " [DB TRY FAIL] $dsn :: $lastError\n", FILE_APPEND);
    }
}

if (!$pdo) {
    $msg = 'Database connection failed. Please contact support.';
    error_log('[DB FATAL] ' . $msg . ' :: ' . $lastError);
    @file_put_contents(__DIR__ . '/../db_debug.log', date('Y-m-d H:i:s') . " [DB FATAL] $lastError\n", FILE_APPEND);
    die($msg);
}

// Runtime guard: create password_resets if it doesn't exist
$pdo->exec("CREATE TABLE IF NOT EXISTS password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    token VARCHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL,
    used_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    UNIQUE KEY uniq_token (token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");