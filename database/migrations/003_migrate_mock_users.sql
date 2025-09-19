-- Migration script to move from mock users to database users
-- Run this after 002_create_user_tables.sql

-- Insert the existing mock users into the database
-- These are the current development users

INSERT INTO users (id, name, email, password_hash, role, status, created_at) VALUES
(1, 'Admin User', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'active', NOW()),
(2, 'Staff User', 'staff@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'active', NOW());

-- Note: The password hashes above correspond to:
-- admin@example.com password: admin123
-- staff@example.com password: staff123

-- Reset auto increment to continue from next available ID
ALTER TABLE users AUTO_INCREMENT = 3;