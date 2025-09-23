-- Add sections control field to kb_articles table
-- This allows administrators to control which articles use collapsible sections

ALTER TABLE kb_articles 
ADD COLUMN enable_sections BOOLEAN DEFAULT TRUE COMMENT 'Whether this article uses collapsible sections';

-- Update existing Email Setup article to not use sections
UPDATE kb_articles 
SET enable_sections = FALSE 
WHERE slug = 'email-setup-instructions';
