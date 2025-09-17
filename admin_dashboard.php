<?php
session_start();
include('actions/connect.php');

// Check if user is admin (you'll need to add admin role to database)
if (!isset($_SESSION['data']) || $_SESSION['data']['standard'] != 'admin') {
    header("Location: index.php");
    exit();
}

// Check for messages from admin actions
$message = isset($_SESSION['message']) ? $_SESSION['message'] : null;
$message_type = isset($_SESSION['message_type']) ? $_SESSION['message_type'] : 'info';

// Clear session messages
unset($_SESSION['message']);
unset($_SESSION['message_type']);

// Get statistics
$total_users_sql = "SELECT COUNT(*) as total FROM userdata";
$total_users = mysqli_fetch_assoc(mysqli_query($conn, $total_users_sql))['total'];

$candidates_sql = "SELECT COUNT(*) as total FROM userdata WHERE standard = 'candidate'";
$candidates = mysqli_fetch_assoc(mysqli_query($conn, $candidates_sql))['total'];

$voters_sql = "SELECT COUNT(*) as total FROM userdata WHERE standard = 'voter'";
$voters = mysqli_fetch_assoc(mysqli_query($conn, $voters_sql))['total'];

$verified_voters_sql = "SELECT COUNT(*) as total FROM userdata WHERE standard = 'voter' AND verification_status = 'verified'";
$verified_voters = mysqli_fetch_assoc(mysqli_query($conn, $verified_voters_sql))['total'];

$pending_verifications_sql = "SELECT COUNT(*) as total FROM userdata WHERE verification_status = 'pending'";
$pending_verifications = mysqli_fetch_assoc(mysqli_query($conn, $pending_verifications_sql))['total'];

$total_votes_sql = "SELECT SUM(votes) as total FROM userdata WHERE standard = 'candidate'";
$total_votes = mysqli_fetch_assoc(mysqli_query($conn, $total_votes_sql))['total'] ?? 0;

