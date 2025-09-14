<?php
// Complete database fix for voting system
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "onlinevotingsystem_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<h2>Complete Voting System Database Fix</h2>";

// Step 1: Check current table structure
echo "<h3>Step 1: Current Table Structure</h3>";
$result = $conn->query("DESCRIBE userdata");
$has_votes = false;

if ($result) {
    echo "<table border='1'><tr><th>Field</th><th>Type</th><th>Default</th></tr>";
    while($row = $result->fetch_assoc()) {
        echo "<tr><td>".$row["Field"]."</td><td>".$row["Type"]."</td><td>".$row["Default"]."</td></tr>";
        if ($row["Field"] == "votes") {
            $has_votes = true;
        }
    }
    echo "</table>";
}

// Step 2: Add votes column if missing
echo "<h3>Step 2: Adding Votes Column</h3>";
if (!$has_votes) {
    if ($conn->query("ALTER TABLE userdata ADD COLUMN votes INT DEFAULT 0")) {
        echo "✅ Votes column added successfully<br>";
    } else {
        echo "❌ Error adding votes column: " . $conn->error . "<br>";
    }
} else {
    echo "✅ Votes column already exists<br>";
}

// Step 3: Initialize all votes to 0
echo "<h3>Step 3: Initialize Votes</h3>";
if ($conn->query("UPDATE userdata SET votes = 0")) {
    echo "✅ All votes initialized to 0<br>";
    echo "Rows affected: " . $conn->affected_rows . "<br>";
} else {
    echo "❌ Error initializing votes: " . $conn->error . "<br>";
}

// Step 4: Test vote increment
echo "<h3>Step 4: Test Vote Increment</h3>";
$test_candidate = $conn->query("SELECT id FROM userdata WHERE standard='candidate' LIMIT 1");
if ($test_candidate && $test_candidate->num_rows > 0) {
    $candidate = $test_candidate->fetch_assoc();
    $candidate_id = $candidate['id'];
    
    if ($conn->query("UPDATE userdata SET votes = votes + 1 WHERE id = $candidate_id")) {
        echo "✅ Test vote increment successful<br>";
    } else {
        echo "❌ Error in test vote: " . $conn->error . "<br>";
    }
}

// Step 5: Show final data
echo "<h3>Step 5: Final Candidate Data</h3>";
$result = $conn->query("SELECT id, username, standard, votes FROM userdata WHERE standard='candidate'");

if ($result && $result->num_rows > 0) {
    echo "<table border='1'><tr><th>ID</th><th>Username</th><th>Standard</th><th>Votes</th></tr>";
    while($row = $result->fetch_assoc()) {
        echo "<tr><td>".$row["id"]."</td><td>".$row["username"]."</td><td>".$row["standard"]."</td><td>".$row["votes"]."</td></tr>";
    }
    echo "</table>";
} else {
    echo "No candidates found or error: " . $conn->error;
}

// Step 6: Show voter data
echo "<h3>Step 6: Voter Status</h3>";
$result = $conn->query("SELECT id, username, standard, status FROM userdata WHERE standard='voter'");

if ($result && $result->num_rows > 0) {
    echo "<table border='1'><tr><th>ID</th><th>Username</th><th>Standard</th><th>Status</th></tr>";
    while($row = $result->fetch_assoc()) {
        echo "<tr><td>".$row["id"]."</td><td>".$row["username"]."</td><td>".$row["standard"]."</td><td>".$row["status"]."</td></tr>";
    }
    echo "</table>";
} else {
    echo "No voters found or error: " . $conn->error;
}

$conn->close();

echo "<br><br>";
echo "<a href='partials/dashboard.php' style='background: #28a745; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-weight: bold;'>✅ Go to Dashboard</a>";
echo "<br><br>";
echo "<a href='debug_votes.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🔍 Debug Database</a>";
?>
