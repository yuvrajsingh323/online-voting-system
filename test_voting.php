<?php
include('actions/connect.php');

echo "<h2>Voting System Test</h2>";

// Test data
$candidate_id = 3; // yuvi
$voter_id = 1; // yash rajpurohit

echo "<h3>Before Voting:</h3>";
// Check current votes
$candidate_query = "SELECT username, votes FROM userdata WHERE id = $candidate_id";
$candidate_result = mysqli_query($conn, $candidate_query);
$candidate = mysqli_fetch_assoc($candidate_result);

$voter_query = "SELECT username, status FROM userdata WHERE id = $voter_id";
$voter_result = mysqli_query($conn, $voter_query);
$voter = mysqli_fetch_assoc($voter_result);

echo "Candidate: " . $candidate['username'] . " - Votes: " . $candidate['votes'] . "<br>";
echo "Voter: " . $voter['username'] . " - Status: " . $voter['status'] . "<br><br>";

// Simulate voting
echo "<h3>Simulating Vote...</h3>";

// Begin transaction
mysqli_autocommit($conn, FALSE);

try {
    // Update candidate votes atomically
    $update_votes = "UPDATE `userdata` SET `votes` = `votes` + 1 WHERE `id` = '$candidate_id' AND `standard` = 'candidate'";
    $result1 = mysqli_query($conn, $update_votes);

    if (!$result1) {
        throw new Exception("Failed to update candidate votes: " . mysqli_error($conn));
    }

    if (mysqli_affected_rows($conn) == 0) {
        throw new Exception("No candidate found with ID: $candidate_id");
    }

    // Update voter status
    $update_status = "UPDATE `userdata` SET `status` = 1 WHERE `id` = '$voter_id' AND `standard` = 'voter'";
    $result2 = mysqli_query($conn, $update_status);

    if (!$result2) {
        throw new Exception("Failed to update voter status: " . mysqli_error($conn));
    }

    // Get new vote count
    $get_new_votes = "SELECT votes FROM `userdata` WHERE `id` = '$candidate_id' AND `standard` = 'candidate'";
    $votes_result = mysqli_query($conn, $get_new_votes);

    if (!$votes_result) {
        throw new Exception("Failed to retrieve updated vote count: " . mysqli_error($conn));
    }

    $votes_row = mysqli_fetch_assoc($votes_result);
    $new_vote_count = $votes_row['votes'];

    // Commit transaction
    mysqli_commit($conn);

    echo "✅ Vote successful! New vote count: $new_vote_count<br><br>";

} catch (Exception $e) {
    // Rollback transaction
    mysqli_rollback($conn);
    echo "❌ Vote failed: " . $e->getMessage() . "<br><br>";
}

// Restore autocommit
mysqli_autocommit($conn, TRUE);

echo "<h3>After Voting:</h3>";
// Check updated data
$candidate_result2 = mysqli_query($conn, $candidate_query);
$candidate2 = mysqli_fetch_assoc($candidate_result2);

$voter_result2 = mysqli_query($conn, $voter_query);
$voter2 = mysqli_fetch_assoc($voter_result2);

echo "Candidate: " . $candidate2['username'] . " - Votes: " . $candidate2['votes'] . "<br>";
echo "Voter: " . $voter2['username'] . " - Status: " . $voter2['status'] . "<br><br>";

echo "<h3>Test Results:</h3>";
if ($candidate2['votes'] == $candidate['votes'] + 1 && $voter2['status'] == 1) {
    echo "✅ All tests passed! Voting mechanism is working correctly.<br>";
} else {
    echo "❌ Test failed! Something is wrong with the voting mechanism.<br>";
}

echo "<br><a href='debug_votes.php'>Check Full Database Status</a>";
?>