-- Migration script to insert mock users for development
-- Run this after 002_create_user_tables.sql
-- WARNING: Do not run in production

INSERT INTO users (id, name, email, password_hash, role, status, created_at) 
VALUES 
  (1, 'Admin User', 'admin@example.com',
   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
   'admin', 'active', NOW()),
  (2, 'Staff User', 'staff@example.com',
   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
   'admin', 'active', NOW());

-- Reset auto increment so IDs continue from next available
ALTER TABLE users AUTO_INCREMENT = 3;