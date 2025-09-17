-- Add age verification fields to userdata table
ALTER TABLE `userdata`
ADD COLUMN `age` INT NULL AFTER `photo`,
ADD COLUMN `id_proof` VARCHAR(255) NULL AFTER `age`,
ADD COLUMN `verification_status` ENUM('pending', 'verified', 'rejected') DEFAULT 'pending' AFTER `id_proof`,
ADD COLUMN `date_of_birth` DATE NULL AFTER `verification_status`;

-- Add index for better performance
CREATE INDEX idx_verification_status ON `userdata` (`verification_status`);
CREATE INDEX idx_age ON `userdata` (`age`);