<?php
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
$users = mysqli_fetch_all($users_result, MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
        .container { background: rgba(255,255,255,0.95); margin: 20px; padding: 30px; border-radius: 15px; }
        .stats-card { background: linear-gradient(45deg, #667eea, #764ba2); color: white; padding: 20px; border-radius: 15px; text-align: center; margin-bottom: 20px; }
        .stats-number { font-size: 2.5rem; font-weight: bold; }
        table { background: white; border-radius: 10px; overflow: hidden; }
        th { background: #667eea; color: white; padding: 15px; }
        td { padding: 15px; border-bottom: 1px solid #eee; }
        .btn-action { margin: 0 2px; padding: 5px 10px; font-size: 0.8rem; }
        .verification-badge { padding: 5px 10px; border-radius: 15px; font-size: 0.8rem; font-weight: 600; }
        .verified { background: #10b981; color: white; }
        .pending { background: #f59e0b; color: white; }
        .rejected { background: #ef4444; color: white; }
    </style>
</head>
<body>
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1>Admin Dashboard</h1>
                <p class="text-muted">Online Voting System Management</p>
            </div>
            <div>
                <a href="partials/dashboard.php" class="btn btn-outline-primary me-2">Back to Dashboard</a>
                <a href="partials/logout.php" class="btn btn-danger">Logout</a>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-2"><div class="stats-card"><div class="stats-number"><?php echo $total_users; ?></div><small>Total Users</small></div></div>
            <div class="col-md-2"><div class="stats-card"><div class="stats-number"><?php echo $admins; ?></div><small>Administrators</small></div></div>
            <div class="col-md-2"><div class="stats-card"><div class="stats-number"><?php echo $candidates; ?></div><small>Candidates</small></div></div>
            <div class="col-md-2"><div class="stats-card"><div class="stats-number"><?php echo $voters; ?></div><small>Total Voters</small></div></div>
            <div class="col-md-2"><div class="stats-card"><div class="stats-number"><?php echo $verified_voters; ?></div><small>Verified Voters</small></div></div>
            <div class="col-md-2"><div class="stats-card"><div class="stats-number"><?php echo $pending_verifications; ?></div><small>Pending Reviews</small></div></div>
        </div>

        <div class="row mb-4">
            <div class="col-md-6"><div class="stats-card"><div class="stats-number"><?php echo $total_votes; ?></div><small>Total Votes Cast</small></div></div>
            <div class="col-md-6"><div class="stats-card"><div class="stats-number"><?php echo $verified_voters > 0 ? round(($total_votes / $verified_voters) * 100, 1) : 0; ?>%</div><small>Voting Participation Rate</small></div></div>
        </div>

        <h3>User Management</h3>

        <div class="row mb-3">
            <div class="col-md-6"><input type="text" id="searchInput" class="form-control" placeholder="Search users..."></div>
            <div class="col-md-3">
                <select id="statusFilter" class="form-select">
                    <option value="">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="verified">Verified</option>
                    <option value="rejected">Rejected</option>
                </select>
            </div>
            <div class="col-md-3">
                <select id="typeFilter" class="form-select">
                    <option value="">All Types</option>
                    <option value="admin">Administrators</option>
                    <option value="candidate">Candidates</option>
                    <option value="voter">Voters</option>
                </select>
            </div>
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
                            <img src="uploads/<?php echo htmlspecialchars($user['photo']); ?>" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                        <?php else: ?>
                            <div style="width: 40px; height: 40px; border-radius: 50%; background: #667eea; color: white; display: flex; align-items: center; justify-content: center; font-weight: bold;">
                                <?php echo strtoupper(substr($user['username'], 0, 2)); ?>
                            </div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <strong><?php echo htmlspecialchars($user['username']); ?></strong><br>
                        <small><?php echo htmlspecialchars($user['mobile']); ?></small>
                    </td>
                    <td>
                        <span class="badge bg-<?php echo ($user['standard'] == 'admin') ? 'danger' : (($user['standard'] == 'candidate') ? 'success' : 'primary'); ?>">
                            <?php echo ucfirst($user['standard']); ?>
                        </span>
                    </td>
                    <td>
                        <?php if ($user['standard'] == 'voter'): ?>
                            <span class="verification-badge <?php echo $user['verification_status']; ?>">
                                <?php echo ucfirst($user['verification_status']); ?>
                            </span>
                        <?php else: ?>
                            N/A
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($user['standard'] == 'candidate'): ?>
                            <?php echo $user['votes'] ?? 0; ?> votes<br><small>Auto-verified</small>
                        <?php else: ?>
                            <?php echo $user['age'] ?? 'N/A'; ?> yrs<br><small><?php echo $user['id_proof'] ? 'ID: Yes' : 'ID: No'; ?></small>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($user['standard'] == 'voter' && $user['verification_status'] == 'pending'): ?>
                            <a href="actions/admin_verify.php?user_id=<?php echo $user['id']; ?>&action=verify" class="btn btn-success btn-action">✓</a>
                            <a href="actions/admin_verify.php?user_id=<?php echo $user['id']; ?>&action=reject" class="btn btn-warning btn-action">✗</a>
                        <?php elseif ($user['standard'] == 'voter' && $user['verification_status'] == 'rejected'): ?>
                            <a href="actions/admin_verify.php?user_id=<?php echo $user['id']; ?>&action=reset_verification" class="btn btn-secondary btn-action">↺</a>
                        <?php endif; ?>

                        <?php if (!empty($user['id_proof']) && file_exists('uploads/' . $user['id_proof'])): ?>
                            <button class="btn btn-info btn-action" onclick="viewIdProof('<?php echo htmlspecialchars($user['id_proof']); ?>')">📄</button>
                        <?php endif; ?>

                        <a href="actions/admin_verify.php?user_id=<?php echo $user['id']; ?>&action=delete" class="btn btn-danger btn-action" onclick="return confirm('Delete user?')">🗑️</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="modal fade" id="idProofModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5>ID Proof Document</h5>
                    <button class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="idProofImage" src="" style="max-width: 100%; max-height: 500px;">
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