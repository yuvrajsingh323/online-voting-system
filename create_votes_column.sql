-- SQL commands to fix the voting system database

-- Add votes column to userdata table
ALTER TABLE userdata ADD COLUMN votes INT DEFAULT 0;

-- Initialize all candidate votes to 0
UPDATE userdata SET votes = 0 WHERE standard = 'candidate';

-- Verify the structure
SELECT * FROM userdata WHERE standard = 'candidate';
