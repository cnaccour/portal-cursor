-- Migration script to move from mock users to database users
-- Run this after 002_create_user_tables.sql
-- WARNING: This migration should only run in development environments

-- DEVELOPMENT ONLY: Check if we're in a non-production environment
-- Production deployments should skip this migration or create secure admin accounts separately

-- Insert development users only if ENVIRONMENT is not 'production'
-- Replace with proper admin account creation for production
INSERT INTO users (id, name, email, password_hash, role, status, created_at) 
SELECT 1, 'Admin User', 'admin@example.com', 
       CASE WHEN COALESCE(current_setting('app.environment', true), 'development') != 'production' 
            THEN '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'
            ELSE NULL 
       END, 'admin', 'active', NOW()
WHERE COALESCE(current_setting('app.environment', true), 'development') != 'production';

INSERT INTO users (id, name, email, password_hash, role, status, created_at) 
SELECT 2, 'Staff User', 'staff@example.com', 
       CASE WHEN COALESCE(current_setting('app.environment', true), 'development') != 'production' 
            THEN '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'
            ELSE NULL 
       END, 'admin', 'active', NOW()
WHERE COALESCE(current_setting('app.environment', true), 'development') != 'production';

-- NOTE: Development passwords are weak and should never be used in production
-- For production: Create admin accounts with strong, unique passwords using environment variables

-- Reset auto increment to continue from next available ID
ALTER TABLE users AUTO_INCREMENT = 3;