<?php
include('actions/connect.php');

// Admin user details
$admin_username = 'admin';
$admin_mobile = '9999999999';
$admin_password = 'admin123'; // Change this to a secure password
$admin_standard = 'admin';

// Check if admin already exists
$check_admin = "SELECT id FROM userdata WHERE username = '$admin_username' AND standard = 'admin'";
$result = mysqli_query($conn, $check_admin);

if (mysqli_num_rows($result) == 0) {
    // Create admin user
    $hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);

    $sql = "INSERT INTO `userdata` (`username`, `mobile`, `password`, `standard`, `status`, `verification_status`)
            VALUES ('$admin_username', '$admin_mobile', '$hashed_password', '$admin_standard', '1', 'verified')";

    if (mysqli_query($conn, $sql)) {
        echo "<div style='color: green; font-weight: bold; text-align: center; margin: 50px;'>";
        echo "<h2>✅ Admin User Created Successfully!</h2>";
        echo "<p><strong>Username:</strong> $admin_username</p>";
        echo "<p><strong>Mobile:</strong> $admin_mobile</p>";
        echo "<p><strong>Password:</strong> $admin_password</p>";
        echo "<p style='color: red;'>⚠️ Please change the password after first login!</p>";
        echo "<br><a href='index.php' style='color: blue;'>Go to Login Page</a>";
        echo "</div>";
    } else {
        echo "<div style='color: red; font-weight: bold; text-align: center; margin: 50px;'>";
        echo "<h2>❌ Error Creating Admin User</h2>";
        echo "<p>" . mysqli_error($conn) . "</p>";
        echo "</div>";
    }
} else {
    echo "<div style='color: orange; font-weight: bold; text-align: center; margin: 50px;'>";
    echo "<h2>⚠️ Admin User Already Exists</h2>";
    echo "<p>The admin user '$admin_username' is already created.</p>";
    echo "<br><a href='index.php' style='color: blue;'>Go to Login Page</a>";
    echo "</div>";
}
?>