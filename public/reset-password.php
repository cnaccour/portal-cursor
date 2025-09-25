<?php
session_start();
require __DIR__.'/includes/db.php';

$token = $_GET['token'] ?? '';
$error = '';
$info = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $token = $_POST['token'] ?? '';
  $p1 = $_POST['password'] ?? '';
  $p2 = $_POST['password_confirm'] ?? '';
  if (strlen($p1) < 8) {
    $error = 'Password must be at least 8 characters.';
  } elseif ($p1 !== $p2) {
    $error = 'Passwords do not match.';
  } else {
    $q = $pdo->prepare('SELECT email, expires_at, used_at FROM password_resets WHERE token = ?');
    $q->execute([$token]);
    $row = $q->fetch();
    if (!$row) {
      $error = 'Invalid or expired reset link.';
    } elseif (!empty($row['used_at']) || (new DateTime() > new DateTime($row['expires_at']))) {
      $error = 'Reset link has expired or already used.';
    } else {
      $hash = password_hash($p1, PASSWORD_BCRYPT);
      $pdo->beginTransaction();
      try {
        $upd = $pdo->prepare('UPDATE users SET password_hash = ? WHERE email = ?');
        $upd->execute([$hash, $row['email']]);
        $mark = $pdo->prepare('UPDATE password_resets SET used_at = NOW() WHERE token = ?');
        $mark->execute([$token]);
        $pdo->commit();
        $info = 'Your password has been reset. You can now log in.';
      } catch (Exception $e) {
        $pdo->rollBack();
        $error = 'Failed to reset password. Please try again.';
      }
    }
  }
}

require __DIR__.'/includes/header.php';
?>
<div class="max-w-md mx-auto bg-white p-6 rounded-xl border mt-10">
  <h1 class="text-xl font-semibold mb-4">Reset Password</h1>
  <?php if ($info): ?>
    <div class="text-green-700 bg-green-50 border border-green-200 rounded-lg p-3 mb-3"><?= htmlspecialchars($info) ?></div>
  <?php endif; ?>
  <?php if ($error): ?>
    <div class="text-amber-700 bg-amber-50 border border-amber-200 rounded-lg p-3 mb-3"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>
  <form method="post" class="space-y-3">
    <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
    <div>
      <label class="block text-sm mb-1">New Password</label>
      <input class="w-full border border-gray-300 rounded-lg px-3 py-2" type="password" name="password" required>
    </div>
    <div>
      <label class="block text-sm mb-1">Confirm Password</label>
      <input class="w-full border border-gray-300 rounded-lg px-3 py-2" type="password" name="password_confirm" required>
    </div>
    <button class="mt-2 px-4 py-2 rounded-lg bg-gray-900 text-white">Reset Password</button>
  </form>
</div>
<?php require __DIR__.'/includes/footer.php'; ?>
