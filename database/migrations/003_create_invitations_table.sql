-- Migration: Create invitations table for invitation-based user signup
-- Run this after 002_create_user_tables.sql

CREATE TABLE IF NOT EXISTS invitations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    token VARCHAR(255) NOT NULL UNIQUE,
    role ENUM('admin', 'manager', 'support', 'staff', 'viewer') NOT NULL DEFAULT 'staff',
    invited_by INT NOT NULL,
    status ENUM('pending', 'accepted', 'expired', 'revoked') NOT NULL DEFAULT 'pending',
    expires_at DATETIME NOT NULL,
    accepted_at DATETIME NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Foreign key to users table
    FOREIGN KEY (invited_by) REFERENCES users(id) ON DELETE CASCADE,
    
    -- Indexes for performance
    INDEX idx_email (email),
    INDEX idx_token (token),
    INDEX idx_status (status),
    INDEX idx_expires_at (expires_at),
    INDEX idx_invited_by (invited_by)
);

-- Add invitation tracking to user_audit_log
ALTER TABLE user_audit_log 
ADD COLUMN invitation_id INT NULL,
ADD FOREIGN KEY (invitation_id) REFERENCES invitations(id) ON DELETE SET NULL;

-- Create view for active invitations
CREATE VIEW active_invitations AS
SELECT 
    i.*,
    u.name as invited_by_name,
    u.email as invited_by_email
FROM invitations i
JOIN users u ON i.invited_by = u.id
WHERE i.status = 'pending' AND i.expires_at > NOW();