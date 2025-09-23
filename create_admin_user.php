<?php
// Create Admin User Script
include('actions/connect.php');

echo "<h1>👑 Create Admin User</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .success { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
    .warning { color: orange; font-weight: bold; }
    .info { color: blue; font-weight: bold; }
    form { background: #f8f9fa; padding: 20px; border-radius: 10px; margin: 20px 0; }
    input, select { padding: 10px; margin: 5px 0; width: 100%; border: 1px solid #ddd; border-radius: 5px; }
    button { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; }
    button:hover { background: #0056b3; }
</style>";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $mobile = mysqli_real_escape_string($conn, $_POST['mobile']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate input
    if (empty($username) || empty($mobile) || empty($password)) {
        echo "<span class='error'>❌ All fields are required!</span><br><br>";
    } elseif ($password != $confirm_password) {
        echo "<span class='error'>❌ Passwords do not match!</span><br><br>";
    } elseif (strlen($mobile) != 10 || !is_numeric($mobile)) {
        echo "<span class='error'>❌ Mobile number must be 10 digits!</span><br><br>";
    } else {
        // Check if admin already exists
        $check_admin = mysqli_query($conn, "SELECT * FROM userdata WHERE standard = 'admin'");
        if (mysqli_num_rows($check_admin) > 0) {
            echo "<span class='warning'>⚠️ Admin user already exists!</span><br><br>";
            $existing_admin = mysqli_fetch_assoc($check_admin);
            echo "<strong>Existing Admin:</strong><br>";
            echo "- Username: " . $existing_admin['username'] . "<br>";
            echo "- Mobile: " . $existing_admin['mobile'] . "<br>";
            echo "- ID: " . $existing_admin['id'] . "<br><br>";
        } else {
            // Create admin user
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $sql = "INSERT INTO `userdata` (`username`, `mobile`, `password`, `standard`, `status`, `verification_status`, `age`)
                    VALUES ('$username', '$mobile', '$hashed_password', 'admin', '1', 'verified', '25')";

            if (mysqli_query($conn, $sql)) {
                $admin_id = mysqli_insert_id($conn);
                echo "<span class='success'>✅ Admin user created successfully!</span><br><br>";
                echo "<strong>New Admin Details:</strong><br>";
                echo "- Username: $username<br>";
                echo "- Mobile: $mobile<br>";
                echo "- ID: $admin_id<br>";
                echo "- Password: [HIDDEN]<br><br>";
                echo "<span class='info'>💡 You can now login as admin using these credentials</span><br><br>";
            } else {
                echo "<span class='error'>❌ Error creating admin: " . mysqli_error($conn) . "</span><br><br>";
            }
        }
    }
}

// Show form
echo "<form method='POST' action=''>
    <h3>Create New Admin User</h3>
    <input type='text' name='username' placeholder='Admin Username' required><br>
    <input type='text' name='mobile' placeholder='Mobile Number (10 digits)' maxlength='10' required><br>
    <input type='password' name='password' placeholder='Password' required><br>
    <input type='password' name='confirm_password' placeholder='Confirm Password' required><br>
    <button type='submit'>Create Admin</button>
</form>";

// Show existing users
echo "<h3>Existing Users in Database:</h3>";
$result = mysqli_query($conn, "SELECT id, username, mobile, standard, verification_status FROM userdata ORDER BY id DESC");

if ($result && mysqli_num_rows($result) > 0) {
    echo "<table style='border-collapse: collapse; width: 100%; margin: 20px 0;'>";
    echo "<tr style='background: #f2f2f2;'><th style='border: 1px solid #ddd; padding: 8px;'>ID</th><th style='border: 1px solid #ddd; padding: 8px;'>Username</th><th style='border: 1px solid #ddd; padding: 8px;'>Mobile</th><th style='border: 1px solid #ddd; padding: 8px;'>Type</th><th style='border: 1px solid #ddd; padding: 8px;'>Status</th></tr>";

    while ($user = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $user['id'] . "</td>";
        echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $user['username'] . "</td>";
        echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $user['mobile'] . "</td>";
        echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $user['standard'] . "</td>";
        echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . $user['verification_status'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<span class='warning'>⚠️ No users found in database</span><br>";
}

echo "<br><h3>Quick Actions:</h3>";
echo "<a href='index.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>Go to Login</a>";
echo "<a href='admin_working.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>Admin Dashboard</a>";
echo "<a href='check_users.php' style='background: #ffc107; color: black; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Check Users</a>";

mysqli_close($conn);
?>