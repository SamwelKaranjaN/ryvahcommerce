-- Add shipping fields to users table
ALTER TABLE `users`
ADD COLUMN `city` varchar(100) DEFAULT NULL AFTER `address`,
ADD COLUMN `state` varchar(100) DEFAULT NULL AFTER `city`,
ADD COLUMN `postal_code` varchar(20) DEFAULT NULL AFTER `state`;

-- Update existing users with default values
UPDATE `users` 
SET 
    `city` = 'New York',
    `state` = 'NY',
    `postal_code` = '10001'
WHERE `city` IS NULL; 