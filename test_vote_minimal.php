<?php
// Very simple test to check if voting.php is working
echo "<h1>Minimal Voting Test</h1>";
echo "<p>If you can see this, PHP is working.</p>";

// Check if this is a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<h2>POST Data Received:</h2>";
    echo "<pre>";
    print_r($_POST);
    echo "</pre>";

    if (isset($_POST['candidate_id'])) {
        echo "<p style='color: green;'>✅ Candidate ID received: " . $_POST['candidate_id'] . "</p>";
    } else {
        echo "<p style='color: red;'>❌ No candidate_id in POST data</p>";
    }
} else {
    echo "<h2>Test Form</h2>";
    echo "<form method='POST'>";
    echo "<input type='hidden' name='candidate_id' value='1'>";
    echo "<button type='submit'>Test POST to this page</button>";
    echo "</form>";
}

echo "<br><a href='partials/dashboard.php'>Back to Dashboard</a>";
?>