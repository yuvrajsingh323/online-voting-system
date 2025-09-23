<?php
session_start();
include('actions/connect.php');

echo "<h1>Complete Voting System Test</h1>";
echo "<p><strong>Test Time:</strong> " . date('Y-m-d H:i:s') . "</p>";

// Step 1: Check PHP and session
echo "<h2>Step 1: PHP & Session Check</h2>";
echo "<p><strong>PHP Version:</strong> " . phpversion() . "</p>";
echo "<p><strong>Session ID:</strong> " . session_id() . "</p>";
echo "<p><strong>Session Status:</strong> " . (session_status() == PHP_SESSION_ACTIVE ? 'Active' : 'Inactive') . "</p>";

// Step 2: Check database connection
echo "<h2>Step 2: Database Connection</h2>";
if ($conn) {
    echo "<p style='color: green;'>✅ Database connected successfully</p>";
} else {
    echo "<p style='color: red;'>❌ Database connection failed: " . mysqli_connect_error() . "</p>";
    exit;
}

// Step 3: Check user session
echo "<h2>Step 3: User Session</h2>";
if (isset($_SESSION['data'])) {
    $user = $_SESSION['data'];
    echo "<p><strong>Username:</strong> " . htmlspecialchars($user['username']) . "</p>";
    echo "<p><strong>Type:</strong> " . htmlspecialchars($user['standard']) . "</p>";
    echo "<p><strong>Status:</strong> " . ($user['status'] == 1 ? 'Voted' : 'Not Voted') . "</p>";
    echo "<p><strong>Age:</strong> " . ($user['age'] ?? 'Not set') . "</p>";
    echo "<p><strong>Verification:</strong> " . ($user['verification_status'] ?? 'Not set') . "</p>";
    echo "<p style='color: green;'>✅ User session is valid</p>";
} else {
    echo "<p style='color: red;'>❌ No user session found</p>";
    echo "<a href='index.php'>Login First</a>";
    exit;
}

// Step 4: Check database data
echo "<h2>Step 4: Database Data Check</h2>";
$user_id = $_SESSION['data']['id'];
$db_check_sql = "SELECT username, standard, status, age, verification_status FROM userdata WHERE id = '$user_id'";
$db_check_result = mysqli_query($conn, $db_check_sql);

if ($db_check_result && mysqli_num_rows($db_check_result) > 0) {
    $db_user = mysqli_fetch_assoc($db_check_result);
    echo "<p><strong>DB Username:</strong> " . htmlspecialchars($db_user['username']) . "</p>";
    echo "<p><strong>DB Type:</strong> " . htmlspecialchars($db_user['standard']) . "</p>";
    echo "<p><strong>DB Status:</strong> " . ($db_user['status'] == 1 ? 'Voted' : 'Not Voted') . "</p>";
    echo "<p><strong>DB Age:</strong> " . ($db_user['age'] ?? 'Not set') . "</p>";
    echo "<p><strong>DB Verification:</strong> " . ($db_user['verification_status'] ?? 'Not set') . "</p>";
    echo "<p style='color: green;'>✅ Database data retrieved successfully</p>";
} else {
    echo "<p style='color: red;'>❌ Could not retrieve user data from database</p>";
}

// Step 5: Check candidates
echo "<h2>Step 5: Candidates Check</h2>";
$candidates_sql = "SELECT id, username, votes FROM userdata WHERE standard = 'candidate' LIMIT 3";
$candidates_result = mysqli_query($conn, $candidates_sql);

if ($candidates_result && mysqli_num_rows($candidates_result) > 0) {
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr><th>ID</th><th>Name</th><th>Votes</th></tr>";
    while ($candidate = mysqli_fetch_assoc($candidates_result)) {
        echo "<tr>";
        echo "<td>" . $candidate['id'] . "</td>";
        echo "<td>" . htmlspecialchars($candidate['username']) . "</td>";
        echo "<td>" . $candidate['votes'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "<p style='color: green;'>✅ Candidates found in database</p>";
} else {
    echo "<p style='color: red;'>❌ No candidates found in database</p>";
}

// Step 6: Voting eligibility check
echo "<h2>Step 6: Voting Eligibility Check</h2>";
$user = $_SESSION['data'];
$eligible = true;
$issues = [];

if ($user['standard'] != 'voter') {
    $eligible = false;
    $issues[] = "User is not a voter";
}

if ($user['status'] == 1) {
    $eligible = false;
    $issues[] = "User has already voted";
}

if (!isset($user['age']) || $user['age'] === NULL || $user['age'] < 18) {
    $eligible = false;
    $issues[] = "User age is invalid or under 18";
}

if (!isset($user['verification_status']) || $user['verification_status'] != 'verified') {
    $eligible = false;
    $issues[] = "User is not verified";
}

if ($eligible) {
    echo "<p style='color: green;'>✅ User is eligible to vote</p>";
} else {
    echo "<p style='color: red;'>❌ User is NOT eligible to vote:</p>";
    echo "<ul>";
    foreach ($issues as $issue) {
        echo "<li>$issue</li>";
    }
    echo "</ul>";
}

// Step 7: Test voting form
echo "<h2>Step 7: Test Voting</h2>";
if ($eligible && mysqli_num_rows($candidates_result) > 0) {
    mysqli_data_seek($candidates_result, 0); // Reset result pointer
    $test_candidate = mysqli_fetch_assoc($candidates_result);

    echo "<p><strong>Test Candidate:</strong> " . htmlspecialchars($test_candidate['username']) . " (ID: " . $test_candidate['id'] . ")</p>";

    echo "<form method='POST' action='actions/voting.php' style='border: 2px solid #007bff; padding: 15px; margin: 10px 0;'>";
    echo "<input type='hidden' name='candidate_id' value='" . $test_candidate['id'] . "'>";
    echo "<p><strong>This form will submit directly to voting.php</strong></p>";
    echo "<button type='submit' onclick='return confirm(\"Test vote for " . htmlspecialchars($test_candidate['username']) . "?\")' style='background: #28a745; color: white; border: none; padding: 10px 20px; cursor: pointer;'>🗳️ TEST VOTE</button>";
    echo "</form>";
} else {
    echo "<p style='color: orange;'>⚠️ Cannot test voting - user not eligible or no candidates</p>";
}

echo "<h2>Step 8: Debug Information</h2>";
echo "<details>";
echo "<summary>Click to view full session data</summary>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";
echo "</details>";

echo "<br><br><a href='partials/dashboard.php'>Back to Dashboard</a>";
echo "<br><a href='index.php'>Back to Login</a>";
?>