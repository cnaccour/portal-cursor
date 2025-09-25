<?php
// Debug: log entry to confirm forgot-password.php executes
file_put_contents(__DIR__ . '/../forgot_debug.log', date('Y-m-d H:i:s') . " forgot-password.php loaded\n", FILE_APPEND);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Bootstrap dependencies with resilience on cPanel
try {
  if (file_exists(__DIR__ . '/../config/db.php')) {
    require_once __DIR__ . '/../config/db.php';
  } else {
    require_once __DIR__ . '/includes/db.php';
  }
  // Email helper is deployed inside docroot under /lib
  if (file_exists(__DIR__ . '/lib/Email.php')) {
    require_once __DIR__ . '/lib/Email.php';
  } elseif (file_exists(__DIR__ . '/../lib/Email.php')) {
    // Fallback if deployed above docroot
    require_once __DIR__ . '/../lib/Email.php';
  } else {
    throw new RuntimeException('Email library not found');
  }
} catch (Throwable $bootErr) {
  echo 'Bootstrap error: ' . htmlspecialchars($bootErr->getMessage());
  exit;
}

$info = '';
$error = '';

try {
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $error = 'Please enter a valid email address.';
    } else {
      // Ensure $pdo is available
      if (!isset($pdo)) { throw new RuntimeException('Database not initialized.'); }

      $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? AND (deleted_at IS NULL)');
      $stmt->execute([$email]);
      $user = $stmt->fetch();

      // Always act the same to avoid user enumeration
      $token = bin2hex(random_bytes(32));
      $expires = (new DateTime('+1 hour'))->format('Y-m-d H:i:s');
      if ($user) {
        $ins = $pdo->prepare('INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)');
        $ins->execute([$email, $token, $expires]);

        $base = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
        $link = $base . '/reset-password.php?token=' . urlencode($token);
        $subject = 'Reset your password';
        $body = '<p>You requested a password reset. Click the link below to reset your password:</p><p><a href="' . htmlspecialchars($link) . '">' . htmlspecialchars($link) . '</a></p><p>This link will expire in 1 hour.</p>';
        $alt = "Reset your password: $link (expires in 1 hour)";
        // Use wrapper; ignore result to avoid user enumeration
        send_smtp_email($email, $subject, $body, $alt);
      }
      $info = 'If the email exists in our system, a reset link has been sent.';
    }
  }
} catch (Throwable $e) {
  $error = 'Error: ' . $e->getMessage();
}

require __DIR__.'/includes/header.php';
?>
<div class="max-w-md mx-auto bg-white p-6 rounded-xl border mt-10">
  <h1 class="text-xl font-semibold mb-4">Forgot Password</h1>
  <?php if ($info): ?>
    <div class="text-green-700 bg-green-50 border border-green-200 rounded-lg p-3 mb-3"><?= htmlspecialchars($info) ?></div>
  <?php endif; ?>
  <?php if ($error): ?>
    <div class="text-amber-700 bg-amber-50 border border-amber-200 rounded-lg p-3 mb-3"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>
  <form method="post" class="space-y-3">
    <div>
      <label class="block text-sm mb-1">Email</label>
      <input class="w-full border border-gray-300 rounded-lg px-3 py-2" type="email" name="email" required>
    </div>
    <button class="mt-2 px-4 py-2 rounded-lg bg-gray-900 text-white">Send reset link</button>
  </form>
</div>
<?php require __DIR__.'/includes/footer.php'; ?>
