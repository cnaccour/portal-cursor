<?php
// Force session initialization with more explicit settings
if (session_status() === PHP_SESSION_NONE) {
  ini_set('session.cookie_httponly', 1);
  ini_set('session.use_only_cookies', 1);
  session_start();
}

function require_login() {
  if (empty($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit;
  }
}

// Role hierarchy (higher numbers = more permissions)
function get_role_level($role) {
  $levels = [
    'viewer' => 0,    // Non-logged in users
    'staff' => 1,     // Basic access
    'support' => 2,   // Support functions
    'manager' => 3,   // Location management
    'admin' => 4      // Full access
  ];
  return $levels[$role] ?? 0;
}

// Check if user has minimum required role
function has_role($required_role) {
  $user_role = $_SESSION['role'] ?? 'viewer';
  return get_role_level($user_role) >= get_role_level($required_role);
}

// Require specific minimum role
function require_role($required_role) {
  if (!has_role($required_role)) {
    header('HTTP/1.1 403 Forbidden');
    require __DIR__.'/header.php';
    echo '<div class="bg-red-50 border border-red-200 rounded-xl p-6 text-center">';
    echo '<h1 class="text-xl font-semibold text-red-800 mb-2">Access Denied</h1>';
    echo '<p class="text-red-600">You need ' . htmlspecialchars($required_role) . ' permissions to access this page.</p>';
    echo '<a href="/dashboard.php" class="inline-block mt-4 px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">Back to Dashboard</a>';
    echo '</div>';
    require __DIR__.'/footer.php';
    exit;
  }
}

// Get current user role
function get_current_role() {
  return $_SESSION['role'] ?? 'viewer';
}

// Get all available roles
function get_all_roles() {
  return ['admin', 'manager', 'support', 'staff', 'viewer'];
}

// Get role display name
function get_role_display_name($role) {
  $names = [
    'admin' => 'Administrator',
    'manager' => 'Manager', 
    'support' => 'Support',
    'staff' => 'Staff',
    'viewer' => 'Viewer'
  ];
  return $names[$role] ?? ucfirst($role ?? 'viewer');
}