<?php
echo "<h2>Candidate Profile Link Test</h2>";
echo "<p>Testing the candidate profile link functionality...</p>";

// Check if candidate_profile.php exists
if (file_exists('candidate_profile.php')) {
    echo "<h3>✅ File Check:</h3>";
    echo "candidate_profile.php exists: <strong>Yes</strong><br>";
} else {
    echo "<h3>❌ File Check:</h3>";
    echo "candidate_profile.php exists: <strong>No</strong><br>";
}

// Test database connection
include('actions/connect.php');

if ($conn) {
    echo "Database connection: <strong>Success</strong><br><br>";

    // Get a candidate ID for testing
    $sql = "SELECT id, username FROM userdata WHERE standard = 'candidate' LIMIT 1";
    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        $candidate = mysqli_fetch_assoc($result);
        $test_id = $candidate['id'];
        $test_name = $candidate['username'];

        echo "<h3>Test Candidate Profile Link:</h3>";
        echo "Test Candidate: <strong>$test_name</strong> (ID: $test_id)<br><br>";

        echo "<h3>Generated Link:</h3>";
        $profile_link = "candidate_profile.php?id=$test_id";
        echo "<code>$profile_link</code><br><br>";

        echo "<h3>Link Preview:</h3>";
        echo "<a href='$profile_link' target='_blank' class='btn btn-primary'>";
        echo "<i class='fas fa-eye me-2'></i>View $test_name's Profile</a><br><br>";

        echo "<h3>Expected Behavior:</h3>";
        echo "<ul>";
        echo "<li>✅ Clicking the link should open the candidate's profile page</li>";
        echo "<li>✅ Profile should show candidate's name, photo, votes, and contact info</li>";
        echo "<li>✅ For voters: Should show vote button if they haven't voted</li>";
        echo "<li>✅ For candidates: Should show profile view only</li>";
        echo "<li>✅ Back button should return to dashboard</li>";
        echo "</ul>";

    } else {
        echo "<h3>❌ No candidates found in database</h3>";
        echo "Please ensure there are candidates registered in the system.<br>";
    }

} else {
    echo "Database connection: <strong>Failed</strong><br>";
}

echo "<br><a href='partials/dashboard.php'>Go to Dashboard</a>";
echo "<br><a href='debug_votes.php'>Check Database Status</a>";
?>