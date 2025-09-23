<?php
include('actions/connect.php');

echo "<h1>🗄️ Database Structure Test</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    table { border-collapse: collapse; width: 100%; margin: 20px 0; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background-color: #f2f2f2; }
    .success { background: #d4edda; color: #155724; }
    .error { background: #f8d7da; color: #721c24; }
    .warning { background: #fff3cd; color: #856404; }
</style>";

echo "<h2>Current Table Structure:</h2>";
$describe_sql = "DESCRIBE userdata";
$result = mysqli_query($conn, $describe_sql);

if ($result) {
    echo "<table>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";

    while ($row = mysqli_fetch_assoc($result)) {
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
    echo "<div class='error'>❌ Error describing table: " . mysqli_error($conn) . "</div>";
}

echo "<h2>Sample Data Query:</h2>";
$sample_sql = "SELECT id, username, standard, verification_status FROM userdata LIMIT 5";
$sample_result = mysqli_query($conn, $sample_sql);

if ($sample_result && mysqli_num_rows($sample_result) > 0) {
    echo "<table>";
    echo "<tr><th>ID</th><th>Username</th><th>Type</th><th>Verification Status</th></tr>";

    while ($user = mysqli_fetch_assoc($sample_result)) {
        echo "<tr>";
        echo "<td>" . (isset($user['id']) ? $user['id'] : 'NULL') . "</td>";
        echo "<td>" . $user['username'] . "</td>";
        echo "<td>" . $user['standard'] . "</td>";
        echo "<td>" . ($user['verification_status'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<div class='warning'>⚠️ No sample data found</div>";
}

echo "<h2>Required Columns Check:</h2>";
$required_columns = ['id', 'username', 'mobile', 'standard', 'verification_status', 'age', 'id_proof', 'photo', 'status', 'votes'];
$missing_columns = [];

$columns_result = mysqli_query($conn, "SHOW COLUMNS FROM userdata");
if ($columns_result) {
    $existing_columns = [];
    while ($col = mysqli_fetch_assoc($columns_result)) {
        $existing_columns[] = strtolower($col['Field']); // Convert to lowercase for comparison
    }

    foreach ($required_columns as $req_col) {
        if (!in_array(strtolower($req_col), $existing_columns)) {
            $missing_columns[] = $req_col;
        }
    }
}

if (empty($missing_columns)) {
    echo "<div class='success'>✅ All required columns are present in the database</div>";
} else {
    echo "<div class='error'>❌ Missing columns: " . implode(', ', $missing_columns) . "</div>";
    echo "<p><a href='database_setup.php'>Click here to run database setup</a></p>";
}

mysqli_close($conn);
?>