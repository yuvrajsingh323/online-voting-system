<?php
session_start();
echo "<h1>Session Debug</h1>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

if (isset($_SESSION['data'])) {
    echo "<h2>Session Data Details:</h2>";
    echo "ID: " . (isset($_SESSION['data']['Id']) ? $_SESSION['data']['Id'] : 'NOT SET') . "<br>";
    echo "id: " . (isset($_SESSION['data']['id']) ? $_SESSION['data']['id'] : 'NOT SET') . "<br>";
    echo "Username: " . (isset($_SESSION['data']['username']) ? $_SESSION['data']['username'] : 'NOT SET') . "<br>";
    echo "Standard: " . (isset($_SESSION['data']['standard']) ? $_SESSION['data']['standard'] : 'NOT SET') . "<br>";
    echo "Status: " . (isset($_SESSION['data']['status']) ? $_SESSION['data']['status'] : 'NOT SET') . "<br>";
}
?>