<?php
session_start();
include('actions/connect.php');

if (!isset($_SESSION['data']) || $_SESSION['data']['standard'] != 'admin') {
    header("Location: index.php");
    exit();
}

// Get the pending user
$user_sql = "SELECT * FROM userdata WHERE verification_status = 'pending' LIMIT 1";
$user_result = mysqli_query($conn, $user_sql);
$user = mysqli_fetch_assoc($user_result);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Test Verification</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .test-box { border: 2px solid #007bff; padding: 20px; margin: 20px 0; border-radius: 10px; }
        .success { background: #d4edda; border-color: #c3e6cb; color: #155724; }
        .error { background: #f8d7da; border-color: #f5c6cb; color: #721c24; }
        .btn { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; text-decoration: none; display: inline-block; margin: 5px; }
        .btn:hover { background: #0056b3; }
    </style>
</head>
<body>
    <h1>🧪 Test User Verification</h1>

    <div class="test-box">
        <h3>Current User Status:</h3>
        <?php if ($user): ?>
            <p><strong>Name:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
            <p><strong>Status:</strong> <?php echo htmlspecialchars($user['verification_status']); ?></p>
            <p><strong>Age:</strong> <?php echo $user['age'] ?? 'N/A'; ?> years</p>
            <p><strong>ID Proof:</strong> <?php echo $user['id_proof'] ? 'Yes' : 'No'; ?></p>

            <a href="actions/admin_verify.php?user_id=<?php echo $user['id']; ?>&action=verify" class="btn">✅ Verify User</a>
            <a href="actions/admin_verify.php?user_id=<?php echo $user['id']; ?>&action=reject" class="btn" style="background: #ffc107; color: black;">❌ Reject User</a>
        <?php else: ?>
            <p>No pending users found.</p>
        <?php endif; ?>
    </div>

    <div class="test-box">
        <h3>Quick Actions:</h3>
        <a href="admin_working.php" class="btn">📊 Admin Dashboard</a>
        <a href="database_setup.php" class="btn" style="background: #28a745;">🗄️ Check Database</a>
        <a href="partials/logout.php" class="btn" style="background: #dc3545;">🚪 Logout</a>
    </div>

    <?php if (isset($_SESSION['message'])): ?>
    <div class="test-box <?php echo ($_SESSION['message_type'] == 'success') ? 'success' : 'error'; ?>">
        <h3><?php echo ucfirst($_SESSION['message_type']); ?>!</h3>
        <p><?php echo $_SESSION['message']; ?></p>
    </div>
    <?php
        unset($_SESSION['message']);
        unset($_SESSION['message_type']);
    endif; ?>

    <div class="test-box">
        <h3>Debug Information:</h3>
        <p><strong>Current User ID:</strong> <?php echo $_SESSION['data']['id'] ?? 'N/A'; ?></p>
        <p><strong>User Type:</strong> <?php echo $_SESSION['data']['standard'] ?? 'N/A'; ?></p>
        <p><strong>Database Connection:</strong> <?php echo $conn ? '✅ Connected' : '❌ Failed'; ?></p>
    </div>
</body>
</html>