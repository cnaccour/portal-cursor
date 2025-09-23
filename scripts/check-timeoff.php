<?php
require_once __DIR__ . '/../includes/db.php';

try {
    $count = (int)$pdo->query('SELECT COUNT(*) FROM time_off_requests')->fetchColumn();
    echo "time_off_requests: {$count}\n";

    $stmt = $pdo->query("SELECT id, first_name, last_name, email, work_location, start_date, end_date, reason, status, submitted_at FROM time_off_requests ORDER BY id DESC LIMIT 1");
    $row = $stmt->fetch();
    if ($row) {
        echo "latest: ".$row['id']." | ".$row['first_name']." ".$row['last_name']." | ".$row['email']." | ".$row['work_location']." | ".$row['start_date']." to ".$row['end_date']." | reason=".$row['reason']." | status=".$row['status']." | at=".$row['submitted_at']."\n";
    }
} catch (Throwable $e) {
    echo "error: ".$e->getMessage()."\n";
    exit(1);
}


