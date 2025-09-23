<?php
include('actions/connect.php');

echo "<h2>Adding Elections Table for Time Session Management</h2>";

// Create elections table
$table_sql = "CREATE TABLE IF NOT EXISTS `elections` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if (mysqli_query($conn, $table_sql)) {
    echo "✅ Elections table created successfully!<br>";
} else {
    echo "❌ Error creating elections table: " . mysqli_error($conn) . "<br>";
    exit;
}

// Insert default election
$insert_sql = "INSERT INTO `elections` (`name`, `description`, `start_time`, `end_time`, `status`) VALUES
('General Election 2025', 'Main election for candidates', '2025-09-25 09:00:00', '2025-09-25 17:00:00', 'upcoming')";

if (mysqli_query($conn, $insert_sql)) {
    echo "✅ Default election inserted successfully!<br>";
} else {
    echo "❌ Error inserting default election: " . mysqli_error($conn) . "<br>";
}

// Verify table structure
echo "<h3>Elections Table Structure:</h3>";
$structure_query = "DESCRIBE elections";
$structure_result = mysqli_query($conn, $structure_query);

if ($structure_result) {
    echo "<table border='1'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while ($row = mysqli_fetch_assoc($structure_result)) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "<td>" . $row['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "❌ Error describing table: " . mysqli_error($conn);
}

echo "<br><br><a href='admin_simple.php'>Go to Admin Dashboard</a>";
?>