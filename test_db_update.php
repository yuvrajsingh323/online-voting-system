<?php
include('actions/connect.php');

echo "<h1>Database Update Test</h1>";

// Test database connection
if (!$conn) {
    die("<p style='color: red;'>❌ Database connection failed: " . mysqli_connect_error() . "</p>");
}
echo "<p style='color: green;'>✅ Database connected successfully</p>";

// Get test data
$voter_query = "SELECT id, username, status FROM userdata WHERE standard = 'voter' AND status = 0 LIMIT 1";
$candidate_query = "SELECT id, username, votes FROM userdata WHERE standard = 'candidate' LIMIT 1";

$voter_result = mysqli_query($conn, $voter_query);
$candidate_result = mysqli_query($conn, $candidate_query);

if (mysqli_num_rows($voter_result) > 0 && mysqli_num_rows($candidate_result) > 0) {
    $voter = mysqli_fetch_assoc($voter_result);
    $candidate = mysqli_fetch_assoc($candidate_result);

    echo "<h2>Test Data</h2>";
    echo "<p><strong>Voter:</strong> " . htmlspecialchars($voter['username']) . " (ID: " . $voter['id'] . ", Status: " . $voter['status'] . ")</p>";
    echo "<p><strong>Candidate:</strong> " . htmlspecialchars($candidate['username']) . " (ID: " . $candidate['id'] . ", Votes: " . $candidate['votes'] . ")</p>";

    // Test the exact queries used in voting.php
    echo "<h2>Testing Database Updates</h2>";

    // Test 1: Update candidate votes
    $test1_sql = "UPDATE userdata SET votes = votes + 1 WHERE id = '" . $candidate['id'] . "' AND standard = 'candidate'";
    echo "<p><strong>Test 1 - Update candidate votes:</strong></p>";
    echo "<code>$test1_sql</code><br>";

    if (mysqli_query($conn, $test1_sql)) {
        $affected = mysqli_affected_rows($conn);
        echo "<p style='color: green;'>✅ SUCCESS: $affected row(s) affected</p>";

        // Get new vote count
        $check_votes = mysqli_query($conn, "SELECT votes FROM userdata WHERE id = '" . $candidate['id'] . "'");
        $new_votes = mysqli_fetch_assoc($check_votes)['votes'];
        echo "<p><strong>New vote count:</strong> $new_votes</p>";

        // Rollback the test
        mysqli_query($conn, "UPDATE userdata SET votes = votes - 1 WHERE id = '" . $candidate['id'] . "' AND standard = 'candidate'");
        echo "<p style='color: blue;'>🔄 Test vote rolled back</p>";
    } else {
        echo "<p style='color: red;'>❌ FAILED: " . mysqli_error($conn) . "</p>";
    }

    // Test 2: Update voter status
    $test2_sql = "UPDATE userdata SET status = 1 WHERE id = '" . $voter['id'] . "' AND standard = 'voter'";
    echo "<p><strong>Test 2 - Update voter status:</strong></p>";
    echo "<code>$test2_sql</code><br>";

    if (mysqli_query($conn, $test2_sql)) {
        $affected = mysqli_affected_rows($conn);
        echo "<p style='color: green;'>✅ SUCCESS: $affected row(s) affected</p>";

        // Check new status
        $check_status = mysqli_query($conn, "SELECT status FROM userdata WHERE id = '" . $voter['id'] . "'");
        $new_status = mysqli_fetch_assoc($check_status)['status'];
        echo "<p><strong>New status:</strong> $new_status</p>";

        // Rollback the test
        mysqli_query($conn, "UPDATE userdata SET status = 0 WHERE id = '" . $voter['id'] . "' AND standard = 'voter'");
        echo "<p style='color: blue;'>🔄 Test status rolled back</p>";
    } else {
        echo "<p style='color: red;'>❌ FAILED: " . mysqli_error($conn) . "</p>";
    }

    // Test 3: Transaction test
    echo "<h2>Testing Transaction</h2>";
    mysqli_autocommit($conn, FALSE);

    try {
        // Update candidate
        mysqli_query($conn, "UPDATE userdata SET votes = votes + 1 WHERE id = '" . $candidate['id'] . "' AND standard = 'candidate'");

        // Update voter
        mysqli_query($conn, "UPDATE userdata SET status = 1 WHERE id = '" . $voter['id'] . "' AND standard = 'voter'");

        // Commit
        if (mysqli_commit($conn)) {
            echo "<p style='color: green;'>✅ Transaction committed successfully</p>";

            // Check results
            $final_votes = mysqli_fetch_assoc(mysqli_query($conn, "SELECT votes FROM userdata WHERE id = '" . $candidate['id'] . "'"))['votes'];
            $final_status = mysqli_fetch_assoc(mysqli_query($conn, "SELECT status FROM userdata WHERE id = '" . $voter['id'] . "'"))['status'];

            echo "<p><strong>Final votes:</strong> $final_votes</p>";
            echo "<p><strong>Final status:</strong> $final_status</p>";

            // Rollback changes
            mysqli_query($conn, "UPDATE userdata SET votes = votes - 1 WHERE id = '" . $candidate['id'] . "' AND standard = 'candidate'");
            mysqli_query($conn, "UPDATE userdata SET status = 0 WHERE id = '" . $voter['id'] . "' AND standard = 'voter'");
            echo "<p style='color: blue;'>🔄 Transaction changes rolled back</p>";
        } else {
            echo "<p style='color: red;'>❌ Transaction commit failed</p>";
            mysqli_rollback($conn);
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Transaction error: " . $e->getMessage() . "</p>";
        mysqli_rollback($conn);
    }

    mysqli_autocommit($conn, TRUE);

} else {
    echo "<p style='color: orange;'>⚠️ Not enough test data. Need at least 1 voter (status=0) and 1 candidate.</p>";
}

echo "<br><a href='index.php'>Back to Login</a>";
?>