<?php
// Auto-fix database columns
echo "<h1>🔧 Auto-Fix Database Columns</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .success { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
    .warning { color: orange; font-weight: bold; }
    .info { color: blue; }
    table { border-collapse: collapse; width: 100%; margin: 20px 0; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background-color: #f2f2f2; }
</style>";

try {
    include('actions/connect.php');

    echo "<h2>1. Testing Database Connection</h2>";
    if ($conn->connect_error) {
        echo "<span class='error'>❌ Connection Failed: " . $conn->connect_error . "</span><br>";
        exit;
    } else {
        echo "<span class='success'>✅ Database Connected Successfully!</span><br>";
    }

    echo "<h2>2. Checking Current Table Structure</h2>";
    $result = mysqli_query($conn, "DESCRIBE userdata");
    $existing_columns = [];

    echo "<table>";
    echo "<tr><th>Column Name</th><th>Type</th><th>Null</th><th>Default</th></tr>";

    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "</tr>";
        $existing_columns[] = $row['Field'];
    }
    echo "</table>";

    $required_columns = ['age', 'id_proof', 'verification_status', 'date_of_birth'];
    $missing_columns = [];

    echo "<h3>Column Status:</h3>";
    foreach ($required_columns as $col) {
        if (in_array($col, $existing_columns)) {
            echo "<span class='success'>✅ $col column exists</span><br>";
        } else {
            echo "<span class='error'>❌ $col column missing</span><br>";
            $missing_columns[] = $col;
        }
    }

    if (!empty($missing_columns)) {
        echo "<h2>3. Adding Missing Columns</h2>";

        $alter_queries = [
            "ALTER TABLE userdata ADD COLUMN IF NOT EXISTS age INT NULL AFTER photo",
            "ALTER TABLE userdata ADD COLUMN IF NOT EXISTS id_proof VARCHAR(255) NULL AFTER age",
            "ALTER TABLE userdata ADD COLUMN IF NOT EXISTS verification_status ENUM('pending', 'verified', 'rejected') DEFAULT 'pending' AFTER id_proof",
            "ALTER TABLE userdata ADD COLUMN IF NOT EXISTS date_of_birth DATE NULL AFTER verification_status"
        ];

        foreach ($alter_queries as $query) {
            echo "Executing: <code>$query</code><br>";
            if (mysqli_query($conn, $query)) {
                echo "<span class='success'>✅ Column added successfully</span><br>";
            } else {
                echo "<span class='error'>❌ Failed: " . mysqli_error($conn) . "</span><br>";
            }
        }

        echo "<h2>4. Creating Indexes</h2>";
        $index_queries = [
            "CREATE INDEX IF NOT EXISTS idx_verification_status ON userdata (verification_status)",
            "CREATE INDEX IF NOT EXISTS idx_age ON userdata (age)"
        ];

        foreach ($index_queries as $query) {
            echo "Executing: <code>$query</code><br>";
            if (mysqli_query($conn, $query)) {
                echo "<span class='success'>✅ Index created successfully</span><br>";
            } else {
                echo "<span class='warning'>⚠️ Index creation failed (may already exist): " . mysqli_error($conn) . "</span><br>";
            }
        }

        echo "<h2>5. Updating Existing Records</h2>";
        $update_queries = [
            "UPDATE userdata SET verification_status = 'verified' WHERE standard IN ('candidate', 'admin') AND verification_status IS NULL",
            "UPDATE userdata SET verification_status = 'pending' WHERE standard = 'voter' AND verification_status IS NULL"
        ];

        foreach ($update_queries as $query) {
            echo "Executing: <code>$query</code><br>";
            if (mysqli_query($conn, $query)) {
                $affected = mysqli_affected_rows($conn);
                echo "<span class='success'>✅ Updated $affected records</span><br>";
            } else {
                echo "<span class='error'>❌ Update failed: " . mysqli_error($conn) . "</span><br>";
            }
        }
    }

    echo "<h2>6. Final Verification</h2>";
    $result = mysqli_query($conn, "DESCRIBE userdata");
    echo "<table>";
    echo "<tr><th>Column Name</th><th>Type</th><th>Null</th><th>Default</th></tr>";

    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";

    echo "<h2>✅ Database Fix Complete!</h2>";
    echo "<div style='background: #d4edda; color: #155724; padding: 20px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3>🎉 Success!</h3>";
    echo "<p>All required database columns have been added. You can now:</p>";
    echo "</div>";

    echo "<h3>Next Steps:</h3>";
    echo "<ol>";
    echo "<li><a href='partials/registration.php'>Register as Administrator</a></li>";
    echo "<li><a href='index.php'>Login as Administrator</a></li>";
    echo "<li><a href='admin_dashboard.php'>Access Admin Dashboard</a></li>";
    echo "</ol>";

    mysqli_close($conn);

} catch (Exception $e) {
    echo "<span class='error'>❌ Error: " . $e->getMessage() . "</span><br>";
    echo "<span class='info'>💡 Make sure XAMPP MySQL is running and database exists</span><br>";
}
?>