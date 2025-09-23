<?php
// Fix Admin Enum in Database
echo "<h1>🔧 Fix Admin Enum in Database</h1>";
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

    echo "<h2>1. Current Standard Column Structure</h2>";
    $result = mysqli_query($conn, "DESCRIBE userdata");

    echo "<table>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";

    while ($row = mysqli_fetch_assoc($result)) {
        if ($row['Field'] == 'standard') {
            echo "<tr style='background-color: #fff3cd;'>";
            echo "<td><strong>" . $row['Field'] . "</strong></td>";
            echo "<td><strong>" . $row['Type'] . "</strong></td>";
            echo "<td>" . $row['Null'] . "</td>";
            echo "<td>" . $row['Key'] . "</td>";
            echo "<td>" . $row['Default'] . "</td>";
            echo "</tr>";
        }
    }
    echo "</table>";

    // Check current enum values
    $enum_check = mysqli_query($conn, "SELECT COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS
                                      WHERE TABLE_NAME = 'userdata' AND COLUMN_NAME = 'standard'");
    $enum_row = mysqli_fetch_assoc($enum_check);
    echo "<strong>Current Enum Values:</strong> " . $enum_row['COLUMN_TYPE'] . "<br><br>";

    echo "<h2>2. Fixing Admin Enum</h2>";

    // Modify the enum to include admin
    $alter_sql = "ALTER TABLE `userdata` MODIFY COLUMN `standard` ENUM('candidate', 'voter', 'admin') NOT NULL";
    echo "Executing: <code>$alter_sql</code><br>";

    if (mysqli_query($conn, $alter_sql)) {
        echo "<span class='success'>✅ Standard column enum updated successfully!</span><br>";
    } else {
        echo "<span class='error'>❌ Failed to update enum: " . mysqli_error($conn) . "</span><br>";
    }

    // Update any existing admin records
    $update_sql = "UPDATE `userdata` SET `standard` = 'admin' WHERE `standard` = 'administrator'";
    echo "Executing: <code>$update_sql</code><br>";

    $update_result = mysqli_query($conn, $update_sql);
    if ($update_result) {
        $affected = mysqli_affected_rows($conn);
        echo "<span class='success'>✅ Updated $affected records</span><br>";
    } else {
        echo "<span class='warning'>⚠️ Update query failed (may be no records to update): " . mysqli_error($conn) . "</span><br>";
    }

    echo "<h2>3. Verification</h2>";

    // Check updated enum values
    $enum_check2 = mysqli_query($conn, "SELECT COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS
                                       WHERE TABLE_NAME = 'userdata' AND COLUMN_NAME = 'standard'");
    $enum_row2 = mysqli_fetch_assoc($enum_check2);
    echo "<strong>Updated Enum Values:</strong> " . $enum_row2['COLUMN_TYPE'] . "<br><br>";

    // Show current users
    $user_result = mysqli_query($conn, "SELECT id, username, mobile, standard, verification_status FROM userdata ORDER BY id DESC LIMIT 10");

    if ($user_result && mysqli_num_rows($user_result) > 0) {
        echo "<h3>Current Users:</h3>";
        echo "<table>";
        echo "<tr><th>ID</th><th>Username</th><th>Mobile</th><th>Type</th><th>Status</th></tr>";

        while ($user = mysqli_fetch_assoc($user_result)) {
            echo "<tr>";
            echo "<td>" . $user['id'] . "</td>";
            echo "<td>" . $user['username'] . "</td>";
            echo "<td>" . $user['mobile'] . "</td>";
            echo "<td>" . $user['standard'] . "</td>";
            echo "<td>" . $user['verification_status'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }

    echo "<h2>✅ Fix Complete!</h2>";
    echo "<div style='background: #d4edda; color: #155724; padding: 20px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3>🎉 Admin Enum Fixed!</h3>";
    echo "<p>The database now accepts 'admin' as a valid user type.</p>";
    echo "<p>You can now register and login as an administrator.</p>";
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
}
?>