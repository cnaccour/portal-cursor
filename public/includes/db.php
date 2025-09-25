<?php
if (session_status() === PHP_SESSION_NONE) { 
    session_start(); 
}

// cPanel MySQL settings (no .env or socket)
$host = 'localhost';
$dbname = 'portaljjosephsal_salon_portal';
$username = 'portaljjosephsal_portal_user';
$password = 'jjsadminportal99';
$dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

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
} catch (PDOException $e) {
    error_log('Database connection failed: ' . $e->getMessage());
    die('Database connection failed. Please contact support.');
}