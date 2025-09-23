<?php
// Set timezone to India
date_default_timezone_set('Asia/Kolkata');

session_start();
include('actions/connect.php');

// Check if user is admin
if (!isset($_SESSION['data']) || $_SESSION['data']['standard'] != 'admin') {
    header("Location: index.php");
    exit();
}

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

$admins_sql = "SELECT COUNT(*) as total FROM userdata WHERE standard = 'admin'";
$admins = mysqli_fetch_assoc(mysqli_query($conn, $admins_sql))['total'];

$total_votes_sql = "SELECT SUM(votes) as total FROM userdata WHERE standard = 'candidate'";
$total_votes = mysqli_fetch_assoc(mysqli_query($conn, $total_votes_sql))['total'] ?? 0;

// Get all users for management
$users_sql = "SELECT * FROM userdata ORDER BY id DESC";
$users_result = mysqli_query($conn, $users_sql);
$users = $users_result ? mysqli_fetch_all($users_result, MYSQLI_ASSOC) : [];
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
            text-align: center;
        }

        .stats-number {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .user-table {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .user-table th {
            background: #667eea;
            color: white;
            padding: 15px;
            text-align: left;
        }

        .user-table td {
            padding: 15px;
            border-bottom: 1px solid #eee;
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
            margin: 0 2px;
            padding: 5px 10px;
            font-size: 0.8rem;
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

            <!-- Messages -->
            <?php if (isset($_SESSION['message'])): ?>
                <div class="alert alert-<?php echo $_SESSION['message_type'] == 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
                    <?php echo $_SESSION['message']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['message'], $_SESSION['message_type']); ?>
            <?php endif; ?>
                <div>
                    <a href="partials/dashboard.php" class="btn btn-outline-primary me-2">
                        <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                    </a>
                    <a href="partials/logout.php" class="btn btn-danger">
                        <i class="fas fa-sign-out-alt me-2"></i>Logout
                    </a>
                </div>
            </div>

            <!-- Server Time Display -->
            <div class="row mb-3">
                <div class="col-md-12">
                    <div class="stats-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted">Server Time</small>
                                <div id="serverTime" class="h5 mb-0"><?php echo date('Y-m-d H:i:s T'); ?></div>
                            </div>
                            <div class="text-end">
                                <small class="text-muted">Timezone</small>
                                <div class="small">Asia/Mumbai</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-2">
                    <div class="stats-card">
                        <div class="stats-number"><?php echo $total_users; ?></div>
                        <small>Total Users</small>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="stats-card">
                        <div class="stats-number"><?php echo $admins; ?></div>
                        <small>Administrators</small>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="stats-card">
                        <div class="stats-number"><?php echo $candidates; ?></div>
                        <small>Candidates</small>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="stats-card">
                        <div class="stats-number"><?php echo $voters; ?></div>
                        <small>Total Voters</small>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="stats-card">
                        <div class="stats-number"><?php echo $verified_voters; ?></div>
                        <small>Verified Voters</small>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="stats-card">
                        <div class="stats-number"><?php echo $pending_verifications; ?></div>
                        <small>Pending Reviews</small>
                    </div>
                </div>
            </div>

            <!-- Additional Stats -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="stats-card">
                        <div class="stats-number"><?php echo $total_votes; ?></div>
                        <small>Total Votes Cast</small>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="stats-card">
                        <div class="stats-number"><?php echo $verified_voters > 0 ? round(($total_votes / $verified_voters) * 100, 1) : 0; ?>%</div>
                        <small>Voting Participation Rate</small>
                    </div>
                </div>
            </div>

            <!-- Election Management Section -->
            <h3 class="mb-3"><i class="fas fa-calendar-alt me-2"></i>Election Time Session Management</h3>

            <!-- Current Elections -->
            <div class="mb-4">
                <h5>Current Elections</h5>
                <?php
                $elections_sql = "SELECT * FROM elections ORDER BY created_at DESC";
                $elections_result = mysqli_query($conn, $elections_sql);
                $elections = $elections_result ? mysqli_fetch_all($elections_result, MYSQLI_ASSOC) : [];
                ?>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Start Time</th>
                                <th>End Time</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Updated</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($elections as $election): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($election['name']); ?></td>
                                <td><?php echo htmlspecialchars(substr($election['description'], 0, 50)); ?>...</td>
                                <td><?php echo date('Y-m-d H:i', strtotime($election['start_time'])); ?></td>
                                <td><?php echo date('Y-m-d H:i', strtotime($election['end_time'])); ?></td>
                                <td>
                                    <span class="badge bg-<?php
                                        if ($election['status'] == 'active') echo 'success';
                                        elseif ($election['status'] == 'upcoming') echo 'warning';
                                        elseif ($election['status'] == 'completed') echo 'secondary';
                                        else echo 'danger';
                                    ?>">
                                        <?php echo ucfirst($election['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo $election['created_at'] ? date('M j, Y H:i', strtotime($election['created_at'])) : 'N/A'; ?></td>
                                <td><?php echo $election['updated_at'] ? date('M j, Y H:i', strtotime($election['updated_at'])) : 'N/A'; ?></td>
                                <td>
                                    <button class="btn btn-sm btn-primary" onclick="editElection(<?php echo $election['id']; ?>)">Edit</button>
                                    <button class="btn btn-sm btn-danger" onclick="deleteElection(<?php echo $election['id']; ?>)">Delete</button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Add/Edit Election Form -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 id="formTitle">Add New Election</h5>
                </div>
                <div class="card-body">
                    <form id="electionForm" method="POST" action="actions/manage_election.php">
                        <input type="hidden" name="election_id" id="electionId" value="">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="electionName" class="form-label">Election Name</label>
                                    <input type="text" class="form-control" id="electionName" name="name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="electionStatus" class="form-label">Status</label>
                                    <select class="form-select" id="electionStatus" name="status">
                                        <option value="upcoming">Upcoming</option>
                                        <option value="active">Active</option>
                                        <option value="completed">Completed</option>
                                        <option value="cancelled">Cancelled</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="electionDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="electionDescription" name="description" rows="3"></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="startTime" class="form-label">Start Time</label>
                                    <input type="datetime-local" class="form-control" id="startTime" name="start_time" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="endTime" class="form-label">End Time</label>
                                    <input type="datetime-local" class="form-control" id="endTime" name="end_time" required>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-success" id="submitBtn">Add Election</button>
                        <button type="button" class="btn btn-secondary" onclick="resetForm()">Cancel</button>
                    </form>
                </div>
            </div>

            <!-- Voters Management Section -->
            <h3 class="mb-3"><i class="fas fa-user-check me-2"></i>Voters Management</h3>

            <!-- Voters Search and Filter -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <input type="text" id="votersSearchInput" class="form-control" placeholder="Search voters...">
                </div>
                <div class="col-md-6">
                    <select id="votersStatusFilter" class="form-select">
                        <option value="">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="verified">Verified</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>
            </div>

            <!-- Voters Table -->
            <div class="user-table mb-5">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Profile</th>
                            <th>Name</th>
                            <th>Status</th>
                            <th>Age</th>
                            <th>ID Proof</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <?php if ($user['standard'] == 'voter'): ?>
                            <tr class="voter-item"
                                data-status="<?php echo $user['verification_status']; ?>"
                                data-name="<?php echo strtolower($user['username']); ?>">
                                <td>
                                    <a href="voter_profile.php?id=<?php echo $user['Id']; ?>" title="View Full Profile" style="text-decoration: none;">
                                        <?php if (!empty($user['photo'])): ?>
                                            <?php
                                            $fileExt = strtolower(pathinfo($user['photo'], PATHINFO_EXTENSION));
                                            $videoTypes = ['mp4', 'avi', 'mov', 'wmv', 'flv', 'mkv'];
                                            $imageTypes = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'svg'];
                                            $isVideo = in_array($fileExt, $videoTypes);
                                            $isImage = in_array($fileExt, $imageTypes);
                                            ?>
                                            <?php if ($isVideo): ?>
                                                <video class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover; cursor: pointer;" muted>
                                                    <source src="uploads/<?php echo htmlspecialchars($user['photo']); ?>" type="video/<?php echo $fileExt; ?>">
                                                </video>
                                            <?php elseif ($isImage): ?>
                                                <img src="uploads/<?php echo htmlspecialchars($user['photo']); ?>"
                                                     alt="Profile" class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover; cursor: pointer;">
                                            <?php else: ?>
                                                <div class="bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center"
                                                     style="width: 40px; height: 40px; font-weight: bold; font-size: 12px; cursor: pointer;">
                                                    FILE
                                                </div>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center"
                                                 style="width: 40px; height: 40px; font-weight: bold; font-size: 14px; cursor: pointer;">
                                                <?php echo strtoupper(substr($user['username'], 0, 2)); ?>
                                            </div>
                                        <?php endif; ?>
                                    </a>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($user['username']); ?></strong><br>
                                    <small class="text-muted"><?php echo htmlspecialchars($user['mobile']); ?></small>
                                </td>
                                <td>
                                    <span class="verification-badge <?php echo $user['verification_status']; ?>">
                                        <?php echo ucfirst($user['verification_status']); ?>
                                    </span>
                                </td>
                                <td><?php echo (isset($user['age']) && $user['age'] !== null) ? $user['age'] . ' yrs' : 'N/A'; ?></td>
                                <td><?php echo (!empty($user['id_proof'])) ? 'Yes' : 'No'; ?></td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <?php if ($user['verification_status'] == 'pending'): ?>
                                            <?php
                                            $verify_url = 'actions/admin_verify.php?user_id=' . $user['Id'] . '&action=verify';
                                            $reject_url = 'actions/admin_verify.php?user_id=' . $user['Id'] . '&action=reject';
                                            ?>
                                            <a href="<?php echo $verify_url; ?>" class="btn btn-success btn-sm me-1" title="Verify Voter">
                                                <i class="fas fa-check"></i> Verify
                                            </a>
                                            <a href="<?php echo $reject_url; ?>" class="btn btn-warning btn-sm me-1" title="Reject Voter">
                                                <i class="fas fa-times"></i> Reject
                                            </a>
                                        <?php elseif ($user['verification_status'] == 'rejected'): ?>
                                            <?php
                                            $reset_verification_url = 'actions/admin_verify.php?user_id=' . $user['Id'] . '&action=reset_verification';
                                            ?>
                                            <a href="<?php echo $reset_verification_url; ?>" class="btn btn-secondary btn-sm me-1" title="Reset to Pending">
                                                <i class="fas fa-undo"></i> Reset
                                            </a>
                                        <?php endif; ?>

                                        <?php if (!empty($user['id_proof'])): ?>
                                            <button class="btn btn-info btn-sm me-1" onclick="viewIdProof('<?php echo htmlspecialchars($user['id_proof']); ?>')" title="View ID Proof">
                                                <i class="fas fa-id-card"></i> View ID
                                            </button>
                                        <?php endif; ?>

                                        <?php
                                        $delete_url = 'actions/admin_verify.php?user_id=' . $user['Id'] . '&action=delete';
                                        $delete_confirm = 'return confirm(\'Are you sure you want to delete this voter?\')';
                                        ?>
                                        <a href="<?php echo $delete_url; ?>" class="btn btn-danger btn-sm" onclick="<?php echo $delete_confirm; ?>" title="Delete Voter">
                                            <i class="fas fa-trash"></i> Delete
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Candidates Management Section -->
            <h3 class="mb-3"><i class="fas fa-crown me-2"></i>Candidates Management</h3>

            <!-- Candidates Search -->
            <div class="row mb-3">
                <div class="col-md-12">
                    <input type="text" id="candidatesSearchInput" class="form-control" placeholder="Search candidates...">
                </div>
            </div>

            <!-- Candidates Table -->
            <div class="user-table">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Profile</th>
                            <th>Name</th>
                            <th>Votes</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <?php if ($user['standard'] == 'candidate'): ?>
                            <tr class="candidate-item"
                                data-name="<?php echo strtolower($user['username']); ?>">
                                <td>
                                    <?php if (!empty($user['photo'])): ?>
                                        <?php
                                        $fileExt = strtolower(pathinfo($user['photo'], PATHINFO_EXTENSION));
                                        $videoTypes = ['mp4', 'avi', 'mov', 'wmv', 'flv', 'mkv'];
                                        $imageTypes = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'svg'];
                                        $isVideo = in_array($fileExt, $videoTypes);
                                        $isImage = in_array($fileExt, $imageTypes);
                                        ?>
                                        <?php if ($isVideo): ?>
                                            <video class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover;" muted>
                                                <source src="uploads/<?php echo htmlspecialchars($user['photo']); ?>" type="video/<?php echo $fileExt; ?>">
                                            </video>
                                        <?php elseif ($isImage): ?>
                                            <img src="uploads/<?php echo htmlspecialchars($user['photo']); ?>"
                                                 alt="Profile" class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover;">
                                        <?php else: ?>
                                            <div class="bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center"
                                                 style="width: 40px; height: 40px; font-weight: bold; font-size: 12px;">
                                                FILE
                                            </div>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center"
                                             style="width: 40px; height: 40px; font-weight: bold; font-size: 14px;">
                                            <?php echo strtoupper(substr($user['username'], 0, 2)); ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($user['username']); ?></strong><br>
                                    <small class="text-muted"><?php echo htmlspecialchars($user['mobile']); ?></small>
                                </td>
                                <td>
                                    <span class="badge bg-primary"><?php echo (isset($user['votes']) && $user['votes'] !== null) ? $user['votes'] : 0; ?> votes</span>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <?php
                                        $reset_url = 'actions/admin_verify.php?user_id=' . $user['Id'] . '&action=reset_votes';
                                        $reset_confirm = 'return confirm(\'Reset all votes for ' . addslashes($user['username']) . ' to 0?\')';
                                        $demote_url = 'actions/admin_verify.php?user_id=' . $user['Id'] . '&action=demote_candidate';
                                        $demote_confirm = 'return confirm(\'Demote ' . addslashes($user['username']) . ' to regular voter? This will reset their votes.\')';
                                        $delete_url = 'actions/admin_verify.php?user_id=' . $user['Id'] . '&action=delete';
                                        $delete_confirm = 'return confirm(\'Delete candidate ' . addslashes($user['username']) . ' permanently?\')';
                                        ?>
                                        <a href="<?php echo $reset_url; ?>" class="btn btn-warning btn-sm" onclick="<?php echo $reset_confirm; ?>" title="Reset Votes">
                                            <i class="fas fa-undo"></i> Reset
                                        </a>
                                        <a href="<?php echo $demote_url; ?>" class="btn btn-secondary btn-sm" onclick="<?php echo $demote_confirm; ?>" title="Demote to Voter">
                                            <i class="fas fa-user-minus"></i> Demote
                                        </a>
                                        <a href="<?php echo $delete_url; ?>" class="btn btn-danger btn-sm" onclick="<?php echo $delete_confirm; ?>" title="Delete Candidate">
                                            <i class="fas fa-trash"></i> Delete
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
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
                    <img id="idProofImage" src="" alt="ID Proof" style="max-width: 100%; max-height: 500px;">
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Voters search and filter functionality
        document.getElementById('votersSearchInput').addEventListener('input', filterVoters);
        document.getElementById('votersStatusFilter').addEventListener('change', filterVoters);

        function filterVoters() {
            const searchTerm = document.getElementById('votersSearchInput').value.toLowerCase();
            const statusFilter = document.getElementById('votersStatusFilter').value;

            const voters = document.querySelectorAll('.voter-item');

            voters.forEach(voter => {
                const name = voter.dataset.name;
                const status = voter.dataset.status;

                const matchesSearch = name.includes(searchTerm);
                const matchesStatus = !statusFilter || status === statusFilter;

                if (matchesSearch && matchesStatus) {
                    voter.style.display = 'table-row';
                } else {
                    voter.style.display = 'none';
                }
            });
        }

        // Candidates search functionality
        document.getElementById('candidatesSearchInput').addEventListener('input', filterCandidates);

        function filterCandidates() {
            const searchTerm = document.getElementById('candidatesSearchInput').value.toLowerCase();

            const candidates = document.querySelectorAll('.candidate-item');

            candidates.forEach(candidate => {
                const name = candidate.dataset.name;

                const matchesSearch = name.includes(searchTerm);

                if (matchesSearch) {
                    candidate.style.display = 'table-row';
                } else {
                    candidate.style.display = 'none';
                }
            });
        }

        function viewIdProof(filename) {
            document.getElementById('idProofImage').src = 'uploads/' + filename;
            new bootstrap.Modal(document.getElementById('idProofModal')).show();
        }

        // Election management functions
        function editElection(id) {
            // Fetch election data and populate form
            fetch('actions/get_election.php?id=' + id)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('electionId').value = data.id;
                    document.getElementById('electionName').value = data.name;
                    document.getElementById('electionDescription').value = data.description;
                    document.getElementById('electionStatus').value = data.status;
                    document.getElementById('startTime').value = data.start_time.replace(' ', 'T');
                    document.getElementById('endTime').value = data.end_time.replace(' ', 'T');
                    document.getElementById('formTitle').textContent = 'Edit Election';
                    document.getElementById('submitBtn').textContent = 'Update Election';
                });
        }

        function deleteElection(id) {
            if (confirm('Are you sure you want to delete this election?')) {
                window.location.href = 'actions/manage_election.php?action=delete&id=' + id;
            }
        }

        function resetForm() {
            document.getElementById('electionForm').reset();
            document.getElementById('electionId').value = '';
            document.getElementById('formTitle').textContent = 'Add New Election';
            document.getElementById('submitBtn').textContent = 'Add Election';
        }

        // Update server time every second
        function updateServerTime() {
            const now = new Date();
            const timeString = now.getFullYear() + '-' +
                String(now.getMonth() + 1).padStart(2, '0') + '-' +
                String(now.getDate()).padStart(2, '0') + ' ' +
                String(now.getHours()).padStart(2, '0') + ':' +
                String(now.getMinutes()).padStart(2, '0') + ':' +
                String(now.getSeconds()).padStart(2, '0') + ' Asia/Mumbai';
            document.getElementById('serverTime').textContent = timeString;
        }

        setInterval(updateServerTime, 1000);
    </script>
</body>
</html>