<?php
include('actions/connect.php');

// Check current admin user
$check_admin = "SELECT id, username, standard, verification_status FROM userdata WHERE username = 'admin'";
$result = mysqli_query($conn, $check_admin);

if ($result && mysqli_num_rows($result) > 0) {
    $admin_data = mysqli_fetch_assoc($result);
    echo "<h2>Current Admin User Status</h2>";
    echo "<p><strong>Username:</strong> " . htmlspecialchars($admin_data['username']) . "</p>";
    echo "<p><strong>Current Account Type:</strong> " . htmlspecialchars($admin_data['standard']) . "</p>";
    echo "<p><strong>Verification Status:</strong> " . htmlspecialchars($admin_data['verification_status']) . "</p>";

    // If admin is not properly set as admin, fix it
    if ($admin_data['standard'] != 'admin') {
        echo "<h3>Fixing Admin Account...</h3>";

        $update_sql = "UPDATE userdata SET
                      standard = 'admin',
                      verification_status = 'verified',
                      age = NULL,
                      id_proof = NULL,
                      date_of_birth = NULL
                      WHERE id = '" . mysqli_real_escape_string($conn, $admin_data['id']) . "'";

        if (mysqli_query($conn, $update_sql)) {
            echo "<p style='color: green;'><strong>✅ SUCCESS:</strong> Admin account has been updated to proper admin type!</p>";
            echo "<p><strong>New Account Type:</strong> admin</p>";
            echo "<p><strong>Verification Status:</strong> verified</p>";
        } else {
            echo "<p style='color: red;'><strong>❌ ERROR:</strong> Failed to update admin account: " . mysqli_error($conn) . "</p>";
        }
    } else {
        echo "<p style='color: green;'><strong>✅ Admin account is already properly configured!</strong></p>";
    }
} else {
    echo "<h2>No Admin User Found</h2>";
    echo "<p>Creating default admin user...</p>";

    // Create default admin user
    $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
    $create_admin = "INSERT INTO userdata (username, mobile, password, standard, verification_status, status, votes)
                    VALUES ('admin', '9999999999', '$admin_password', 'admin', 'verified', '0', '0')";

    if (mysqli_query($conn, $create_admin)) {
        echo "<p style='color: green;'><strong>✅ SUCCESS:</strong> Default admin user created!</p>";
        echo "<p><strong>Username:</strong> admin</p>";
        echo "<p><strong>Password:</strong> admin123</p>";
        echo "<p><strong>Account Type:</strong> admin</p>";
    } else {
        echo "<p style='color: red;'><strong>❌ ERROR:</strong> Failed to create admin user: " . mysqli_error($conn) . "</p>";
    }
}

echo "<br><br><a href='admin_dashboard.php'>Go to Admin Dashboard</a>";
echo "<br><a href='index.php'>Go to Login Page</a>";
?>