<?php
// Comprehensive Database Diagnostic Script
echo "<h1>🩺 Database Diagnostic Report</h1>";
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

// Test 1: PHP Configuration
echo "<h2>1. 📋 PHP Configuration</h2>";
echo "<strong>PHP Version:</strong> " . phpversion() . "<br>";
echo "<strong>File Uploads Enabled:</strong> " . (ini_get('file_uploads') ? '<span class="success">✅ Yes</span>' : '<span class="error">❌ No</span>') . "<br>";
echo "<strong>Upload Max Size:</strong> " . ini_get('upload_max_filesize') . "<br>";
echo "<strong>Post Max Size:</strong> " . ini_get('post_max_filesize') . "<br>";
echo "<strong>Memory Limit:</strong> " . ini_get('memory_limit') . "<br>";

// Test 2: Database Connection
echo "<h2>2. 🔌 Database Connection Test</h2>";
try {
    include('config.php');
    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        echo "<span class='error'>❌ Connection Failed: " . $conn->connect_error . "</span><br>";
    } else {
        echo "<span class='success'>✅ Database Connected Successfully!</span><br>";
        echo "<strong>Server:</strong> $servername<br>";
        echo "<strong>Database:</strong> $dbname<br>";
    }
} catch (Exception $e) {
    echo "<span class='error'>❌ Connection Error: " . $e->getMessage() . "</span><br>";
    $conn = null;
}

// Test 3: Table Structure
echo "<h2>3. 📊 Table Structure Analysis</h2>";
if ($conn) {
    $result = mysqli_query($conn, "SHOW TABLES LIKE 'userdata'");
    if (mysqli_num_rows($result) > 0) {
        echo "<span class='success'>✅ 'userdata' table exists</span><br>";

        // Check columns
        $columns_result = mysqli_query($conn, "DESCRIBE userdata");
        $existing_columns = [];
        echo "<h3>Existing Columns:</h3>";
        echo "<table>";
        echo "<tr><th>Column Name</th><th>Type</th><th>Null</th><th>Default</th></tr>";

        while ($row = mysqli_fetch_assoc($columns_result)) {
            echo "<tr>";
            echo "<td>" . $row['Field'] . "</td>";
            echo "<td>" . $row['Type'] . "</td>";
            echo "<td>" . $row['Null'] . "</td>";
            echo "<td>" . $row['Default'] . "</td>";
            echo "</tr>";
            $existing_columns[] = $row['Field'];
        }
        echo "</table>";

        // Check for required columns
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

        // Auto-fix missing columns
        if (!empty($missing_columns)) {
            echo "<h3>🔧 Auto-Fixing Missing Columns:</h3>";
            $alter_queries = [
                "ALTER TABLE userdata ADD COLUMN age INT NULL AFTER photo",
                "ALTER TABLE userdata ADD COLUMN id_proof VARCHAR(255) NULL AFTER age",
                "ALTER TABLE userdata ADD COLUMN verification_status ENUM('pending', 'verified', 'rejected') DEFAULT 'pending' AFTER id_proof",
                "ALTER TABLE userdata ADD COLUMN date_of_birth DATE NULL AFTER verification_status"
            ];

            foreach ($alter_queries as $query) {
                $column_name = explode('ADD COLUMN ', $query)[1];
                $column_name = explode(' ', $column_name)[0];

                if (in_array($column_name, $missing_columns)) {
                    if (mysqli_query($conn, $query)) {
                        echo "<span class='success'>✅ Added $column_name column</span><br>";
                    } else {
                        echo "<span class='error'>❌ Failed to add $column_name: " . mysqli_error($conn) . "</span><br>";
                    }
                }
            }

            // Create indexes
            echo "<h3>📈 Creating Indexes:</h3>";
            $index_queries = [
                "CREATE INDEX idx_verification_status ON userdata (verification_status)",
                "CREATE INDEX idx_age ON userdata (age)"
            ];

            foreach ($index_queries as $query) {
                if (mysqli_query($conn, $query)) {
                    echo "<span class='success'>✅ Index created</span><br>";
                } else {
                    echo "<span class='warning'>⚠️ Index creation failed (may already exist): " . mysqli_error($conn) . "</span><br>";
                }
            }
        }

    } else {
        echo "<span class='error'>❌ 'userdata' table does not exist</span><br>";
        echo "<span class='info'>💡 You may need to run the initial database setup first</span><br>";
    }
}

// Test 4: File Permissions
echo "<h2>4. 📁 File System Check</h2>";
$uploads_dir = __DIR__ . '/uploads/';

if (file_exists($uploads_dir)) {
    echo "<span class='success'>✅ Uploads directory exists</span><br>";

    if (is_writable($uploads_dir)) {
        echo "<span class='success'>✅ Uploads directory is writable</span><br>";
    } else {
        echo "<span class='error'>❌ Uploads directory is not writable</span><br>";
        echo "<span class='info'>💡 Run: chmod 755 uploads/</span><br>";
    }

    // Count files in uploads
    $files = glob($uploads_dir . '*');
    echo "<strong>Files in uploads:</strong> " . count($files) . "<br>";

} else {
    echo "<span class='error'>❌ Uploads directory does not exist</span><br>";
    echo "<span class='info'>💡 Create the uploads directory</span><br>";
}

// Test 5: Sample Data
echo "<h2>5. 📈 Sample Data Check</h2>";
if ($conn) {
    $count_result = mysqli_query($conn, "SELECT COUNT(*) as total FROM userdata");
    if ($count_result) {
        $count = mysqli_fetch_assoc($count_result)['total'];
        echo "<strong>Total users:</strong> $count<br>";

        // Show recent users
        $recent_result = mysqli_query($conn, "SELECT username, standard, verification_status FROM userdata ORDER BY id DESC LIMIT 5");
        if (mysqli_num_rows($recent_result) > 0) {
            echo "<h3>Recent Users:</h3>";
            echo "<table>";
            echo "<tr><th>Username</th><th>Type</th><th>Verification Status</th></tr>";
            while ($user = mysqli_fetch_assoc($recent_result)) {
                echo "<tr>";
                echo "<td>" . $user['username'] . "</td>";
                echo "<td>" . $user['standard'] . "</td>";
                echo "<td>" . $user['verification_status'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    }
}

// Recommendations
echo "<h2>6. 🎯 Recommendations</h2>";
echo "<h3>If registration still fails:</h3>";
echo "1. <strong>Check PHP Error Logs:</strong> C:\\xampp\\php\\logs\\php_error_log<br>";
echo "2. <strong>Verify Database Permissions:</strong> Ensure user has INSERT/ALTER permissions<br>";
echo "3. <strong>Test with phpMyAdmin:</strong> Try manual SQL execution<br>";
echo "4. <strong>Check File Permissions:</strong> uploads/ directory must be writable<br>";
echo "5. <strong>Browser Console:</strong> Check for JavaScript errors (F12)<br>";

echo "<h3>Quick Test:</h3>";
echo "<a href='partials/registration.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Test Registration</a><br><br>";

echo "<a href='database_setup.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Run Database Setup</a><br><br>";

if ($conn) {
    $conn->close();
}
?>