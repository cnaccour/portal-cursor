-- Add user tracking to kb_articles table
-- This allows tracking who created and last updated each article

ALTER TABLE kb_articles 
ADD COLUMN created_by INT DEFAULT NULL COMMENT 'User ID who created the article',
ADD COLUMN updated_by INT DEFAULT NULL COMMENT 'User ID who last updated the article';

-- Add foreign keys if users table exists (may be skipped on some hosts)
ALTER TABLE kb_articles 
ADD CONSTRAINT fk_kb_created_by FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL;
ALTER TABLE kb_articles 
ADD CONSTRAINT fk_kb_updated_by FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL;
