<?php
session_start();
include('actions/connect.php');

echo "<h1>🧪 Admin Verification Test</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .test-section { border: 2px solid #007bff; padding: 20px; margin: 20px 0; border-radius: 10px; }
    .success { background: #d4edda; border-color: #c3e6cb; color: #155724; }
    .error { background: #f8d7da; border-color: #f5c6cb; color: #721c24; }
    .warning { background: #fff3cd; border-color: #ffeaa7; color: #856404; }
    .info { background: #d1ecf1; border-color: #bee5eb; color: #0c5460; }
    .btn { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; text-decoration: none; margin: 5px; }
    .btn:hover { background: #0056b3; }
    table { border-collapse: collapse; width: 100%; margin: 20px 0; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background-color: #f2f2f2; }
</style>";

// Test 1: Check admin session
echo "<div class='test-section info'>";
echo "<h3>1. Admin Session Check</h3>";
if (isset($_SESSION['data']) && $_SESSION['data']['standard'] == 'admin') {
    echo "<span class='success'>✅ Admin session active</span><br>";
    echo "Admin ID: " . ($_SESSION['data']['id'] ?? 'N/A') . "<br>";
    echo "Username: " . ($_SESSION['data']['username'] ?? 'N/A') . "<br>";
} else {
    echo "<span class='error'>❌ No admin session found</span><br>";
    echo "<a href='index.php' class='btn'>Go to Login</a>";
    exit;
}
echo "</div>";

// Test 2: Check database connection
echo "<div class='test-section info'>";
echo "<h3>2. Database Connection</h3>";
if ($conn) {
    echo "<span class='success'>✅ Database connected</span><br>";
} else {
    echo "<span class='error'>❌ Database connection failed</span><br>";
    echo "Error: " . mysqli_connect_error();
}
echo "</div>";

// Test 3: Check pending users
echo "<div class='test-section warning'>";
echo "<h3>3. Pending Users Check</h3>";
$pending_query = "SELECT * FROM userdata WHERE verification_status = 'pending' AND standard = 'voter'";
$pending_result = mysqli_query($conn, $pending_query);

if ($pending_result && mysqli_num_rows($pending_result) > 0) {
    echo "<span class='success'>✅ Found " . mysqli_num_rows($pending_result) . " pending user(s)</span><br><br>";

    echo "<table>";
    echo "<tr><th>ID</th><th>Username</th><th>Mobile</th><th>Age</th><th>ID Proof</th><th>Actions</th></tr>";

    while ($user = mysqli_fetch_assoc($pending_result)) {
        echo "<tr>";
        echo "<td>" . $user['id'] . "</td>";
        echo "<td>" . $user['username'] . "</td>";
        echo "<td>" . $user['mobile'] . "</td>";
        echo "<td>" . ($user['age'] ?? 'N/A') . "</td>";
        echo "<td>" . ($user['id_proof'] ? 'Yes' : 'No') . "</td>";
        echo "<td>";
        echo "<a href='actions/admin_verify.php?user_id=" . urlencode($user['id']) . "&action=verify' class='btn' style='background: #28a745;' onclick='return confirm(\"Verify this user?\")'>Verify</a>";
        echo "<a href='actions/admin_verify.php?user_id=" . urlencode($user['id']) . "&action=reject' class='btn' style='background: #ffc107; color: black;' onclick='return confirm(\"Reject this user?\")'>Reject</a>";
        echo "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<span class='warning'>⚠️ No pending users found</span><br>";
}
echo "</div>";

// Test 4: Check verified users
echo "<div class='test-section success'>";
echo "<h3>4. Verified Users Check</h3>";
$verified_query = "SELECT * FROM userdata WHERE verification_status = 'verified' AND standard = 'voter'";
$verified_result = mysqli_query($conn, $verified_query);

if ($verified_result && mysqli_num_rows($verified_result) > 0) {
    echo "<span class='success'>✅ Found " . mysqli_num_rows($verified_result) . " verified user(s)</span><br><br>";

    echo "<table>";
    echo "<tr><th>ID</th><th>Username</th><th>Mobile</th><th>Age</th><th>Status</th></tr>";

    while ($user = mysqli_fetch_assoc($verified_result)) {
        echo "<tr>";
        echo "<td>" . $user['id'] . "</td>";
        echo "<td>" . $user['username'] . "</td>";
        echo "<td>" . $user['mobile'] . "</td>";
        echo "<td>" . ($user['age'] ?? 'N/A') . "</td>";
        echo "<td><span style='background: #28a745; color: white; padding: 3px 8px; border-radius: 10px;'>Verified</span></td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<span class='warning'>⚠️ No verified users found</span><br>";
}
echo "</div>";

// Test 5: Show session messages
if (isset($_SESSION['message'])) {
    $message_type = isset($_SESSION['message_type']) ? $_SESSION['message_type'] : 'info';
    $bg_color = $message_type == 'success' ? '#d4edda' : ($message_type == 'error' ? '#f8d7da' : '#d1ecf1');

    echo "<div class='test-section' style='background: $bg_color;'>";
    echo "<h3>5. Last Action Result</h3>";
    echo "<strong>" . ucfirst($message_type) . ":</strong> " . $_SESSION['message'];
    echo "</div>";

    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
}

// Quick actions
echo "<div class='test-section'>";
echo "<h3>Quick Actions</h3>";
echo "<a href='admin_working.php' class='btn'>📊 Admin Dashboard</a>";
echo "<a href='create_admin_user.php' class='btn' style='background: #28a745;'>👑 Create Admin</a>";
echo "<a href='check_users.php' class='btn' style='background: #ffc107; color: black;'>👥 Check Users</a>";
echo "<a href='database_setup.php' class='btn' style='background: #17a2b8;'>🗄️ Database Setup</a>";
echo "<a href='partials/logout.php' class='btn' style='background: #dc3545;'>🚪 Logout</a>";
echo "</div>";

mysqli_close($conn);
?>