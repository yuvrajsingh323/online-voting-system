<?php
// Direct database initialization
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "onlinevotingsystem_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "Database connected successfully!<br><br>";

// Add votes column
$sql1 = "ALTER TABLE userdata ADD COLUMN votes INT DEFAULT 0";
if ($conn->query($sql1) === TRUE) {
    echo "✅ Votes column added successfully<br>";
} else {
    if (strpos($conn->error, 'Duplicate column') !== false) {
        echo "✅ Votes column already exists<br>";
    } else {
        echo "❌ Error adding votes column: " . $conn->error . "<br>";
    }
}

// Initialize votes to 0
$sql2 = "UPDATE userdata SET votes = 0 WHERE standard = 'candidate'";
if ($conn->query($sql2) === TRUE) {
    echo "✅ Candidate votes initialized to 0<br>";
    echo "Rows updated: " . $conn->affected_rows . "<br>";
} else {
    echo "❌ Error initializing votes: " . $conn->error . "<br>";
}

// Show current candidates
echo "<br><h3>Current Candidates:</h3>";
$sql3 = "SELECT id, username, votes FROM userdata WHERE standard = 'candidate'";
$result = $conn->query($sql3);

if ($result->num_rows > 0) {
    echo "<table border='1'><tr><th>ID</th><th>Name</th><th>Votes</th></tr>";
    while($row = $result->fetch_assoc()) {
        echo "<tr><td>".$row["id"]."</td><td>".$row["username"]."</td><td>".$row["votes"]."</td></tr>";
    }
    echo "</table>";
} else {
    echo "No candidates found";
}

$conn->close();

echo "<br><br><a href='partials/dashboard.php'>Go to Dashboard</a>";
?>
