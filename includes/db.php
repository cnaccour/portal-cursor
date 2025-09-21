<?php
// Database connection for both Replit and cPanel environments
// Uses environment detection for automatic configuration

if (session_status() === PHP_SESSION_NONE) { 
    session_start(); 
}

// Detect environment and set database configuration
if (isset($_ENV['DATABASE_URL']) || getenv('DATABASE_URL')) {
    // Replit environment with PostgreSQL
    $database_url = $_ENV['DATABASE_URL'] ?? getenv('DATABASE_URL');
    $db_config = parse_url($database_url);
    
    $host = $db_config['host'];
    $port = $db_config['port'] ?? 5432;
    $dbname = ltrim($db_config['path'], '/');
    $username = $db_config['user'];
    $password = $db_config['pass'];
    
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require";
} else {
    // cPanel production environment with MySQL
    $host = 'localhost';
    $dbname = 'portaljjosephsal_salon_portal';
    $username = 'portaljjosephsal_portal_user';
    $password = 'jjsportaladmin81';
    
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
}

try {
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    
} catch (PDOException $e) {
    error_log('Database connection failed: ' . $e->getMessage());
    die('Database connection failed. Please contact support.');
}