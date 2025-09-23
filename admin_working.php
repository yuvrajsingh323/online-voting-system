<?php
error_reporting(0);
ini_set('display_errors', 0);
session_start();
include('actions/connect.php');

if (!isset($_SESSION['data']) || $_SESSION['data']['standard'] != 'admin') {
    header("Location: index.php");
    exit();
}

$total_users = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM userdata"))['total'];
$candidates = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM userdata WHERE standard = 'candidate'"))['total'];
$voters = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM userdata WHERE standard = 'voter'"))['total'];
$verified_voters = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM userdata WHERE standard = 'voter' AND verification_status = 'verified'"))['total'];
$pending_verifications = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM userdata WHERE verification_status = 'pending'"))['total'];
$admins = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM userdata WHERE standard = 'admin'"))['total'];
$total_votes = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(votes) as total FROM userdata WHERE standard = 'candidate'"))['total'] ?? 0;

$users_result = mysqli_query($conn, "SELECT * FROM userdata ORDER BY id DESC");
if (!$users_result) {
    die("Database query failed: " . mysqli_error($conn));
}
$users = mysqli_fetch_all($users_result, MYSQLI_ASSOC);

// Debug: Check if users array has data
if (!empty($users)) {
    error_log("Users found: " . count($users));
    error_log("First user keys: " . implode(', ', array_keys($users[0])));
    error_log("First user ID: " . (isset($users[0]['id']) ? $users[0]['id'] : 'NOT SET'));
} else {
    error_log("No users found in database");
}

