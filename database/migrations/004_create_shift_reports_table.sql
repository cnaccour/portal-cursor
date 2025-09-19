-- Migration: Create shift_reports table
-- Description: Store all shift report data with proper relational structure
-- Created: 2025-09-19

CREATE TABLE IF NOT EXISTS shift_reports (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL,
    shift_date DATE NOT NULL,
    shift_type VARCHAR(20) CHECK (shift_type IN ('morning', 'evening')) NOT NULL DEFAULT 'morning',
    location VARCHAR(100) NOT NULL,
    checklist_data JSONB,
    reviews_count INTEGER DEFAULT 0,
    shipments_data JSONB,
    refunds_data JSONB,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Add foreign key constraint
ALTER TABLE shift_reports 
ADD CONSTRAINT fk_shift_reports_user 
FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;

-- Create indexes for better query performance
CREATE INDEX IF NOT EXISTS idx_shift_reports_shift_date ON shift_reports (shift_date);
CREATE INDEX IF NOT EXISTS idx_shift_reports_location ON shift_reports (location);
CREATE INDEX IF NOT EXISTS idx_shift_reports_shift_type ON shift_reports (shift_type);
CREATE INDEX IF NOT EXISTS idx_shift_reports_user_date ON shift_reports (user_id, shift_date);
CREATE INDEX IF NOT EXISTS idx_shift_reports_created_at ON shift_reports (created_at);

-- Add comments for documentation
COMMENT ON TABLE shift_reports IS 'Store all shift report submissions with detailed tracking';
COMMENT ON COLUMN shift_reports.checklist_data IS 'Array of completed checklist items';
COMMENT ON COLUMN shift_reports.shipments_data IS 'Shipment information: {status, vendor, notes}';
COMMENT ON COLUMN shift_reports.refunds_data IS 'Array of refunds: [{amount, reason, customer, service, notes}]';