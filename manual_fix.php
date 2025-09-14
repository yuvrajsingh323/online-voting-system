<?php
include('actions/connect.php');

echo "<h2>Manual Database Fix</h2>";

// Step 1: Add votes column
echo "<h3>Step 1: Adding votes column</h3>";
$add_column = "ALTER TABLE userdata ADD COLUMN votes INT DEFAULT 0";
$result1 = mysqli_query($conn, $add_column);

if ($result1) {
    echo "✅ Votes column added successfully<br>";
} else {
    $error = mysqli_error($conn);
    if (strpos($error, 'Duplicate column') !== false) {
        echo "✅ Votes column already exists<br>";
    } else {
        echo "❌ Error: " . $error . "<br>";
    }
}

// Step 2: Initialize votes
echo "<h3>Step 2: Initializing candidate votes</h3>";
$init_votes = "UPDATE userdata SET votes = 0 WHERE standard = 'candidate'";
$result2 = mysqli_query($conn, $init_votes);

if ($result2) {
    echo "✅ Candidate votes initialized<br>";
    echo "Rows affected: " . mysqli_affected_rows($conn) . "<br>";
} else {
    echo "❌ Error initializing votes: " . mysqli_error($conn) . "<br>";
}

// Step 3: Test a vote
echo "<h3>Step 3: Testing vote increment</h3>";
$test_vote = "UPDATE userdata SET votes = votes + 1 WHERE standard = 'candidate' LIMIT 1";
$result3 = mysqli_query($conn, $test_vote);

if ($result3) {
    echo "✅ Test vote successful<br>";
} else {
    echo "❌ Error testing vote: " . mysqli_error($conn) . "<br>";
}

// Step 4: Show current data
echo "<h3>Step 4: Current candidate data</h3>";
$show_data = "SELECT id, username, standard, votes FROM userdata WHERE standard = 'candidate'";
$result4 = mysqli_query($conn, $show_data);

if ($result4) {
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Username</th><th>Standard</th><th>Votes</th></tr>";
    while ($row = mysqli_fetch_assoc($result4)) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['username'] . "</td>";
        echo "<td>" . $row['standard'] . "</td>";
        echo "<td>" . $row['votes'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "❌ Error showing data: " . mysqli_error($conn) . "<br>";
}

echo "<br><br><a href='partials/dashboard.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Dashboard</a>";
?>
