<?php
// Check Users in Database
include('actions/connect.php');

echo "<h1>👥 Database Users Check</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .success { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
    .warning { color: orange; font-weight: bold; }
    table { border-collapse: collapse; width: 100%; margin: 20px 0; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background-color: #f2f2f2; }
</style>";

// Check if table exists
$result = mysqli_query($conn, "SHOW TABLES LIKE 'userdata'");
if (mysqli_num_rows($result) == 0) {
    echo "<span class='error'>❌ 'userdata' table does not exist!</span><br>";
    echo "<a href='setup_database.php'>Run Database Setup</a><br><br>";
    exit;
}

// Get all users
$sql = "SELECT id, username, mobile, standard, verification_status, age FROM userdata ORDER BY id DESC";
$result = mysqli_query($conn, $sql);

if ($result && mysqli_num_rows($result) > 0) {
    echo "<span class='success'>✅ Found " . mysqli_num_rows($result) . " users in database</span><br><br>";

    echo "<table>";
    echo "<tr><th>ID</th><th>Username</th><th>Mobile</th><th>Type</th><th>Verification</th><th>Age</th></tr>";

    while ($user = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>" . $user['id'] . "</td>";
        echo "<td>" . $user['username'] . "</td>";
        echo "<td>" . $user['mobile'] . "</td>";
        echo "<td>" . $user['standard'] . "</td>";
        echo "<td>" . $user['verification_status'] . "</td>";
        echo "<td>" . ($user['age'] ?? 'N/A') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<span class='warning'>⚠️ No users found in database</span><br>";
}

// Check for admin user specifically
$admin_check = mysqli_query($conn, "SELECT * FROM userdata WHERE standard = 'admin'");
if (mysqli_num_rows($admin_check) > 0) {
    echo "<span class='success'>✅ Admin user exists</span><br>";
    $admin = mysqli_fetch_assoc($admin_check);
    echo "<strong>Admin Details:</strong><br>";
    echo "- Username: " . $admin['username'] . "<br>";
    echo "- Mobile: " . $admin['mobile'] . "<br>";
    echo "- Status: " . $admin['verification_status'] . "<br>";
} else {
    echo "<span class='error'>❌ No admin user found</span><br>";
    echo "<span class='info'>💡 Admins must register themselves using the registration form</span><br>";
    echo "<a href='partials/registration.php'>Register as Admin</a><br>";
}

echo "<br><h3>Quick Actions:</h3>";
echo "<a href='index.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>Go to Login</a>";
echo "<a href='create_admin_user.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>Create Admin</a>";
echo "<a href='setup_database.php' style='background: #ffc107; color: black; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Setup Database</a>";

mysqli_close($conn);
?>