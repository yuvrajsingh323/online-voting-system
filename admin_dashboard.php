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
$total_users_result = mysqli_query($conn, $total_users_sql);
$total_users = mysqli_fetch_assoc($total_users_result)['total'];

$candidates_sql = "SELECT COUNT(*) as total FROM userdata WHERE standard = 'candidate'";
$candidates_result = mysqli_query($conn, $candidates_sql);
$candidates = mysqli_fetch_assoc($candidates_result)['total'];

$voters_sql = "SELECT COUNT(*) as total FROM userdata WHERE standard = 'voter'";
$voters_result = mysqli_query($conn, $voters_sql);
$voters = mysqli_fetch_assoc($voters_result)['total'];

$verified_voters_sql = "SELECT COUNT(*) as total FROM userdata WHERE standard = 'voter' AND verification_status = 'verified'";
$verified_voters_result = mysqli_query($conn, $verified_voters_sql);
$verified_voters = mysqli_fetch_assoc($verified_voters_result)['total'];

$pending_verifications_sql = "SELECT COUNT(*) as total FROM userdata WHERE verification_status = 'pending'";
$pending_verifications_result = mysqli_query($conn, $pending_verifications_sql);
$pending_verifications = mysqli_fetch_assoc($pending_verifications_result)['total'];

$admins_sql = "SELECT COUNT(*) as total FROM userdata WHERE standard = 'admin'";
$admins_result = mysqli_query($conn, $admins_sql);
$admins = mysqli_fetch_assoc($admins_result)['total'];

$total_votes_sql = "SELECT SUM(votes) as total FROM userdata WHERE standard = 'candidate'";
$total_votes_result = mysqli_query($conn, $total_votes_sql);
$total_votes_row = mysqli_fetch_assoc($total_votes_result);
$total_votes = $total_votes_row['total'] ?? 0;

// Get voters for management
$voters_sql = "SELECT id, username, mobile, standard, verification_status, age, id_proof, photo, status, votes, date_of_birth FROM userdata WHERE standard = 'voter' ORDER BY id DESC";
$voters_result = mysqli_query($conn, $voters_sql);
if (!$voters_result) {
    die("Database query failed: " . mysqli_error($conn));
}
$voters_data = mysqli_fetch_all($voters_result, MYSQLI_ASSOC);

