<?php
// Production database connection for both Replit and cPanel environments
// Uses environment detection for automatic configuration

if (session_status() === PHP_SESSION_NONE) { 
    session_start(); 
}

// Database configuration based on environment
$host = 'localhost';
$dbname = 'portaljjosephsal_salon_portal';
$username = 'portaljjosephsal_portal_user';
$password = 'jjsportaladmin81';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    error_log('Database connection failed: ' . $e->getMessage());
    die('Database connection failed. Please contact support.');
}