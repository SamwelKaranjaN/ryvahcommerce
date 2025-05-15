-- Add last_logout column to users table
ALTER TABLE users
ADD COLUMN last_logout DATETIME NULL DEFAULT NULL; 