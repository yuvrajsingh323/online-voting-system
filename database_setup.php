<?php
include('actions/connect.php');

echo "<h2>Database Setup and Verification</h2>";

// Check if votes column exists
$check_column = "SHOW COLUMNS FROM userdata LIKE 'votes'";
$result = mysqli_query($conn, $check_column);

if (mysqli_num_rows($result) == 0) {
    echo "Votes column doesn't exist. Adding it...<br>";

    // Add votes column
    $add_column = "ALTER TABLE userdata ADD COLUMN votes INT DEFAULT 0";
    if (mysqli_query($conn, $add_column)) {
        echo "✅ Votes column added successfully!<br>";
    } else {
        echo "❌ Error adding votes column: " . mysqli_error($conn) . "<br>";
        exit;
    }
} else {
    echo "✅ Votes column already exists.<br>";
}

// Initialize all candidate votes to 0 if they are NULL
$init_votes = "UPDATE userdata SET votes = 0 WHERE standard = 'candidate' AND votes IS NULL";
if (mysqli_query($conn, $init_votes)) {
    echo "✅ Initialized NULL candidate votes to 0.<br>";
    echo "Affected rows: " . mysqli_affected_rows($conn) . "<br>";
} else {
    echo "❌ Error initializing votes: " . mysqli_error($conn) . "<br>";
}

// Verify table structure
echo "<h3>Table Structure Verification:</h3>";
$structure_query = "DESCRIBE userdata";
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

echo "<br><br><a href='partials/dashboard.php'>Go to Dashboard</a>";
?>