<?php
include('actions/connect.php');

echo "<h2>Candidate Profile Vote Count Test</h2>";

// Simulate candidate login (ID 3 - yuvi)
$candidate_id = 3;

// Fetch candidate data as if they just logged in
$sql = "SELECT * FROM `userdata` WHERE `id` = '$candidate_id' AND `standard` = 'candidate'";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) > 0) {
    $candidate_data = mysqli_fetch_array($result);

    echo "<h3>Candidate Data from Database:</h3>";
    echo "Name: " . $candidate_data['username'] . "<br>";
    echo "ID: " . $candidate_data['id'] . "<br>";
    echo "Votes (Database): " . $candidate_data['votes'] . "<br><br>";

    // Simulate session data (as if they just logged in)
    echo "<h3>Simulated Session Data (Before Dashboard Load):</h3>";
    echo "Name: " . $candidate_data['username'] . "<br>";
    echo "ID: " . $candidate_data['id'] . "<br>";
    echo "Votes (Session - Old): " . $candidate_data['votes'] . "<br><br>";

    // Now simulate what the dashboard does - fetch fresh data
    echo "<h3>Dashboard Fresh Data Fetch:</h3>";
    $current_user_sql = "SELECT votes FROM `userdata` WHERE `id` = '$candidate_id' AND `standard` = 'candidate'";
    $current_user_result = mysqli_query($conn, $current_user_sql);

    if ($current_user_result && mysqli_num_rows($current_user_result) > 0) {
        $current_user_data = mysqli_fetch_assoc($current_user_result);
        $fresh_votes = intval($current_user_data['votes']);

        echo "Votes (Fresh from Database): " . $fresh_votes . "<br><br>";

        echo "<h3>Result:</h3>";
        if ($fresh_votes == $candidate_data['votes']) {
            echo "✅ Candidate profile will now show correct vote count: <strong>$fresh_votes</strong><br>";
        } else {
            echo "❌ Vote count mismatch detected!<br>";
        }
    } else {
        echo "❌ Error fetching fresh candidate data<br>";
    }

} else {
    echo "❌ Candidate not found<br>";
}

echo "<br><a href='debug_votes.php'>Check Full Database Status</a>";
echo "<br><a href='partials/dashboard.php'>Go to Dashboard</a>";
?>