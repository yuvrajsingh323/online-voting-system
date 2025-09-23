-- Fix admin enum in standard column
ALTER TABLE `userdata`
MODIFY COLUMN `standard` ENUM('candidate', 'voter', 'admin') NOT NULL;

-- Update any existing admin records to have correct enum value
UPDATE `userdata` SET `standard` = 'admin' WHERE `standard` = 'administrator';

-- Verify the change
DESCRIBE `userdata`;