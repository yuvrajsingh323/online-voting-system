<?php
include('actions/connect.php');

echo "<h1>🔍 Photo Path Analysis</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    table { border-collapse: collapse; width: 100%; margin: 20px 0; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background-color: #f2f2f2; }
    .exists { background: #d4edda; color: #155724; }
    .missing { background: #f8d7da; color: #721c24; }
    .empty { background: #fff3cd; color: #856404; }
</style>";

$query = "SELECT id, username, standard, photo, id_proof FROM userdata ORDER BY id DESC";
$result = mysqli_query($conn, $query);

if ($result && mysqli_num_rows($result) > 0) {
    echo "<table>";
    echo "<tr><th>ID</th><th>Username</th><th>Type</th><th>Photo Path</th><th>Photo Exists</th><th>ID Proof Path</th><th>ID Proof Exists</th></tr>";

    while ($user = mysqli_fetch_assoc($result)) {
        $photo_exists = !empty($user['photo']) && file_exists('uploads/' . $user['photo']);
        $id_proof_exists = !empty($user['id_proof']) && file_exists('uploads/' . $user['id_proof']);

        echo "<tr>";
        echo "<td>" . $user['id'] . "</td>";
        echo "<td>" . htmlspecialchars($user['username']) . "</td>";
        echo "<td>" . ucfirst($user['standard']) . "</td>";
        echo "<td>" . htmlspecialchars($user['photo'] ?? 'NULL') . "</td>";
        echo "<td class='" . (empty($user['photo']) ? 'empty' : ($photo_exists ? 'exists' : 'missing')) . "'>";
        echo empty($user['photo']) ? 'EMPTY' : ($photo_exists ? 'EXISTS' : 'MISSING');
        echo "</td>";
        echo "<td>" . htmlspecialchars($user['id_proof'] ?? 'NULL') . "</td>";
        echo "<td class='" . (empty($user['id_proof']) ? 'empty' : ($id_proof_exists ? 'exists' : 'missing')) . "'>";
        echo empty($user['id_proof']) ? 'EMPTY' : ($id_proof_exists ? 'EXISTS' : 'MISSING');
        echo "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No users found in database.</p>";
}

echo "<h2>📁 Files in Uploads Directory:</h2>";
$files = scandir('uploads/');
echo "<ul>";
foreach ($files as $file) {
    if ($file != '.' && $file != '..') {
        echo "<li>" . htmlspecialchars($file) . "</li>";
    }
}
echo "</ul>";

mysqli_close($conn);
?>