if (empty($users)) {
    echo "<div style='background: #fff3cd; color: #856404; padding: 15px; border-radius: 5px; margin-bottom: 20px; border: 1px solid #ffeaa7;'>⚠️ No users found in database. <a href='create_admin_user.php'>Create an admin user</a> or <a href='database_setup.php'>check database setup</a>.</div>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <meta charset="UTF-8">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; font-family: Arial, sans-serif; }
        .container { background: rgba(255,255,255,0.95); margin: 20px; padding: 30px; border-radius: 15px; }
        .stats-card { background: linear-gradient(45deg, #667eea, #764ba2); color: white; padding: 20px; border-radius: 15px; text-align: center; margin-bottom: 20px; }
        .stats-number { font-size: 2.5rem; font-weight: bold; margin-bottom: 5px; }
        table { background: white; border-radius: 10px; overflow: hidden; width: 100%; }
        th { background: #667eea; color: white; padding: 15px; text-align: left; }
        td { padding: 15px; border-bottom: 1px solid #eee; }
        .btn-action { margin: 0 2px; padding: 5px 10px; font-size: 0.8rem; text-decoration: none; border: none; border-radius: 5px; cursor: pointer; }
        .verification-badge { padding: 5px 10px; border-radius: 15px; font-size: 0.8rem; font-weight: 600; }
        .verified { background: #10b981; color: white; }
        .pending { background: #f59e0b; color: white; }
        .rejected { background: #ef4444; color: white; }
        .profile-img { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; }
        .profile-placeholder { width: 40px; height: 40px; border-radius: 50%; background: #667eea; color: white; display: flex; align-items: center; justify-content: center; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
            <div>
                <h1 style="margin: 0;">Admin Dashboard</h1>
                <p style="color: #666; margin: 5px 0;">Online Voting System Management</p>
            </div>
            <div>
                <a href="partials/dashboard.php" style="background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-right: 10px;">Back to Dashboard</a>
                <a href="partials/logout.php" style="background: #dc3545; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Logout</a>
            </div>
        </div>

        <?php if (isset($_SESSION['message'])): ?>
        <div style="background: <?php echo ($_SESSION['message_type'] == 'success') ? '#d4edda' : '#f8d7da'; ?>; color: <?php echo ($_SESSION['message_type'] == 'success') ? '#155724' : '#721c24'; ?>; padding: 15px; border-radius: 5px; margin-bottom: 20px; border: 1px solid <?php echo ($_SESSION['message_type'] == 'success') ? '#c3e6cb' : '#f5c6cb'; ?>;">
            <strong><?php echo ucfirst($_SESSION['message_type']); ?>:</strong> <?php echo $_SESSION['message']; ?>
        </div>
        <?php
            unset($_SESSION['message']);
            unset($_SESSION['message_type']);
        endif; ?>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px;">
            <div class="stats-card"><div class="stats-number"><?php echo $total_users; ?></div><small>Total Users</small></div>
            <div class="stats-card"><div class="stats-number"><?php echo $admins; ?></div><small>Administrators</small></div>
            <div class="stats-card"><div class="stats-number"><?php echo $candidates; ?></div><small>Candidates</small></div>
            <div class="stats-card"><div class="stats-number"><?php echo $voters; ?></div><small>Total Voters</small></div>
            <div class="stats-card"><div class="stats-number"><?php echo $verified_voters; ?></div><small>Verified Voters</small></div>
            <div class="stats-card"><div class="stats-number"><?php echo $pending_verifications; ?></div><small>Pending Reviews</small></div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 30px;">
            <div class="stats-card"><div class="stats-number"><?php echo $total_votes; ?></div><small>Total Votes Cast</small></div>
            <div class="stats-card"><div class="stats-number"><?php echo $verified_voters > 0 ? round(($total_votes / $verified_voters) * 100, 1) : 0; ?>%</div><small>Voting Participation Rate</small></div>
        </div>

        <h3 style="margin-bottom: 20px;">User Management</h3>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 20px;">
            <input type="text" id="searchInput" class="form-control" placeholder="Search users..." style="padding: 10px;">
            <select id="statusFilter" class="form-select" style="padding: 10px;">
                <option value="">All Status</option>
                <option value="pending">Pending</option>
                <option value="verified">Verified</option>
                <option value="rejected">Rejected</option>
            </select>
            <select id="typeFilter" class="form-select" style="padding: 10px;">
                <option value="">All Types</option>
                <option value="admin">Administrators</option>
                <option value="candidate">Candidates</option>
                <option value="voter">Voters</option>
            </select>
        </div>

        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Profile</th>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Details</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr class="user-item" data-status="<?php echo $user['verification_status']; ?>" data-type="<?php echo $user['standard']; ?>" data-name="<?php echo strtolower($user['username']); ?>">
                    <td>
                        <?php if (!empty($user['photo']) && file_exists('uploads/' . $user['photo'])): ?>
                            <img src="uploads/<?php echo htmlspecialchars($user['photo']); ?>" class="profile-img">
                        <?php else: ?>
                            <div class="profile-placeholder">
                                <?php echo strtoupper(substr($user['username'], 0, 2)); ?>
                            </div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <strong><?php echo htmlspecialchars($user['username']); ?></strong><br>
                        <small style="color: #666;"><?php echo htmlspecialchars($user['mobile']); ?></small>
                        <?php if ($user['standard'] == 'voter'): ?>
                        <br><small style="color: #999;">ID: <?php echo isset($user['id']) ? $user['id'] : 'N/A'; ?></small>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="badge bg-<?php echo ($user['standard'] == 'admin') ? 'danger' : (($user['standard'] == 'candidate') ? 'success' : 'primary'); ?>" style="padding: 5px 10px; border-radius: 15px;">
                            <?php echo ucfirst($user['standard']); ?>
                        </span>
                    </td>
                    <td>
                        <?php if ($user['standard'] == 'voter'): ?>
                            <span class="verification-badge <?php echo $user['verification_status']; ?>">
                                <?php echo ucfirst($user['verification_status']); ?>
                            </span>
                        <?php else: ?>
                            <span style="color: #666;">N/A</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($user['standard'] == 'candidate'): ?>
                            <div><?php echo $user['votes'] ?? 0; ?> votes</div>
                            <small style="color: #666;">Auto-verified</small>
                        <?php else: ?>
                            <div><?php echo $user['age'] ?? 'N/A'; ?> yrs</div>
                            <small style="color: #666;"><?php echo $user['id_proof'] ? 'ID: Yes' : 'ID: No'; ?></small>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php
                        $user_id = isset($user['id']) && !empty($user['id']) ? $user['id'] : '';
                        if ($user['standard'] == 'voter' && $user['verification_status'] == 'pending'): ?>
                            <a href="actions/admin_verify.php?user_id=<?php echo urlencode($user_id); ?>&action=verify" class="btn-action" style="background: #198754; color: white;">Verify</a>
                            <a href="actions/admin_verify.php?user_id=<?php echo urlencode($user_id); ?>&action=reject" class="btn-action" style="background: #ffc107; color: black;">Reject</a>
                        <?php elseif ($user['standard'] == 'voter' && $user['verification_status'] == 'rejected'): ?>
                            <a href="actions/admin_verify.php?user_id=<?php echo urlencode($user_id); ?>&action=reset_verification" class="btn-action" style="background: #6c757d; color: white;">Reset</a>
                        <?php endif; ?>

                        <?php if (!empty($user['id_proof']) && file_exists('uploads/' . $user['id_proof'])): ?>
                            <button class="btn-action" style="background: #0dcaf0; color: black;" onclick="viewIdProof('<?php echo htmlspecialchars($user['id_proof']); ?>')">View ID</button>
                        <?php endif; ?>

                        <a href="actions/admin_verify.php?user_id=<?php echo urlencode($user_id); ?>&action=delete" class="btn-action" style="background: #dc3545; color: white;" onclick="return confirm('Are you sure you want to delete this user?')">Delete</a>

                        <?php if ($user['standard'] == 'voter'): ?>
                        <br><small style="color: #999; font-size: 10px;">Debug: ID=<?php echo htmlspecialchars($user_id); ?> | Raw: <?php echo isset($user['id']) ? 'SET' : 'NOT SET'; ?> | Value: <?php echo isset($user['id']) ? htmlspecialchars($user['id']) : 'NULL'; ?> | Array: <?php echo isset($user['id']) ? 'HAS_ID_KEY' : 'NO_ID_KEY'; ?></small>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="modal fade" id="idProofModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">ID Proof Document</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="idProofImage" src="" alt="ID Proof" style="max-width: 100%; max-height: 500px;">
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('searchInput').addEventListener('input', filterUsers);
        document.getElementById('statusFilter').addEventListener('change', filterUsers);
        document.getElementById('typeFilter').addEventListener('change', filterUsers);

        function filterUsers() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const statusFilter = document.getElementById('statusFilter').value;
            const typeFilter = document.getElementById('typeFilter').value;

            document.querySelectorAll('.user-item').forEach(user => {
                const name = user.dataset.name;
                const status = user.dataset.status;
                const type = user.dataset.type;

                const matchesSearch = name.includes(searchTerm);
                const matchesStatus = !statusFilter || status === statusFilter;
                const matchesType = !typeFilter || type === typeFilter;

                user.style.display = (matchesSearch && matchesStatus && matchesType) ? 'table-row' : 'none';
            });
        }

        function viewIdProof(filename) {
            document.getElementById('idProofImage').src = 'uploads/' + filename;
            new bootstrap.Modal(document.getElementById('idProofModal')).show();
        }
    </script>
</body>
</html>