<?php
// migrate.php

// Load environment variables from .env file if it exists
if (file_exists(__DIR__ . '/.env')) {
    $lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
            putenv(trim($key) . '=' . trim($value));
        }
    }
}

// MAMP PRO Development Configuration
$host   = getenv('DB_HOST') ?: 'localhost';
$user   = getenv('DB_USER') ?: 'root';
$pass   = getenv('DB_PASS') ?: 'root';
$name   = getenv('DB_NAME') ?: 'portal_dev2';
$port   = isset($_ENV['DB_PORT']) ? (int)$_ENV['DB_PORT'] : 3306;
$socket = getenv('DB_SOCKET') ?: '/Applications/MAMP/tmp/mysql/mysql.sock';

$mysqli = new mysqli($host, $user, $pass, $name, $port, $socket);
if ($mysqli->connect_error) {
    fwrite(STDERR, "Connection failed ({$mysqli->connect_errno}): {$mysqli->connect_error}\n");
    exit(1);
}

// Track applied migrations
$mysqli->query("
    CREATE TABLE IF NOT EXISTS migrations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        filename VARCHAR(255) UNIQUE,
        applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )
");

$files = glob(__DIR__ . "/database/migrations/*.sql");
sort($files);

foreach ($files as $file) {
    $filename = basename($file);

    $check = $mysqli->prepare("SELECT 1 FROM migrations WHERE filename = ?");
    $check->bind_param("s", $filename);
    $check->execute();
    $check->store_result();

    if ($check->num_rows === 0) {
        echo "Applying migration: $filename\n";
        $sql = file_get_contents($file);

        // Clean out DELIMITER statements (not supported by mysqli::multi_query)
        $sql = preg_replace('/^\s*DELIMITER\s+.+$/mi', '', $sql);

        if ($mysqli->multi_query($sql)) {
            while ($mysqli->more_results() && $mysqli->next_result()) { /* flush */ }
            $insert = $mysqli->prepare("INSERT INTO migrations (filename) VALUES (?)");
            $insert->bind_param("s", $filename);
            $insert->execute();
            echo "✅ Done: $filename\n";
        } else {
            fwrite(STDERR, "❌ Error applying $filename: " . $mysqli->error . "\n");
            exit(1);
        }
    } else {
        echo "Skipping (already applied): $filename\n";
    }
}

echo "All migrations applied.\n";