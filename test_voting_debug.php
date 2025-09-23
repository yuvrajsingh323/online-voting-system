<?php
session_start();
include('actions/connect.php');

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Voting System Debug</h1>";

// Check database connection
echo "<h2>Database Connection</h2>";
if ($conn) {
    echo "<p style='color: green;'>✅ Database connected successfully</p>";
} else {
    echo "<p style='color: red;'>❌ Database connection failed: " . mysqli_connect_error() . "</p>";
    exit;
}

// Check session data
echo "<h2>Session Data</h2>";
if (isset($_SESSION['data'])) {
    echo "<p><strong>User ID:</strong> " . ($_SESSION['data']['id'] ?? 'Not set') . "</p>";
    echo "<p><strong>Username:</strong> " . ($_SESSION['data']['username'] ?? 'Not set') . "</p>";
    echo "<p><strong>Standard:</strong> " . ($_SESSION['data']['standard'] ?? 'Not set') . "</p>";
    echo "<p><strong>Status:</strong> " . ($_SESSION['data']['status'] ?? 'Not set') . "</p>";
    echo "<p><strong>Age:</strong> " . ($_SESSION['data']['age'] ?? 'Not set') . "</p>";
    echo "<p><strong>Verification Status:</strong> " . ($_SESSION['data']['verification_status'] ?? 'Not set') . "</p>";
} else {
    echo "<p style='color: red;'>❌ No session data found</p>";
}

// Check candidates
echo "<h2>Candidates</h2>";
$candidates_sql = "SELECT id, username, votes FROM userdata WHERE standard = 'candidate'";
$candidates_result = mysqli_query($conn, $candidates_sql);

if ($candidates_result && mysqli_num_rows($candidates_result) > 0) {
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Username</th><th>Votes</th></tr>";
    while ($candidate = mysqli_fetch_assoc($candidates_result)) {
        echo "<tr>";
        echo "<td>" . $candidate['id'] . "</td>";
        echo "<td>" . $candidate['username'] . "</td>";
        echo "<td>" . $candidate['votes'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: orange;'>⚠️ No candidates found</p>";
}

// Check voters
echo "<h2>Voters</h2>";
$voters_sql = "SELECT id, username, status, age, verification_status FROM userdata WHERE standard = 'voter'";
$voters_result = mysqli_query($conn, $voters_sql);

if ($voters_result && mysqli_num_rows($voters_result) > 0) {
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Username</th><th>Status</th><th>Age</th><th>Verification</th></tr>";
    while ($voter = mysqli_fetch_assoc($voters_result)) {
        echo "<tr>";
        echo "<td>" . $voter['id'] . "</td>";
        echo "<td>" . $voter['username'] . "</td>";
        echo "<td>" . ($voter['status'] == 1 ? 'Voted' : 'Not Voted') . "</td>";
        echo "<td>" . ($voter['age'] ?? 'N/A') . "</td>";
        echo "<td>" . ($voter['verification_status'] ?? 'N/A') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: orange;'>⚠️ No voters found</p>";
}

// Test voting simulation
echo "<h2>Test Voting Simulation</h2>";
if (isset($_SESSION['data']) && $_SESSION['data']['standard'] == 'voter') {
    $voter_id = $_SESSION['data']['id'];
    $candidate_id = 1; // Assuming first candidate

    echo "<p><strong>Simulating vote for voter ID:</strong> $voter_id</p>";
    echo "<p><strong>To candidate ID:</strong> $candidate_id</p>";

    // Test candidate update
    $test_update = "UPDATE userdata SET votes = votes + 1 WHERE id = '$candidate_id' AND standard = 'candidate'";
    if (mysqli_query($conn, $test_update)) {
        echo "<p style='color: green;'>✅ Candidate vote update: SUCCESS</p>";
        // Rollback the test
        mysqli_query($conn, "UPDATE userdata SET votes = votes - 1 WHERE id = '$candidate_id' AND standard = 'candidate'");
    } else {
        echo "<p style='color: red;'>❌ Candidate vote update: FAILED - " . mysqli_error($conn) . "</p>";
    }

    // Test voter status update
    $test_status = "UPDATE userdata SET status = 1 WHERE id = '$voter_id' AND standard = 'voter'";
    if (mysqli_query($conn, $test_status)) {
        echo "<p style='color: green;'>✅ Voter status update: SUCCESS</p>";
        // Rollback the test
        mysqli_query($conn, "UPDATE userdata SET status = 0 WHERE id = '$voter_id' AND standard = 'voter'");
    } else {
        echo "<p style='color: red;'>❌ Voter status update: FAILED - " . mysqli_error($conn) . "</p>";
    }
} else {
    echo "<p style='color: orange;'>⚠️ Not logged in as voter - cannot test voting</p>";
}

echo "<br><br><a href='partials/dashboard.php'>Back to Dashboard</a>";
?>