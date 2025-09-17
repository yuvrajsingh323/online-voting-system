<?php
// Complete Database Setup Script
echo "<h1>🗄️ Complete Database Setup</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .success { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
    .warning { color: orange; font-weight: bold; }
    .info { color: blue; }
</style>";

// Step 1: Test basic MySQL connection (without database)
echo "<h2>1. Testing MySQL Connection</h2>";
$servername = "localhost";
$username = "root";
$password = "";

try {
    $conn = new mysqli($servername, $username, $password);

    if ($conn->connect_error) {
        echo "<span class='error'>❌ MySQL Connection Failed: " . $conn->connect_error . "</span><br>";
        echo "<span class='info'>💡 Make sure XAMPP MySQL is running</span><br>";
        exit;
    } else {
        echo "<span class='success'>✅ MySQL Connected Successfully!</span><br>";
    }
} catch (Exception $e) {
    echo "<span class='error'>❌ MySQL Connection Error: " . $e->getMessage() . "</span><br>";
    echo "<span class='info'>💡 Start XAMPP and enable MySQL</span><br>";
    exit;
}

// Step 2: Create database if it doesn't exist
echo "<h2>2. Creating Database</h2>";
$dbname = "onlinevotingsystem_db";

$sql = "CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
if ($conn->query($sql) === TRUE) {
    echo "<span class='success'>✅ Database '$dbname' ready</span><br>";
} else {
    echo "<span class='error'>❌ Database creation failed: " . $conn->error . "</span><br>";
    exit;
}

// Step 3: Select the database
$conn->select_db($dbname);

// Step 4: Create users table with all required fields
echo "<h2>3. Creating Users Table</h2>";
$table_sql = "CREATE TABLE IF NOT EXISTS `userdata` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `username` varchar(100) NOT NULL,
    `mobile` varchar(15) NOT NULL,
    `password` varchar(255) NOT NULL,
    `standard` enum('candidate','voter','admin') NOT NULL,
    `photo` varchar(255) DEFAULT '',
    `status` int(11) DEFAULT 0,
    `votes` int(11) DEFAULT 0,
    `age` int(11) DEFAULT NULL,
    `id_proof` varchar(255) DEFAULT '',
    `verification_status` enum('pending','verified','rejected') DEFAULT 'pending',
    `date_of_birth` date DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_user` (`username`,`mobile`,`standard`),
    KEY `idx_verification_status` (`verification_status`),
    KEY `idx_age` (`age`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if ($conn->query($table_sql) === TRUE) {
    echo "<span class='success'>✅ Users table created successfully</span><br>";
} else {
    echo "<span class='error'>❌ Table creation failed: " . $conn->error . "</span><br>";
    exit;
}

// Step 5: Create uploads directory if it doesn't exist
echo "<h2>4. Setting Up Uploads Directory</h2>";
$uploads_dir = __DIR__ . '/uploads/';

if (!file_exists($uploads_dir)) {
    if (mkdir($uploads_dir, 0755, true)) {
        echo "<span class='success'>✅ Uploads directory created</span><br>";
    } else {
        echo "<span class='warning'>⚠️ Could not create uploads directory</span><br>";
    }
} else {
    echo "<span class='success'>✅ Uploads directory exists</span><br>";
}

if (is_writable($uploads_dir)) {
    echo "<span class='success'>✅ Uploads directory is writable</span><br>";
} else {
    echo "<span class='error'>❌ Uploads directory is not writable</span><br>";
    echo "<span class='info'>💡 Run: chmod 755 uploads/</span><br>";
}

// Step 6: Create config.php file
echo "<h2>5. Creating Configuration File</h2>";
$config_content = "<?php
\$servername = \"localhost\";
\$username = \"root\";
\$password = \"\";
\$dbname = \"onlinevotingsystem_db\";
?>";

$config_file = __DIR__ . '/config.php';
if (!file_exists($config_file)) {
    if (file_put_contents($config_file, $config_content)) {
        echo "<span class='success'>✅ Config file created</span><br>";
    } else {
        echo "<span class='error'>❌ Could not create config file</span><br>";
    }
} else {
    echo "<span class='success'>✅ Config file exists</span><br>";
}

// Step 7: Test full connection
echo "<h2>6. Testing Full Database Connection</h2>";
$test_conn = new mysqli($servername, $username, $password, $dbname);
if ($test_conn->connect_error) {
    echo "<span class='error'>❌ Full connection test failed: " . $test_conn->connect_error . "</span><br>";
} else {
    echo "<span class='success'>✅ Full database connection successful!</span><br>";

    // Test a simple query
    $test_query = "SELECT COUNT(*) as total FROM userdata";
    $result = $test_conn->query($test_query);
    if ($result) {
        $row = $result->fetch_assoc();
        echo "<span class='success'>✅ Query test successful - " . $row['total'] . " users in database</span><br>";
    } else {
        echo "<span class='error'>❌ Query test failed: " . $test_conn->error . "</span><br>";
    }
    $test_conn->close();
}

// Step 8: Summary and next steps
echo "<h2>7. Setup Complete!</h2>";
echo "<div style='background: #d4edda; color: #155724; padding: 20px; border-radius: 5px; margin: 20px 0;'>";
echo "<h3>✅ Database Setup Successful!</h3>";
echo "<p>Your Online Voting System database is now ready.</p>";
echo "</div>";

echo "<h3>Next Steps:</h3>";
echo "<ol>";
echo "<li><a href='partials/registration.php'>Register as Admin</a> (select Administrator from account type)</li>";
echo "<li><a href='index.php'>Go to Login Page</a></li>";
echo "<li><a href='partials/registration.php'>Test Voter/Candidate Registration</a></li>";
echo "</ol>";

echo "<div style='background: #fff3cd; color: #856404; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<h4>🔐 Admin Registration:</h4>";
echo "<p>Administrators must register themselves using the registration form. Choose 'Administrator' as the account type.</p>";
echo "<p><strong>Note:</strong> Admins are automatically verified and don't need age/ID proof.</p>";
echo "</div>";

$conn->close();
?>