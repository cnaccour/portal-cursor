-- Migration: Add Eliana Stewson user
-- Description: Add Eliana Stewson as a manager user for realistic shift reports
-- Created: 2025-01-22

INSERT INTO users (name, email, password_hash, role, status, created_at) 
VALUES (
    'Eliana Stewson',
    'eliana@jjosephsalon.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password: "password"
    'manager',
    'active',
    NOW()
);
