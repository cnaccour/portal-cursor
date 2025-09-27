<?php
/**
 * Public Signup Page (Invitation-based)
 * Looks and feels like any other app page (uses global header/footer)
 */

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/invitation-manager.php';
require_once __DIR__ . '/includes/auth.php';

// If already logged in, go to dashboard
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!empty($_SESSION['user_id'])) {
    header('Location: /portal/dashboard.php');
    exit;
}

// Ensure CSRF for signup flow
if (empty($_SESSION['signup_csrf_token'])) {
    $_SESSION['signup_csrf_token'] = bin2hex(random_bytes(32));
}

$token = $_GET['token'] ?? '';
$invitation = null;
$error_message = '';

try {
    if ($token === '') {
        throw new InvalidArgumentException('Invalid or missing invitation token.');
    }
    $invitation = InvitationManager::getInstance()->getInvitationByToken($token);
    if (!$invitation) {
        throw new InvalidArgumentException('Invalid invitation token.');
    }
    if ($invitation['status'] !== 'pending') {
        throw new InvalidArgumentException('This invitation is no longer active.');
    }
    if (strtotime($invitation['expires_at']) < time()) {
        throw new InvalidArgumentException('This invitation has expired.');
    }
} catch (Throwable $e) {
    $error_message = $e->getMessage();
}

// Handle POST (registration)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$error_message) {
    try {
        $csrf = $_POST['csrf_token'] ?? '';
        if (!hash_equals($_SESSION['signup_csrf_token'] ?? '', $csrf)) {
            throw new InvalidArgumentException('Security token invalid. Please refresh and try again.');
        }

        $first_name = trim($_POST['first_name'] ?? '');
        $last_name = trim($_POST['last_name'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if ($first_name === '' || $last_name === '') {
            throw new InvalidArgumentException('Please enter your first and last name.');
        }
        if (strlen($first_name) < 2 || strlen($last_name) < 2) {
            throw new InvalidArgumentException('Name must be at least 2 characters.');
        }
        if (strlen($password) < 8) {
            throw new InvalidArgumentException('Password must be at least 8 characters long.');
        }
        if ($password !== $confirm) {
            throw new InvalidArgumentException('Passwords do not match.');
        }

        $full_name = trim($first_name . ' ' . $last_name);
        $user_id = InvitationManager::getInstance()->acceptInvitation($token, $full_name, $password);

        // Login session
        $_SESSION['user_id'] = $user_id;
        $_SESSION['email'] = $invitation['email'];
        $_SESSION['name'] = $full_name;
        $_SESSION['role'] = $invitation['role'];
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        unset($_SESSION['signup_csrf_token']);
        session_regenerate_id(true);

        header('Location: /portal/dashboard.php?welcome=1');
        exit;
    } catch (Throwable $e) {
        $error_message = $e->getMessage();
    }
}

function roleLabel($role) {
    $map = ['admin'=>'Administrator','manager'=>'Manager','support'=>'Support Specialist','staff'=>'Staff Member','viewer'=>'Viewer'];
    return $map[$role] ?? ucfirst($role);
}

require __DIR__ . '/includes/header.php';
?>

<div class="max-w-lg mx-auto">
  <div class="mb-6">
    <h1 class="text-2xl font-semibold">Complete Your Registration</h1>
    <p class="text-sm text-gray-600 mt-1">Create your account to access the team portal.</p>
  </div>

  <?php if ($error_message): ?>
  <div class="bg-red-50 border border-red-200 rounded-xl p-4 text-red-800 mb-6">
    <div class="flex items-start gap-2">
      <svg class="w-5 h-5 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10A8 8 0 112 10a8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
      <div class="text-sm"><?= htmlspecialchars($error_message) ?></div>
    </div>
  </div>
  <?php else: ?>

  <div class="bg-white rounded-xl border">
    <div class="p-6 border-b">
      <div class="flex items-center justify-between">
        <div>
          <div class="text-sm font-semibold text-gray-900">Invitation Details</div>
          <p class="text-xs text-gray-600 mt-1">Sent to <?= htmlspecialchars($invitation['email']) ?> · Role: <?= htmlspecialchars(roleLabel($invitation['role'])) ?></p>
        </div>
      </div>
    </div>

    <form method="POST" class="p-6 space-y-4">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['signup_csrf_token']) ?>">

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">First Name</label>
          <input type="text" name="first_name" required value="<?= htmlspecialchars($_POST['first_name'] ?? '') ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Last Name</label>
          <input type="text" name="last_name" required value="<?= htmlspecialchars($_POST['last_name'] ?? '') ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
        </div>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Password</label>
        <input type="password" name="password" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Create a strong password">
        <p class="text-xs text-gray-500 mt-1">Must be at least 8 characters long</p>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Confirm Password</label>
        <input type="password" name="confirm_password" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Confirm your password">
      </div>

      <div class="flex justify-end">
        <button type="submit" class="px-4 py-2 text-white rounded-lg transition-colors" style="background-color:#000">Complete Registration</button>
      </div>
    </form>
  </div>

  <?php endif; ?>
</div>

<script>
// Minimal client-side checks
document.addEventListener('DOMContentLoaded', function() {
  const form = document.querySelector('form');
  if (!form) return;
  form.addEventListener('submit', function(e) {
    const pw = form.querySelector('input[name="password"]').value;
    const cpw = form.querySelector('input[name="confirm_password"]').value;
    if (pw.length < 8) { e.preventDefault(); alert('Password must be at least 8 characters long.'); }
    if (pw !== cpw) { e.preventDefault(); alert('Passwords do not match.'); }
  });
});
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
