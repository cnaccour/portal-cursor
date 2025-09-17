<?php
require __DIR__.'/../includes/auth.php';
require_login();

// Local-only: save submissions to a text file
$data = [
  'user'     => $_SESSION['name'] ?? 'Unknown',
  'location' => $_POST['location'] ?? '',
  'shipments'=> $_POST['shipments'] ?? '',
  'vendor'   => $_POST['vendor'] ?? '',
  'notes'    => $_POST['notes'] ?? '',
  'time'     => date('Y-m-d H:i:s')
];

$file = __DIR__ . '/../submissions.txt';
$line = json_encode($data) . PHP_EOL;
file_put_contents($file, $line, FILE_APPEND);

header('Location: /dashboard.php?ok=1');
exit;