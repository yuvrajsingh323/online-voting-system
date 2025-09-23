<?php
include('actions/connect.php');

echo "<h2>Profile Images Check</h2>";

// Get all users with photos
$sql = "SELECT id, username, photo FROM userdata WHERE photo != '' AND photo IS NOT NULL";
$result = mysqli_query($conn, $sql);

echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr><th>ID</th><th>Username</th><th>Photo Path</th><th>File Exists</th><th>Preview</th></tr>";

while ($row = mysqli_fetch_assoc($result)) {
    $photo_path = 'uploads/' . $row['photo'];
    $file_exists = file_exists($photo_path);
    $exists_text = $file_exists ? '<span style="color: green;">YES</span>' : '<span style="color: red;">NO</span>';

    echo "<tr>";
    echo "<td>" . $row['id'] . "</td>";
    echo "<td>" . htmlspecialchars($row['username']) . "</td>";
    echo "<td>" . htmlspecialchars($row['photo']) . "</td>";
    echo "<td>" . $exists_text . "</td>";
    echo "<td>";
    if ($file_exists) {
        $file_ext = strtolower(pathinfo($row['photo'], PATHINFO_EXTENSION));
        if (in_array($file_ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
            echo "<img src='" . htmlspecialchars($photo_path) . "' style='max-width: 100px; max-height: 100px;'>";
        } elseif (in_array($file_ext, ['mp4', 'avi', 'mov'])) {
            echo "<video style='max-width: 100px; max-height: 100px;' controls><source src='" . htmlspecialchars($photo_path) . "' type='video/$file_ext'></video>";
        } else {
            echo "Unsupported format";
        }
    } else {
        echo "File not found";
    }
    echo "</td>";
    echo "</tr>";
}

echo "</table>";

// Check uploads directory
echo "<h3>Uploads Directory Contents:</h3>";
$uploads_dir = 'uploads/';
if (is_dir($uploads_dir)) {
    $files = scandir($uploads_dir);
    echo "<ul>";
    foreach ($files as $file) {
        if ($file != '.' && $file != '..') {
            echo "<li>" . htmlspecialchars($file) . "</li>";
        }
    }
    echo "</ul>";
} else {
    echo "<span style='color: red;'>Uploads directory does not exist!</span>";
}

echo "<br><a href='admin_simple.php'>Back to Admin Dashboard</a>";
?>