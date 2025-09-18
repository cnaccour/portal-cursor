<?php
session_start(); // Start session at the beginning
require __DIR__.'/includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = trim($_POST['email'] ?? '');
  $pass  = $_POST['password'] ?? '';

  // Local-only: search the mock_users array
  $u = null;
  foreach ($mock_users as $user) {
    if ($user['email'] === $email) {
      $u = $user;
      break;
    }
  }

  if ($u && password_verify($pass, $u['password_hash'])) {
    session_regenerate_id(true); // Prevent session fixation attacks
    $_SESSION['user_id'] = $u['id'];
    $_SESSION['name'] = $u['name'];
    $_SESSION['role'] = $u['role'] ?? 'staff'; // Default to staff if no role set
    $_SESSION['email'] = $u['email']; // Add email to session
    header('Location: /dashboard.php');
    exit;
  } else {
    $err = "Invalid email or password.";
  }
}

require __DIR__.'/includes/header.php';
?>

<div class="max-w-md mx-auto bg-white p-6 rounded-xl border mt-10">
  <h1 class="text-xl font-semibold mb-4">Login</h1>
  <?php if (!empty($err)): ?>
    <div class="text-red-600 mb-3"><?= htmlspecialchars($err) ?></div>
  <?php endif; ?>
  <form method="post" class="space-y-3">
    <div>
      <label class="block text-sm mb-1">Email</label>
      <input class="w-full border rounded-lg px-3 py-2" type="email" name="email" required>
    </div>
    <div>
      <label class="block text-sm mb-1">Password</label>
      <input class="w-full border rounded-lg px-3 py-2" type="password" name="password" required>
    </div>
    <button class="mt-2 px-4 py-2 rounded-lg bg-gray-900 text-white">Sign in</button>
  </form>
</div>

<?php require __DIR__.'/includes/footer.php'; ?>