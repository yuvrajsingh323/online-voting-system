<?php
include('actions/connect.php');

echo "<h1>Database Structure Check</h1>";

// Check connection
if (!$conn) {
    die("<p style='color: red;'>❌ Database connection failed: " . mysqli_connect_error() . "</p>");
}
echo "<p style='color: green;'>✅ Database connected successfully</p>";

// Check if userdata table exists
$table_check = mysqli_query($conn, "SHOW TABLES LIKE 'userdata'");
if (mysqli_num_rows($table_check) == 0) {
    die("<p style='color: red;'>❌ userdata table does not exist</p>");
}
echo "<p style='color: green;'>✅ userdata table exists</p>";

// Check table structure
echo "<h2>Table Structure</h2>";
$structure_sql = "DESCRIBE userdata";
$structure_result = mysqli_query($conn, $structure_sql);

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
    echo "<p style='color: red;'>❌ Could not get table structure: " . mysqli_error($conn) . "</p>";
}

// Check data
echo "<h2>Sample Data</h2>";
$data_sql = "SELECT id, username, standard, status, votes, age, verification_status FROM userdata LIMIT 10";
$data_result = mysqli_query($conn, $data_sql);

if ($data_result && mysqli_num_rows($data_result) > 0) {
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Username</th><th>Type</th><th>Status</th><th>Votes</th><th>Age</th><th>Verification</th></tr>";
    while ($row = mysqli_fetch_assoc($data_result)) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . htmlspecialchars($row['username']) . "</td>";
        echo "<td>" . $row['standard'] . "</td>";
        echo "<td>" . ($row['status'] == 1 ? 'Voted' : 'Not Voted') . "</td>";
        echo "<td>" . ($row['votes'] ?? 0) . "</td>";
        echo "<td>" . ($row['age'] ?? 'N/A') . "</td>";
        echo "<td>" . ($row['verification_status'] ?? 'N/A') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No data found in table</p>";
}

// Test voting simulation
echo "<h2>Voting Test</h2>";
$test_voter = mysqli_query($conn, "SELECT id FROM userdata WHERE standard = 'voter' AND status = 0 LIMIT 1");
$test_candidate = mysqli_query($conn, "SELECT id FROM userdata WHERE standard = 'candidate' LIMIT 1");

if (mysqli_num_rows($test_voter) > 0 && mysqli_num_rows($test_candidate) > 0) {
    $voter = mysqli_fetch_assoc($test_voter);
    $candidate = mysqli_fetch_assoc($test_candidate);

    echo "<p><strong>Test Voter ID:</strong> " . $voter['id'] . "</p>";
    echo "<p><strong>Test Candidate ID:</strong> " . $candidate['id'] . "</p>";

    // Test update queries
    $test1 = "UPDATE userdata SET votes = votes + 1 WHERE id = '" . $candidate['id'] . "' AND standard = 'candidate'";
    $test2 = "UPDATE userdata SET status = 1 WHERE id = '" . $voter['id'] . "' AND standard = 'voter'";

    echo "<p><strong>Test Query 1:</strong> " . $test1 . "</p>";
    echo "<p><strong>Test Query 2:</strong> " . $test2 . "</p>";

    if (mysqli_query($conn, $test1)) {
        echo "<p style='color: green;'>✅ Candidate vote update test: SUCCESS</p>";
        // Rollback
        mysqli_query($conn, "UPDATE userdata SET votes = votes - 1 WHERE id = '" . $candidate['id'] . "' AND standard = 'candidate'");
    } else {
        echo "<p style='color: red;'>❌ Candidate vote update test: FAILED - " . mysqli_error($conn) . "</p>";
    }

    if (mysqli_query($conn, $test2)) {
        echo "<p style='color: green;'>✅ Voter status update test: SUCCESS</p>";
        // Rollback
        mysqli_query($conn, "UPDATE userdata SET status = 0 WHERE id = '" . $voter['id'] . "' AND standard = 'voter'");
    } else {
        echo "<p style='color: red;'>❌ Voter status update test: FAILED - " . mysqli_error($conn) . "</p>";
    }
} else {
    echo "<p style='color: orange;'>⚠️ Not enough test data (need at least 1 voter and 1 candidate)</p>";
}

echo "<br><a href='index.php'>Back to Login</a>";
?>