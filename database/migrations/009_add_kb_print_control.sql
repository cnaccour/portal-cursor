-- Add print control field to kb_articles table
-- This allows administrators to control which articles can be printed

ALTER TABLE kb_articles 
ADD COLUMN allow_print BOOLEAN DEFAULT TRUE COMMENT 'Whether this article can be printed';

-- Update existing Email Setup article to not allow printing
UPDATE kb_articles 
SET allow_print = FALSE 
WHERE slug = 'email-setup-instructions';
