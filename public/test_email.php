<?php
require_once __DIR__ . '/../lib/Email.php';
$config = require __DIR__ . '/../config/config.php';
$to = $config['SMTP']['to_email'] ?? '';
if (!$to) { echo 'Set TO_EMAIL in env to test.'; exit; }
$result = send_smtp_email($to, 'SMTP Test', '<p>This is a test email from cPanel deploy test.</p>', 'SMTP Test');
echo $result['success'] ? 'Email sent' : ('Failed: ' . htmlspecialchars($result['error'] ?? 'unknown'));
