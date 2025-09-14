<?php
// Quick verification of voting system status
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "onlinevotingsystem_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<h2>Voting System Status Check</h2>";

// Check votes column exists
$structure = $conn->query("DESCRIBE userdata");
$has_votes = false;
if ($structure) {
    while($row = $structure->fetch_assoc()) {
        if ($row["Field"] == "votes") {
            $has_votes = true;
            echo "✅ Votes column exists: " . $row["Type"] . " (Default: " . $row["Default"] . ")<br>";
            break;
        }
    }
}

if (!$has_votes) {
    echo "❌ Votes column missing<br>";
} else {
    // Show current vote counts
    echo "<h3>Current Vote Counts:</h3>";
    $candidates = $conn->query("SELECT id, username, votes FROM userdata WHERE standard='candidate'");
    if ($candidates && $candidates->num_rows > 0) {
        echo "<table border='1'><tr><th>ID</th><th>Candidate</th><th>Votes</th></tr>";
        while($row = $candidates->fetch_assoc()) {
            echo "<tr><td>".$row["id"]."</td><td>".$row["username"]."</td><td>".$row["votes"]."</td></tr>";
        }
        echo "</table>";
    }
    
    // Show voter status
    echo "<h3>Voter Status:</h3>";
    $voters = $conn->query("SELECT id, username, status FROM userdata WHERE standard='voter'");
    if ($voters && $voters->num_rows > 0) {
        echo "<table border='1'><tr><th>ID</th><th>Voter</th><th>Status</th></tr>";
        while($row = $voters->fetch_assoc()) {
            $status = $row["status"] == 1 ? "Voted" : "Not Voted";
            echo "<tr><td>".$row["id"]."</td><td>".$row["username"]."</td><td>".$status."</td></tr>";
        }
        echo "</table>";
    }
}

$conn->close();
echo "<br><a href='partials/dashboard.php'>Go to Dashboard</a>";
?>
