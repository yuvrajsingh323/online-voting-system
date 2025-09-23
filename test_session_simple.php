<?php
session_start();

echo "<h1>Simple Session Test</h1>";

// Test session creation
$_SESSION['test_time'] = date('Y-m-d H:i:s');
$_SESSION['test_value'] = 'session_working_' . rand(1000, 9999);

echo "<h2>Session Information</h2>";
echo "<p><strong>Session ID:</strong> " . session_id() . "</p>";
echo "<p><strong>Session Status:</strong> " . session_status() . "</p>";
echo "<p><strong>Test Time:</strong> " . $_SESSION['test_time'] . "</p>";
echo "<p><strong>Test Value:</strong> " . $_SESSION['test_value'] . "</p>";

// Check if session data persists
if (isset($_SESSION['data'])) {
    echo "<h2>User Session Data</h2>";
    echo "<p><strong>Username:</strong> " . ($_SESSION['data']['username'] ?? 'Not set') . "</p>";
    echo "<p><strong>User Type:</strong> " . ($_SESSION['data']['standard'] ?? 'Not set') . "</p>";
    echo "<p><strong>Status:</strong> " . ($_SESSION['data']['status'] ?? 'Not set') . "</p>";
    echo "<p><strong>Age:</strong> " . ($_SESSION['data']['age'] ?? 'Not set') . "</p>";
    echo "<p><strong>Verification:</strong> " . ($_SESSION['data']['verification_status'] ?? 'Not set') . "</p>";
} else {
    echo "<h2>No User Session Data</h2>";
    echo "<p style='color: orange;'>⚠️ User not logged in</p>";
}

// Test session update
echo "<h2>Session Update Test</h2>";
$old_status = $_SESSION['data']['status'] ?? 'not set';
echo "<p><strong>Original status:</strong> $old_status</p>";

// Update status
$_SESSION['data']['status'] = 1;
$_SESSION['status'] = 1;

echo "<p><strong>Updated status:</strong> " . ($_SESSION['data']['status'] ?? 'not set') . "</p>";
echo "<p style='color: green;'>✅ Session update successful</p>";

// Test form to check if session persists across requests
echo "<h2>Test Session Persistence</h2>";
echo "<form method='POST'>";
echo "<input type='hidden' name='test_request' value='1'>";
echo "<button type='submit'>Test POST Request (Check if session persists)</button>";
echo "</form>";

if (isset($_POST['test_request'])) {
    echo "<p style='color: green;'>✅ POST request received - session is working</p>";
    echo "<p><strong>Session still active:</strong> " . (session_status() == PHP_SESSION_ACTIVE ? 'Yes' : 'No') . "</p>";
    echo "<p><strong>Updated status after POST:</strong> " . ($_SESSION['data']['status'] ?? 'not set') . "</p>";
}

echo "<br><a href='index.php'>Back to Login</a>";
echo "<br><a href='partials/dashboard.php'>Go to Dashboard</a>";
?>