-- Add Elections Table for Time Session Management
CREATE TABLE IF NOT EXISTS `elections` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NOT NULL,
    `description` text,
    `start_time` datetime NOT NULL,
    `end_time` datetime NOT NULL,
    `status` enum('upcoming','active','completed','cancelled') DEFAULT 'upcoming',
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_status` (`status`),
    KEY `idx_start_time` (`start_time`),
    KEY `idx_end_time` (`end_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert a default election
INSERT INTO `elections` (`name`, `description`, `start_time`, `end_time`, `status`) VALUES
('General Election 2025', 'Main election for candidates', '2025-09-25 09:00:00', '2025-09-25 17:00:00', 'upcoming');