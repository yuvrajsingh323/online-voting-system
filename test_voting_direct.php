<?php
session_start();
include('actions/connect.php');

echo "<h1>Direct Voting Test</h1>";

// Check if user is logged in
if (!isset($_SESSION['data'])) {
    echo "<p style='color: red;'>❌ Not logged in. Please login first.</p>";
    echo "<a href='index.php'>Go to Login</a>";
    exit;
}

$user = $_SESSION['data'];
echo "<h2>Current User</h2>";
echo "<p><strong>Name:</strong> " . htmlspecialchars($user['username']) . "</p>";
echo "<p><strong>Type:</strong> " . htmlspecialchars($user['standard']) . "</p>";
echo "<p><strong>Status:</strong> " . ($user['status'] == 1 ? 'Voted' : 'Not Voted') . "</p>";
echo "<p><strong>Age:</strong> " . ($user['age'] ?? 'N/A') . "</p>";
echo "<p><strong>Verification:</strong> " . ($user['verification_status'] ?? 'N/A') . "</p>";

// Get candidates
$candidates_sql = "SELECT id, username, votes FROM userdata WHERE standard = 'candidate' LIMIT 3";
$candidates_result = mysqli_query($conn, $candidates_sql);

if ($candidates_result && mysqli_num_rows($candidates_result) > 0) {
    echo "<h2>Test Voting (Direct to voting.php)</h2>";
    while ($candidate = mysqli_fetch_assoc($candidates_result)) {
        echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 10px;'>";
        echo "<p><strong>Candidate:</strong> " . htmlspecialchars($candidate['username']) . " (ID: " . $candidate['id'] . ", Votes: " . $candidate['votes'] . ")</p>";

        if ($user['standard'] == 'voter' && $user['status'] == 0) {
            echo "<form method='POST' action='actions/voting.php' style='display: inline;'>";
            echo "<input type='hidden' name='candidate_id' value='" . $candidate['id'] . "'>";
            echo "<button type='submit' onclick='return confirm(\"Vote for " . htmlspecialchars($candidate['username']) . "?\")' style='background: #007bff; color: white; border: none; padding: 5px 10px; cursor: pointer;'>Vote for " . htmlspecialchars($candidate['username']) . "</button>";
            echo "</form>";
        } elseif ($user['status'] == 1) {
            echo "<span style='color: green;'>Already Voted</span>";
        } else {
            echo "<span style='color: gray;'>Cannot Vote</span>";
        }
        echo "</div>";
    }
} else {
    echo "<p>No candidates found.</p>";
}

echo "<br><a href='partials/dashboard.php'>Back to Dashboard</a>";
?>