// Get candidates for management
$candidates_sql = "SELECT id, username, mobile, standard, verification_status, age, id_proof, photo, status, votes, date_of_birth FROM userdata WHERE standard = 'candidate' ORDER BY id DESC";
$candidates_result = mysqli_query($conn, $candidates_sql);
if (!$candidates_result) {
    die("Database query failed: " . mysqli_error($conn));
}
$candidates_data = mysqli_fetch_all($candidates_result, MYSQLI_ASSOC);

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
                        <div class="stats-number"><?php echo $admins; ?></div>
                        <small>Administrators</small>
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
            </div>

            <!-- Additional Stats Row -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="stats-card text-center">
                        <div class="stats-number"><?php echo $total_votes; ?></div>
                        <small>Total Votes Cast</small>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="stats-card text-center">
                        <div class="stats-number"><?php echo $verified_voters > 0 ? round(($total_votes / $verified_voters) * 100, 1) : 0; ?>%</div>
                        <small>Voting Participation Rate</small>
                    </div>
                </div>
            </div>

            <!-- Voters Management Section -->
            <h3 class="section-title"><i class="fas fa-user-check me-2"></i>Voters Management</h3>

            <div class="row mb-3">
                <div class="col-md-6">
                    <input type="text" id="voterSearchInput" class="form-control" placeholder="Search voters...">
                </div>
                <div class="col-md-6">
                    <select id="voterStatusFilter" class="form-select">
                        <option value="">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="verified">Verified</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>
            </div>

            <div class="user-list" id="votersList">
                <?php if (!empty($voters_data)): ?>
                    <?php foreach ($voters_data as $user): ?>
                    <div class="user-card voter-item"
                          data-status="<?php echo $user['verification_status']; ?>"
                          data-name="<?php echo strtolower($user['username']); ?>">
                        <div class="row align-items-center">
                            <div class="col-md-2">
                                <?php if (!empty($user['photo']) && file_exists('uploads/' . $user['photo'])): ?>
                                    <?php
                                    $file_path = 'uploads/' . $user['photo'];
                                    $file_ext = strtolower(pathinfo($user['photo'], PATHINFO_EXTENSION));
                                    $is_image = in_array($file_ext, ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp']);
                                    $is_video = in_array($file_ext, ['mp4', 'avi', 'mov', 'wmv', 'flv', 'mkv']);
                                    ?>

                                    <?php if ($is_image): ?>
                                        <img src="<?php echo htmlspecialchars($file_path); ?>"
                                             alt="Profile Photo" class="rounded-circle"
                                             style="width: 50px; height: 50px; object-fit: cover; border: 2px solid #667eea;"
                                             title="Photo: <?php echo htmlspecialchars($user['photo']); ?>"
                                             onerror="showFallback(this)">
                                    <?php elseif ($is_video): ?>
                                        <video class="rounded-circle" style="width: 50px; height: 50px; object-fit: cover; border: 2px solid #667eea;" muted>
                                            <source src="<?php echo htmlspecialchars($file_path); ?>" type="video/<?php echo $file_ext; ?>">
                                        </video>
                                    <?php else: ?>
                                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center"
                                             style="width: 50px; height: 50px; font-weight: bold;">
                                            <i class="fas fa-file"></i>
                                        </div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center"
                                         style="width: 50px; height: 50px; font-weight: bold;"
                                         title="No photo uploaded">
                                        <?php echo strtoupper(substr($user['username'], 0, 2)); ?>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($user['photo'])): ?>
                                <br><small style="color: #999; font-size: 9px;">File: <?php echo file_exists('uploads/' . $user['photo']) ? 'EXISTS' : 'MISSING'; ?></small>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-3">
                                <h6 class="mb-1"><?php echo htmlspecialchars($user['username']); ?></h6>
                                <small class="text-muted"><?php echo htmlspecialchars($user['mobile']); ?></small>
                                <br><small style="color: #999;">ID: <?php echo htmlspecialchars($user['id'] ?? 'N/A'); ?></small>
                            </div>
                            <div class="col-md-2">
                                <span class="badge bg-primary">Voter</span>
                            </div>
                            <div class="col-md-2">
                                <span class="verification-badge <?php echo $user['verification_status']; ?>">
                                    <?php echo ucfirst($user['verification_status']); ?>
                                </span>
                            </div>
                            <div class="col-md-1">
                                <div>
                                    <div><?php echo $user['age'] ?? 'N/A'; ?> yrs</div>
                                    <small class="text-muted"><?php echo $user['id_proof'] ? 'ID: Yes' : 'ID: No'; ?></small>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="btn-group" role="group">
                                    <?php if ($user['verification_status'] == 'pending'): ?>
                                        <a href="actions/admin_verify.php?user_id=<?php echo urlencode($user['id']); ?>&action=verify" class="btn btn-success btn-action" title="Verify Voter" onclick="return confirm('Verify this voter?')">
                                            <i class="fas fa-check"></i> Verify
                                        </a>
                                        <a href="actions/admin_verify.php?user_id=<?php echo urlencode($user['id']); ?>&action=reject" class="btn btn-warning btn-action" title="Reject Voter" onclick="return confirm('Reject this voter?')">
                                            <i class="fas fa-times"></i> Reject
                                        </a>
                                    <?php elseif ($user['verification_status'] == 'rejected'): ?>
                                        <a href="actions/admin_verify.php?user_id=<?php echo urlencode($user['id']); ?>&action=reset_verification" class="btn btn-secondary btn-action" title="Reset to Pending" onclick="return confirm('Reset verification status?')">
                                            <i class="fas fa-undo"></i> Reset
                                        </a>
                                    <?php endif; ?>

                                    <?php if (!empty($user['id_proof'])): ?>
                                        <button class="btn btn-info btn-action" onclick="viewIdProof('<?php echo htmlspecialchars($user['id_proof']); ?>')" title="View ID Proof">
                                            <i class="fas fa-id-card"></i> View ID
                                        </button>
                                    <?php endif; ?>

                                    <a href="actions/admin_verify.php?user_id=<?php echo urlencode($user['id']); ?>&action=delete" class="btn btn-danger btn-action" title="Delete Voter" onclick="return confirm('Are you sure you want to delete this voter?')">
                                        <i class="fas fa-trash"></i> Delete
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="alert alert-info">
                        <p>No voters found.</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Candidates Management Section -->
            <h3 class="section-title mt-5"><i class="fas fa-crown me-2"></i>Candidates Management</h3>

            <div class="row mb-3">
                <div class="col-md-6">
                    <input type="text" id="candidateSearchInput" class="form-control" placeholder="Search candidates...">
                </div>
                <div class="col-md-6">
                    <select id="candidateStatusFilter" class="form-select">
                        <option value="">All Candidates</option>
                        <option value="all">Show All</option>
                    </select>
                </div>
            </div>

            <div class="user-list" id="candidatesList">
                <?php if (!empty($candidates_data)): ?>
                    <?php foreach ($candidates_data as $user): ?>
                    <div class="user-card candidate-item"
                          data-name="<?php echo strtolower($user['username']); ?>">
                        <div class="row align-items-center">
                            <div class="col-md-2">
                                <?php if (!empty($user['photo']) && file_exists('uploads/' . $user['photo'])): ?>
                                    <?php
                                    $file_path = 'uploads/' . $user['photo'];
                                    $file_ext = strtolower(pathinfo($user['photo'], PATHINFO_EXTENSION));
                                    $is_image = in_array($file_ext, ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp']);
                                    $is_video = in_array($file_ext, ['mp4', 'avi', 'mov', 'wmv', 'flv', 'mkv']);
                                    ?>

                                    <?php if ($is_image): ?>
                                        <img src="<?php echo htmlspecialchars($file_path); ?>"
                                             alt="Profile Photo" class="rounded-circle"
                                             style="width: 50px; height: 50px; object-fit: cover; border: 2px solid #667eea;"
                                             title="Photo: <?php echo htmlspecialchars($user['photo']); ?>"
                                             onerror="showFallback(this)">
                                    <?php elseif ($is_video): ?>
                                        <video class="rounded-circle" style="width: 50px; height: 50px; object-fit: cover; border: 2px solid #667eea;" muted>
                                            <source src="<?php echo htmlspecialchars($file_path); ?>" type="video/<?php echo $file_ext; ?>">
                                        </video>
                                    <?php else: ?>
                                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center"
                                             style="width: 50px; height: 50px; font-weight: bold;">
                                            <i class="fas fa-file"></i>
                                        </div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center"
                                         style="width: 50px; height: 50px; font-weight: bold;"
                                         title="No photo uploaded">
                                        <?php echo strtoupper(substr($user['username'], 0, 2)); ?>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($user['photo'])): ?>
                                <br><small style="color: #999; font-size: 9px;">File: <?php echo file_exists('uploads/' . $user['photo']) ? 'EXISTS' : 'MISSING'; ?></small>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-3">
                                <h6 class="mb-1"><?php echo htmlspecialchars($user['username']); ?></h6>
                                <small class="text-muted"><?php echo htmlspecialchars($user['mobile']); ?></small>
                            </div>
                            <div class="col-md-2">
                                <span class="badge bg-success">Candidate</span>
                            </div>
                            <div class="col-md-2">
                                <span class="text-muted">Auto-verified</span>
                            </div>
                            <div class="col-md-1">
                                <div>
                                    <div class="badge bg-info"><?php echo $user['votes'] ?? 0; ?> votes</div>
                                    <small class="text-muted d-block">Total Votes</small>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="btn-group" role="group">
                                    <a href="actions/admin_verify.php?user_id=<?php echo urlencode($user['id']); ?>&action=delete" class="btn btn-danger btn-action" title="Delete Candidate" onclick="return confirm('Are you sure you want to delete this candidate?')">
                                        <i class="fas fa-trash"></i> Delete
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="alert alert-info">
                        <p>No candidates found.</p>
                    </div>
                <?php endif; ?>
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
        // Voters search and filter functionality
        document.getElementById('voterSearchInput').addEventListener('input', filterVoters);
        document.getElementById('voterStatusFilter').addEventListener('change', filterVoters);

        function filterVoters() {
            const searchTerm = document.getElementById('voterSearchInput').value.toLowerCase();
            const statusFilter = document.getElementById('voterStatusFilter').value;

            const voters = document.querySelectorAll('.voter-item');

            voters.forEach(voter => {
                const name = voter.dataset.name;
                const status = voter.dataset.status;

                const matchesSearch = name.includes(searchTerm);
                const matchesStatus = !statusFilter || status === statusFilter;

                if (matchesSearch && matchesStatus) {
                    voter.style.display = 'block';
                } else {
                    voter.style.display = 'none';
                }
            });
        }

        // Candidates search and filter functionality
        document.getElementById('candidateSearchInput').addEventListener('input', filterCandidates);
        document.getElementById('candidateStatusFilter').addEventListener('change', filterCandidates);

        function filterCandidates() {
            const searchTerm = document.getElementById('candidateSearchInput').value.toLowerCase();
            const statusFilter = document.getElementById('candidateStatusFilter').value;

            const candidates = document.querySelectorAll('.candidate-item');

            candidates.forEach(candidate => {
                const name = candidate.dataset.name;

                const matchesSearch = name.includes(searchTerm);

                if (matchesSearch) {
                    candidate.style.display = 'block';
                } else {
                    candidate.style.display = 'none';
                }
            });
        }

        function viewIdProof(filename) {
            document.getElementById('idProofImage').src = 'uploads/' + filename;
            new bootstrap.Modal(document.getElementById('idProofModal')).show();
        }

        function showFallback(imgElement) {
            // Replace image with fallback avatar when image fails to load
            const fallback = document.createElement('div');
            fallback.className = 'bg-primary text-white rounded-circle d-flex align-items-center justify-content-center';
            fallback.style.cssText = 'width: 50px; height: 50px; font-weight: bold;';
            fallback.title = 'Photo failed to load';

            // Get username from the parent row to extract initials
            const row = imgElement.closest('.user-card');
            const username = row ? row.querySelector('h6')?.textContent || 'U' : 'U';
            fallback.innerHTML = username.substring(0, 2).toUpperCase();

            // Replace the image with fallback
            imgElement.parentNode.replaceChild(fallback, imgElement);
        }

        // Handle image load errors
        document.addEventListener('DOMContentLoaded', function() {
            const profileImages = document.querySelectorAll('img[alt="Profile Photo"]');
            profileImages.forEach(function(img) {
                img.addEventListener('error', function() {
                    showFallback(this);
                });
            });
        });
    </script>
</body>
</html>