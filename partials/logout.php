<?php
// Start session to ensure we can destroy it
session_start();

// Clear all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Clear the session cookie if it exists
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Redirect to home page (index.php)
header("Location: ../index.php");
exit();
?>