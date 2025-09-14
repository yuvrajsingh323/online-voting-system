<?php
// Test logout functionality
session_start();

// Simulate logged in user
$_SESSION['data'] = [
    'id' => 1,
    'username' => 'testuser',
    'standard' => 'voter'
];

echo "<h2>Logout Test</h2>";
echo "<p>Testing logout functionality...</p>";

// Show session data before logout
echo "<h3>Before Logout:</h3>";
echo "Session ID: " . session_id() . "<br>";
echo "Session Data: " . (isset($_SESSION['data']) ? "Present" : "Not Present") . "<br>";
if (isset($_SESSION['data'])) {
    echo "Username: " . $_SESSION['data']['username'] . "<br>";
}

// Simulate logout process
$_SESSION = array();
session_destroy();

if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

echo "<h3>After Logout:</h3>";
echo "Session Data: " . (isset($_SESSION['data']) ? "Still Present (ERROR)" : "Cleared Successfully") . "<br>";
echo "Session Destroyed: " . (session_status() === PHP_SESSION_NONE ? "Yes" : "No") . "<br>";

echo "<h3>Redirect Test:</h3>";
echo "<p>The logout should redirect to: <strong>../index.php</strong></p>";
echo "<p><a href='partials/logout.php'>Click here to test actual logout redirect</a></p>";
echo "<p><a href='index.php'>Go to Home Page</a></p>";
?>