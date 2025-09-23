-- Fix Database: Add missing age verification columns
-- Run this in phpMyAdmin or MySQL command line

USE `onlinevotingsystem_db`;

-- Add age column if it doesn't exist
ALTER TABLE `userdata`
ADD COLUMN IF NOT EXISTS `age` INT NULL AFTER `photo`;

-- Add id_proof column if it doesn't exist
ALTER TABLE `userdata`
ADD COLUMN IF NOT EXISTS `id_proof` VARCHAR(255) NULL AFTER `age`;

-- Add verification_status column if it doesn't exist
ALTER TABLE `userdata`
ADD COLUMN IF NOT EXISTS `verification_status` ENUM('pending', 'verified', 'rejected') DEFAULT 'pending' AFTER `id_proof`;

-- Add date_of_birth column if it doesn't exist
ALTER TABLE `userdata`
ADD COLUMN IF NOT EXISTS `date_of_birth` DATE NULL AFTER `verification_status`;

-- Create indexes for better performance
CREATE INDEX IF NOT EXISTS `idx_verification_status` ON `userdata` (`verification_status`);
CREATE INDEX IF NOT EXISTS `idx_age` ON `userdata` (`age`);

-- Update existing records to have proper verification status
UPDATE `userdata` SET `verification_status` = 'verified' WHERE `standard` IN ('candidate', 'admin');
UPDATE `userdata` SET `verification_status` = 'pending' WHERE `standard` = 'voter' AND `verification_status` IS NULL;

-- Show the updated table structure
DESCRIBE `userdata`;