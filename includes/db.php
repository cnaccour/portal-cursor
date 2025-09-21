<?php
// Local-only mock "database" for login testing in Replit.
// REMOVE this file and restore the real db.php when you deploy.

// Mock invitations data for development
$mock_invitations = [];

$mock_users = [
  [
    'id' => 1,
    'name' => 'Admin User',
    'email' => 'admin@example.com',
    // password is: admin123
    'password_hash' => password_hash('admin123', PASSWORD_DEFAULT),
    'role' => 'admin',
  ],
  [
    'id' => 2,
    'name' => 'Staff User',
    'email' => 'staff@example.com',
    // password is: staff123
    'password_hash' => password_hash('staff123', PASSWORD_DEFAULT),
    'role' => 'admin', // Set to admin as requested
  ],
];