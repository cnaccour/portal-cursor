-- Create shift report email settings table
CREATE TABLE IF NOT EXISTS shift_report_email_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    location VARCHAR(100) NOT NULL,
    email_addresses TEXT NOT NULL COMMENT 'JSON array of email addresses',
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_location (location)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default settings for existing locations
INSERT INTO shift_report_email_settings (location, email_addresses, is_active) VALUES
('Land O\' Lakes', '["manager@jjosephsalon.com", "admin@jjosephsalon.com"]', 1),
('Odessa', '["manager@jjosephsalon.com", "admin@jjosephsalon.com"]', 1),
('Citrus Park', '["manager@jjosephsalon.com", "admin@jjosephsalon.com"]', 1)
ON DUPLICATE KEY UPDATE 
    email_addresses = VALUES(email_addresses),
    is_active = VALUES(is_active);
