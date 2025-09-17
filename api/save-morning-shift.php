<?php
require __DIR__.'/../includes/auth.php';
require_login();

// Collect form data
$data = [
  'user'       => $_SESSION['name'] ?? 'Unknown',
  'shift_date' => $_POST['shift_date'] ?? '',
  'location'   => $_POST['location'] ?? '',
  'checklist'  => $_POST['checklist'] ?? [],
  'reviews'    => $_POST['reviews_count'] ?? 0,
  'shipments'  => [
    'status' => $_POST['shipments'] ?? 'no',
    'vendor' => $_POST['shipment_vendor'] ?? '',
    'notes'  => $_POST['shipment_notes'] ?? '',
  ],
  // Filter refunds to remove empty entries
  $refunds = $_POST['refunds'] ?? [];
  $refunds = array_filter($refunds, function($r) {
    return !empty($r['amount']) || !empty($r['reason']) || !empty($r['customer']) || !empty($r['service']) || !empty($r['notes']);
  });

  'refunds'    => array_values($refunds),
  'notes'      => $_POST['notes'] ?? '',
  'time'       => date('Y-m-d H:i:s')
];

// Save as JSON line into text file
$file = __DIR__ . '/../morning-shift.txt';
$line = json_encode($data, JSON_UNESCAPED_UNICODE) . PHP_EOL;
file_put_contents($file, $line, FILE_APPEND);

// Redirect back to dashboard with success flag
header('Location: /dashboard.php?ok=1');
exit;