<?php
// Simulate candidate login session
session_start();

// Simulate candidate data (ID 3 - yuvi with 1 vote)
$_SESSION['data'] = [
    'id' => 3,
    'username' => 'yuvi',
    'mobile' => '1234567890',
    'standard' => 'candidate',
    'status' => 0,
    'votes' => 0, // This should be updated to 1 by the dashboard
    'photo' => ''
];

echo "<h2>Candidate Profile Display Test</h2>";
echo "<p>This test simulates a candidate login and checks if the vote count displays correctly.</p>";

// Include the database connection and dashboard logic
include('actions/connect.php');

// Simulate the dashboard's candidate vote count fetching logic
$data = $_SESSION['data'];

echo "<h3>Before Dashboard Processing:</h3>";
echo "Session votes: " . $data['votes'] . "<br>";

// For candidates, fetch their current vote count from database to ensure accuracy
if ($data['standard'] == 'candidate') {
    $current_user_sql = "SELECT votes FROM `userdata` WHERE `id` = '" . mysqli_real_escape_string($conn, $data['id']) . "' AND `standard` = 'candidate'";
    $current_user_result = mysqli_query($conn, $current_user_sql);

    if ($current_user_result && mysqli_num_rows($current_user_result) > 0) {
        $current_user_data = mysqli_fetch_assoc($current_user_result);
        $fresh_vote_count = intval($current_user_data['votes']);
        $data['votes'] = $fresh_vote_count; // Update local data
        $_SESSION['data']['votes'] = $fresh_vote_count; // Update session data

        echo "<h3>After Dashboard Processing:</h3>";
        echo "Database votes: " . $fresh_vote_count . "<br>";
        echo "Local data votes: " . $data['votes'] . "<br>";
        echo "Session votes: " . $_SESSION['data']['votes'] . "<br><br>";

        echo "<h3>Candidate Profile Display:</h3>";
        echo "<div style='border: 1px solid #ccc; padding: 20px; border-radius: 10px; background: #f9f9f9;'>";
        echo "<h4>Your Candidate Profile</h4>";
        echo "<div style='margin: 10px 0;'>";
        echo "<span style='background: linear-gradient(45deg, #10b981, #34d399); color: white; border-radius: 20px; padding: 0.5rem 1rem; font-weight: 600;'>Candidate</span>";
        echo "</div>";
        echo "<div style='background: white; border-radius: 15px; padding: 1.5rem; text-align: center; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1); margin: 1rem 0;'>";
        echo "<div style='font-size: 2rem; font-weight: bold; color: #4f46e5;'>" . (isset($data['votes']) ? htmlspecialchars($data['votes']) : '0') . "</div>";
        echo "<small style='color: #666;'>Total Votes</small>";
        echo "</div>";
        echo "</div>";

        if ($fresh_vote_count == 1) {
            echo "<h3 style='color: green;'>✅ SUCCESS: Candidate profile shows correct vote count!</h3>";
        } else {
            echo "<h3 style='color: red;'>❌ FAILED: Vote count is still incorrect</h3>";
        }
    } else {
        echo "<h3 style='color: red;'>❌ Error: Could not fetch candidate data from database</h3>";
    }
} else {
    echo "<h3 style='color: red;'>❌ Error: Not a candidate account</h3>";
}

echo "<br><a href='debug_votes.php'>Check Database Status</a>";
echo "<br><a href='partials/dashboard.php'>Go to Actual Dashboard</a>";
?>