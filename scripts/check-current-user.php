<?php
// Check what user is currently logged in via session
session_start();

echo "=== CURRENT USER CHECK ===\n";

if (isset($_SESSION['user_id'])) {
    echo "✓ User ID from session: " . $_SESSION['user_id'] . "\n";
} else {
    echo "✗ No user_id in session\n";
}

if (isset($_SESSION['user_name'])) {
    echo "✓ User name from session: " . $_SESSION['user_name'] . "\n";
} else {
    echo "✗ No user_name in session\n";
}

if (isset($_SESSION['user_role'])) {
    echo "✓ User role from session: " . $_SESSION['user_role'] . "\n";
} else {
    echo "✗ No user_role in session\n";
}

echo "\nFull session data:\n";
print_r($_SESSION);

echo "\n=== CHECK COMPLETE ===\n";
?>
