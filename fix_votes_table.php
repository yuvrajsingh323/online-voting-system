<?php
include('actions/connect.php');

echo "<h3>Fixing Votes Column</h3>";

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
    }
} else {
    echo "✅ Votes column already exists.<br>";
}

// Initialize all candidate votes to 0 if they are NULL
$init_votes = "UPDATE userdata SET votes = 0 WHERE standard = 'candidate' AND (votes IS NULL OR votes = '')";
if (mysqli_query($conn, $init_votes)) {
    echo "✅ Initialized candidate votes to 0.<br>";
    echo "Affected rows: " . mysqli_affected_rows($conn) . "<br>";
} else {
    echo "❌ Error initializing votes: " . mysqli_error($conn) . "<br>";
}

// Auto-redirect to dashboard after 3 seconds
echo "<br>✅ Database setup complete! Redirecting to dashboard...<br>";
echo "<script>
    setTimeout(function() {
        window.location.href = 'partials/dashboard.php';
    }, 3000);
</script>";

echo "<br><a href='debug_votes.php'>Check Database Status</a><br>";
echo "<a href='partials/dashboard.php'>Back to Dashboard Now</a>";
?>
