<?php
// Update Current User Profile (first name, last name, email)

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/db.php';

require_login();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
    exit;
}

try {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $user_id = (int)($_SESSION['user_id'] ?? 0);

    if ($user_id <= 0) {
        throw new RuntimeException('Not authenticated');
    }

    if ($first_name === '' || $last_name === '') {
        throw new InvalidArgumentException('First name and Last name are required');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new InvalidArgumentException('Please enter a valid email');
    }

    $full_name = $first_name . ' ' . $last_name;

    // Ensure email uniqueness (excluding current user)
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? AND id <> ? LIMIT 1');
    $stmt->execute([$email, $user_id]);
    if ($stmt->fetch()) {
        throw new InvalidArgumentException('That email is already in use by another account');
    }

    // Update user
    $stmt = $pdo->prepare('UPDATE users SET name = ?, email = ?, updated_at = NOW() WHERE id = ?');
    $ok = $stmt->execute([$full_name, $email, $user_id]);

    if (!$ok) {
        throw new RuntimeException('Failed to update profile');
    }

    // Update session
    $_SESSION['name'] = $full_name;
    $_SESSION['email'] = $email;

    echo json_encode(['success' => true, 'message' => 'Profile updated successfully', 'name' => $full_name, 'email' => $email]);

} catch (InvalidArgumentException $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} catch (Throwable $e) {
    error_log('Update profile error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'An error occurred while updating your profile']);
}


