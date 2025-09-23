-- Migration: Create shift_reports table (MySQL version)
-- Description: Store all shift report data with proper relational structure
-- Created: 2025-09-19

CREATE TABLE IF NOT EXISTS shift_reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    shift_date DATE NOT NULL,
    shift_type ENUM('morning','evening') NOT NULL DEFAULT 'morning',
    location VARCHAR(100) NOT NULL,
    checklist_data JSON NULL,
    reviews_count INT DEFAULT 0,
    shipments_data JSON NULL,
    refunds_data JSON NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_shift_reports_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Indexes
CREATE INDEX idx_shift_reports_shift_date ON shift_reports (shift_date);
CREATE INDEX idx_shift_reports_location ON shift_reports (location);
CREATE INDEX idx_shift_reports_shift_type ON shift_reports (shift_type);
CREATE INDEX idx_shift_reports_user_date ON shift_reports (user_id, shift_date);
CREATE INDEX idx_shift_reports_created_at ON shift_reports (created_at);