// Get all users for management
$users_sql = "SELECT * FROM userdata ORDER BY id DESC";
$users_result = mysqli_query($conn, $users_sql);
$users = mysqli_fetch_all($users_result, MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Online Voting System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .admin-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            margin: 20px;
            padding: 30px;
        }

        .stats-card {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .stats-number {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .user-card {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .verification-badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .verified { background: #10b981; color: white; }
        .pending { background: #f59e0b; color: white; }
        .rejected { background: #ef4444; color: white; }

        .btn-action {
            margin: 2px;
            padding: 5px 10px;
            font-size: 0.8rem;
        }

        .id-preview {
            max-width: 100px;
            max-height: 100px;
            border-radius: 5px;
            cursor: pointer;
            transition: transform 0.3s ease;
        }

        .id-preview:hover {
            transform: scale(1.1);
        }

        .modal-image {
            max-width: 100%;
            max-height: 500px;
        }

        .section-title {
            color: #1f2937;
            font-weight: 700;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #667eea;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="admin-container">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="mb-0"><i class="fas fa-cog me-3"></i>Admin Dashboard</h1>
                    <p class="text-muted mb-0">Online Voting System Management</p>
                </div>
                <div>
                    <a href="partials/dashboard.php" class="btn btn-outline-primary me-2">
                        <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                    </a>
                    <a href="partials/logout.php" class="btn btn-danger">
                        <i class="fas fa-sign-out-alt me-2"></i>Logout
                    </a>
                </div>
            </div>

            <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type == 'error' ? 'danger' : ($message_type == 'warning' ? 'warning' : ($message_type == 'info' ? 'info' : 'success')); ?> alert-dismissible fade show" role="alert">
                <i class="fas fa-<?php echo $message_type == 'error' ? 'exclamation-triangle' : ($message_type == 'warning' ? 'exclamation-circle' : ($message_type == 'info' ? 'info-circle' : 'check-circle')); ?> me-2"></i>
                <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-2">
                    <div class="stats-card text-center">
                        <div class="stats-number"><?php echo $total_users; ?></div>
                        <small>Total Users</small>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="stats-card text-center">
                        <div class="stats-number"><?php echo $candidates; ?></div>
                        <small>Candidates</small>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="stats-card text-center">
                        <div class="stats-number"><?php echo $voters; ?></div>
                        <small>Total Voters</small>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="stats-card text-center">
                        <div class="stats-number"><?php echo $verified_voters; ?></div>
                        <small>Verified Voters</small>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="stats-card text-center">
                        <div class="stats-number"><?php echo $pending_verifications; ?></div>
                        <small>Pending Reviews</small>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="stats-card text-center">
                        <div class="stats-number"><?php echo $total_votes; ?></div>
                        <small>Total Votes</small>
                    </div>
                </div>
            </div>

            <!-- User Management Section -->
            <h3 class="section-title"><i class="fas fa-users me-2"></i>User Management</h3>

            <div class="row mb-3">
                <div class="col-md-6">
                    <input type="text" id="searchInput" class="form-control" placeholder="Search users...">
                </div>
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
                        <option value="candidate">Candidates</option>
                        <option value="voter">Voters</option>
                    </select>
                </div>
            </div>

            <div class="user-list">
                <?php foreach ($users as $user): ?>
                <div class="user-card user-item"
                     data-status="<?php echo $user['verification_status']; ?>"
                     data-type="<?php echo $user['standard']; ?>"
                     data-name="<?php echo strtolower($user['username']); ?>">
                    <div class="row align-items-center">
                        <div class="col-md-2">
                            <?php if (!empty($user['photo'])): ?>
                                <img src="uploads/<?php echo htmlspecialchars($user['photo']); ?>"
                                     alt="Profile" class="rounded-circle" style="width: 50px; height: 50px; object-fit: cover;">
                            <?php else: ?>
                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center"
                                     style="width: 50px; height: 50px; font-weight: bold;">
                                    <?php echo strtoupper(substr($user['username'], 0, 2)); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-3">
                            <h6 class="mb-1"><?php echo htmlspecialchars($user['username']); ?></h6>
                            <small class="text-muted"><?php echo htmlspecialchars($user['mobile']); ?></small>
                        </div>
                        <div class="col-md-2">
                            <span class="badge bg-<?php echo $user['standard'] == 'candidate' ? 'success' : 'primary'; ?>">
                                <?php echo ucfirst($user['standard']); ?>
                            </span>
                        </div>
                        <div class="col-md-2">
                            <?php if ($user['standard'] == 'voter'): ?>
                                <span class="verification-badge <?php echo $user['verification_status']; ?>">
                                    <?php echo ucfirst($user['verification_status']); ?>
                                </span>
                            <?php else: ?>
                                <span class="text-muted">N/A</span>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-1">
                            <?php if ($user['standard'] == 'candidate'): ?>
                                <span class="badge bg-info"><?php echo $user['votes'] ?? 0; ?> votes</span>
                            <?php else: ?>
                                <span class="text-muted"><?php echo $user['age'] ?? 'N/A'; ?> yrs</span>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-2">
                            <div class="btn-group" role="group">
                                <?php if ($user['standard'] == 'voter' && $user['verification_status'] == 'pending'): ?>
                                    <form method="POST" action="actions/admin_verify.php" class="d-inline">
                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                        <input type="hidden" name="action" value="verify">
                                        <button type="submit" class="btn btn-success btn-action" title="Verify User">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </form>
                                    <form method="POST" action="actions/admin_verify.php" class="d-inline">
                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                        <input type="hidden" name="action" value="reject">
                                        <button type="submit" class="btn btn-warning btn-action" title="Reject User">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>

                                <?php if ($user['standard'] == 'voter' && $user['verification_status'] == 'rejected'): ?>
                                    <form method="POST" action="actions/admin_verify.php" class="d-inline">
                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                        <input type="hidden" name="action" value="reset_verification">
                                        <button type="submit" class="btn btn-secondary btn-action" title="Reset to Pending">
                                            <i class="fas fa-undo"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>

                                <?php if (!empty($user['id_proof'])): ?>
                                    <button class="btn btn-info btn-action" onclick="viewIdProof('<?php echo $user['id_proof']; ?>')" title="View ID Proof">
                                        <i class="fas fa-id-card"></i>
                                    </button>
                                <?php endif; ?>

                                <form method="POST" action="actions/admin_verify.php" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this user?')">
                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <button type="submit" class="btn btn-danger btn-action" title="Delete User">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- ID Proof Modal -->
    <div class="modal fade" id="idProofModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">ID Proof Document</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="idProofImage" src="" alt="ID Proof" class="modal-image">
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Search and filter functionality
        document.getElementById('searchInput').addEventListener('input', filterUsers);
        document.getElementById('statusFilter').addEventListener('change', filterUsers);
        document.getElementById('typeFilter').addEventListener('change', filterUsers);

        function filterUsers() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const statusFilter = document.getElementById('statusFilter').value;
            const typeFilter = document.getElementById('typeFilter').value;

            const users = document.querySelectorAll('.user-item');

            users.forEach(user => {
                const name = user.dataset.name;
                const status = user.dataset.status;
                const type = user.dataset.type;

                const matchesSearch = name.includes(searchTerm);
                const matchesStatus = !statusFilter || status === statusFilter;
                const matchesType = !typeFilter || type === typeFilter;

                if (matchesSearch && matchesStatus && matchesType) {
                    user.style.display = 'block';
                } else {
                    user.style.display = 'none';
                }
            });
        }

        function viewIdProof(filename) {
            document.getElementById('idProofImage').src = 'uploads/' + filename;
            new bootstrap.Modal(document.getElementById('idProofModal')).show();
        }
    </script>
</body>
</html>