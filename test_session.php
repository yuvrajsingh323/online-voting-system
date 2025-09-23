<?php
session_start();

echo "<h1>Session Test</h1>";

// Check session status
echo "<h2>Session Status</h2>";
echo "<p><strong>Session ID:</strong> " . session_id() . "</p>";
echo "<p><strong>Session Status:</strong> " . session_status() . "</p>";
echo "<p><strong>Session Active:</strong> " . (session_status() == PHP_SESSION_ACTIVE ? 'Yes' : 'No') . "</p>";

// Check session data
echo "<h2>Session Data</h2>";
if (isset($_SESSION['data'])) {
    echo "<pre>";
    print_r($_SESSION['data']);
    echo "</pre>";
} else {
    echo "<p style='color: red;'>❌ No session data found</p>";
}

// Check specific session variables
echo "<h2>Specific Variables</h2>";
$variables = ['id', 'status', 'data'];
foreach ($variables as $var) {
    if (isset($_SESSION[$var])) {
        echo "<p><strong>$_SESSION['$var']:</strong> " . (is_array($_SESSION[$var]) ? 'Array' : $_SESSION[$var]) . "</p>";
    } else {
        echo "<p style='color: orange;'>⚠️ $_SESSION['$var'] not set</p>";
    }
}

// Test session update
echo "<h2>Session Update Test</h2>";
if (isset($_SESSION['data'])) {
    $original_status = $_SESSION['data']['status'] ?? 'not set';
    echo "<p><strong>Original status:</strong> $original_status</p>";

    // Test update
    $_SESSION['data']['status'] = 1;
    $_SESSION['status'] = 1;

    echo "<p><strong>Updated status:</strong> " . ($_SESSION['data']['status'] ?? 'not set') . "</p>";
    echo "<p style='color: green;'>✅ Session update successful</p>";
} else {
    echo "<p style='color: red;'>❌ Cannot test session update - no session data</p>";
}

// Test form to simulate voting
echo "<h2>Test Voting Simulation</h2>";
if (isset($_SESSION['data']) && $_SESSION['data']['standard'] == 'voter') {
    echo "<form method='POST' action='actions/voting.php'>";
    echo "<input type='hidden' name='candidate_id' value='1'>"; // Assuming candidate ID 1
    echo "<button type='submit'>Test Vote (Candidate ID: 1)</button>";
    echo "</form>";
} else {
    echo "<p style='color: orange;'>⚠️ Not logged in as voter</p>";
}

echo "<br><a href='index.php'>Back to Login</a>";
echo "<br><a href='partials/dashboard.php'>Go to Dashboard</a>";
?>