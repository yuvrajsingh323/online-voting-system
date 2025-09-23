<?php
// Test Database Connection and Table Structure
echo "<h1>🔍 Database Connection Test</h1>";
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

echo "<h2>1. PHP Configuration</h2>";
echo "<strong>PHP Version:</strong> " . phpversion() . "<br>";
echo "<strong>Current Directory:</strong> " . __DIR__ . "<br>";
echo "<strong>Config File Path:</strong> " . __DIR__ . '/config.php' . "<br>";
echo "<strong>Config File Exists:</strong> " . (file_exists('config.php') ? '<span class="success">✅ Yes</span>' : '<span class="error">❌ No</span>') . "<br>";

echo "<h2>2. Database Connection Test</h2>";

// Test 1: Include config file
echo "<h3>Testing Config Include:</h3>";
try {
    include('config.php');
    echo "<span class='success'>✅ Config file included successfully</span><br>";
    echo "<strong>Server:</strong> $servername<br>";
    echo "<strong>Database:</strong> $dbname<br>";
} catch (Exception $e) {
    echo "<span class='error'>❌ Config include failed: " . $e->getMessage() . "</span><br>";
    exit;
}

// Test 2: Database connection
echo "<h3>Testing Database Connection:</h3>";
try {
    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        echo "<span class='error'>❌ Database connection failed: " . $conn->connect_error . "</span><br>";
        exit;
    } else {
        echo "<span class='success'>✅ Database connected successfully!</span><br>";
    }
} catch (Exception $e) {
    echo "<span class='error'>❌ Database connection error: " . $e->getMessage() . "</span><br>";
    exit;
}

// Test 3: Check if table exists
echo "<h2>3. Table Structure Check</h2>";
$result = mysqli_query($conn, "SHOW TABLES LIKE 'userdata'");
if (mysqli_num_rows($result) > 0) {
    echo "<span class='success'>✅ 'userdata' table exists</span><br>";

    // Show table structure
    echo "<h3>Table Structure:</h3>";
    $structure_result = mysqli_query($conn, "DESCRIBE userdata");

    echo "<table>";
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
    echo "<span class='error'>❌ 'userdata' table does not exist</span><br>";
    echo "<span class='info'>💡 Run database setup first</span><br>";
}

// Test 4: Show sample data
echo "<h2>4. Sample User Data</h2>";
$user_result = mysqli_query($conn, "SELECT id, username, mobile, standard, verification_status FROM userdata ORDER BY id DESC LIMIT 10");

if ($user_result && mysqli_num_rows($user_result) > 0) {
    echo "<span class='success'>✅ Found " . mysqli_num_rows($user_result) . " users</span><br>";
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
} else {
    echo "<span class='warning'>⚠️ No users found in database</span><br>";
}

// Test 5: Test login query
echo "<h2>5. Login Query Test</h2>";
if (isset($_GET['test_username']) && isset($_GET['test_mobile']) && isset($_GET['test_standard'])) {
    $test_username = $_GET['test_username'];
    $test_mobile = $_GET['test_mobile'];
    $test_standard = $_GET['test_standard'];

    echo "<h3>Testing Login Query:</h3>";
    echo "<strong>Username:</strong> $test_username<br>";
    echo "<strong>Mobile:</strong> $test_mobile<br>";
    echo "<strong>Type:</strong> $test_standard<br>";

    $login_query = "SELECT * FROM `userdata` WHERE `username`='$test_username' AND `mobile`='$test_mobile' AND `standard`='$test_standard'";
    echo "<strong>Query:</strong> $login_query<br>";

    $login_result = mysqli_query($conn, $login_query);

    if ($login_result && mysqli_num_rows($login_result) > 0) {
        echo "<span class='success'>✅ User found! Login should work.</span><br>";
        $user_data = mysqli_fetch_assoc($login_result);
        echo "<strong>User Data:</strong><br>";
        echo "- ID: " . $user_data['id'] . "<br>";
        echo "- Username: " . $user_data['username'] . "<br>";
        echo "- Mobile: " . $user_data['mobile'] . "<br>";
        echo "- Type: " . $user_data['standard'] . "<br>";
        echo "- Status: " . $user_data['verification_status'] . "<br>";
    } else {
        echo "<span class='error'>❌ User not found! Login will fail.</span><br>";
        echo "<strong>Error:</strong> " . mysqli_error($conn) . "<br>";
    }
} else {
    echo "<span class='info'>💡 Add ?test_username=yourname&test_mobile=yourmobile&test_standard=admin to URL to test login</span><br>";
}

echo "<h2>6. Quick Actions</h2>";
echo "<a href='partials/registration.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>Register as Admin</a>";
echo "<a href='index.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>Go to Login</a>";
echo "<a href='check_users.php' style='background: #6c757d; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Check Users</a>";

mysqli_close($conn);
?>