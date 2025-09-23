<?php
session_start();
include('actions/connect.php');

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Simple Voting Test</h1>";

// Check if user is logged in
if (!isset($_SESSION['data'])) {
    echo "<p style='color: red;'>❌ Not logged in. Please login first.</p>";
    echo "<a href='index.php'>Go to Login</a>";
    exit;
}

$user = $_SESSION['data'];
echo "<h2>User Info</h2>";
echo "<p><strong>Name:</strong> " . htmlspecialchars($user['username']) . "</p>";
echo "<p><strong>Type:</strong> " . htmlspecialchars($user['standard']) . "</p>";
echo "<p><strong>Status:</strong> " . ($user['status'] == 1 ? 'Voted' : 'Not Voted') . "</p>";
echo "<p><strong>Age:</strong> " . ($user['age'] ?? 'N/A') . "</p>";
echo "<p><strong>Verification:</strong> " . ($user['verification_status'] ?? 'N/A') . "</p>";

// Check candidates
echo "<h2>Candidates</h2>";
$candidates_sql = "SELECT id, username, votes FROM userdata WHERE standard = 'candidate' ORDER BY id";
$candidates_result = mysqli_query($conn, $candidates_sql);

if ($candidates_result && mysqli_num_rows($candidates_result) > 0) {
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Name</th><th>Votes</th><th>Action</th></tr>";
    while ($candidate = mysqli_fetch_assoc($candidates_result)) {
        echo "<tr>";
        echo "<td>" . $candidate['id'] . "</td>";
        echo "<td>" . htmlspecialchars($candidate['username']) . "</td>";
        echo "<td>" . $candidate['votes'] . "</td>";
        echo "<td>";
        if ($user['standard'] == 'voter' && $user['status'] == 0) {
            echo "<form method='POST' action='actions/voting.php' style='display:inline;'>";
            echo "<input type='hidden' name='candidate_id' value='" . $candidate['id'] . "'>";
            echo "<button type='submit' onclick='return confirm(\"Vote for " . htmlspecialchars($candidate['username']) . "?\")'>Vote</button>";
            echo "</form>";
        } elseif ($user['status'] == 1) {
            echo "<span style='color: green;'>Already Voted</span>";
        } else {
            echo "<span style='color: gray;'>Cannot Vote</span>";
        }
        echo "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No candidates found.</p>";
}

echo "<br><a href='partials/dashboard.php'>Back to Dashboard</a>";
?>