<?php
include('actions/connect.php');

echo "<h3>Database Debug Information</h3>";

// Check table structure
echo "<h4>Table Structure:</h4>";
$structure_query = "DESCRIBE userdata";
$structure_result = mysqli_query($conn, $structure_query);

if ($structure_result) {
    echo "<table border='1'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while ($row = mysqli_fetch_assoc($structure_result)) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "<td>" . $row['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "Error describing table: " . mysqli_error($conn);
}

// Check candidate data
echo "<h4>Candidate Data:</h4>";
$candidates_query = "SELECT id, username, standard, votes FROM userdata WHERE standard='candidate'";
$candidates_result = mysqli_query($conn, $candidates_query);

if ($candidates_result) {
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Username</th><th>Standard</th><th>Votes</th></tr>";
    while ($row = mysqli_fetch_assoc($candidates_result)) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['username'] . "</td>";
        echo "<td>" . $row['standard'] . "</td>";
        echo "<td>" . (isset($row['votes']) ? $row['votes'] : 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "Error fetching candidates: " . mysqli_error($conn);
}

// Check voter data
echo "<h4>Voter Data:</h4>";
$voters_query = "SELECT id, username, standard, status FROM userdata WHERE standard='voter'";
$voters_result = mysqli_query($conn, $voters_query);

if ($voters_result) {
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Username</th><th>Standard</th><th>Status</th></tr>";
    while ($row = mysqli_fetch_assoc($voters_result)) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['username'] . "</td>";
        echo "<td>" . $row['standard'] . "</td>";
        echo "<td>" . $row['status'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "Error fetching voters: " . mysqli_error($conn);
}
?>
