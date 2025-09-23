-- Migration: Insert demo time off request (development only)
-- Description: Ensures table exists and inserts a realistic demo record for UI verification
-- Created: 2025-01-22

-- Ensure table exists (MySQL-compatible)
CREATE TABLE IF NOT EXISTS time_off_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(255) NOT NULL,
    last_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    work_location VARCHAR(100) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    date_range VARCHAR(255),
    reason VARCHAR(100) NOT NULL,
    additional_info TEXT,
    has_compensation TINYINT(1) DEFAULT 0,
    understands_blackout TINYINT(1) DEFAULT 0,
    status VARCHAR(50) DEFAULT 'pending',
    submitted_by INT NULL,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    reviewed_at TIMESTAMP NULL,
    reviewed_by INT NULL,
    INDEX idx_email (email),
    INDEX idx_dates (start_date, end_date)
);

-- Insert demo request (Eliana Stewson)
INSERT INTO time_off_requests (
    first_name, last_name, email, work_location, start_date, end_date, date_range, reason,
    additional_info, has_compensation, understands_blackout, status, submitted_by
) VALUES (
    'Eliana',
    'Stewson',
    'eliana@jjosephsalon.com',
    'citrus-park',
    '2025-02-10',
    '2025-02-14',
    '2025-02-10 to 2025-02-14',
    'vacation',
    'Family trip planned months in advance. Coverage arranged with front desk team; handed off open tasks and prepared notes for the week prior.',
    1,
    1,
    'pending',
    (SELECT id FROM users WHERE email = 'eliana@jjosephsalon.com' LIMIT 1)
);


