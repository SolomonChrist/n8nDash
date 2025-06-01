-- Add logo_path column to users table
ALTER TABLE users ADD COLUMN logo_path VARCHAR(255) DEFAULT NULL; 