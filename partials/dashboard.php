<?php
session_start();
include('../actions/connect.php');

$data = isset($_SESSION['data']) ? $_SESSION['data'] : null;

// Redirect to login if no session data exists
if (!$data) {
    header("Location: ../");
    exit();
}

// Get pending verifications count for admin stats
$pending_verifications_sql = "SELECT COUNT(*) as total FROM userdata WHERE verification_status = 'pending'";
$pending_result = mysqli_query($conn, $pending_verifications_sql);
$pending_row = mysqli_fetch_assoc($pending_result);
$pending_verifications = $pending_row['total'];

// Note: Database schema should be properly set up using database_setup.php
// ALTER TABLE calls removed for performance - run database_setup.php if schema issues occur

// Always fetch fresh candidate data to avoid session issues
$sql = "SELECT username, photo, Id, votes FROM `userdata` WHERE `standard`='candidate'";
$resultcandidate = mysqli_query($conn, $sql);
$candidates = [];

if ($resultcandidate && mysqli_num_rows($resultcandidate) > 0) {
    while ($row = mysqli_fetch_assoc($resultcandidate)) {
        // Debug: Show raw data
        // echo "<!-- Raw candidate data: " . print_r($row, true) . " -->";

        // Ensure all required keys exist
        $row['Id'] = isset($row['Id']) ? $row['Id'] : 0;
        $row['username'] = isset($row['username']) ? $row['username'] : 'Unknown';
        $row['photo'] = isset($row['photo']) ? $row['photo'] : '';
        $row['votes'] = isset($row['votes']) ? intval($row['votes']) : 0;
        $candidates[] = $row;
    }
}

// For candidates, fetch their current vote count from database to ensure accuracy
if ($data['standard'] == 'candidate' && isset($data['Id']) && !empty($data['Id'])) {
    $user_id = mysqli_real_escape_string($conn, $data['Id']);
    $current_user_sql = "SELECT votes FROM `userdata` WHERE `Id` = '$user_id' AND `standard` = 'candidate'";
    $current_user_result = mysqli_query($conn, $current_user_sql);

    if ($current_user_result && mysqli_num_rows($current_user_result) > 0) {
        $current_user_data = mysqli_fetch_assoc($current_user_result);
        $fresh_vote_count = intval($current_user_data['votes']);
        $data['votes'] = $fresh_vote_count; // Update local data
        $_SESSION['data']['votes'] = $fresh_vote_count; // Update session data
    }
}

// For voters, fetch their current status from database to ensure accuracy
if ($data['standard'] == 'voter' && isset($data['Id']) && !empty($data['Id'])) {
    $user_id = mysqli_real_escape_string($conn, $data['Id']);
    $current_user_sql = "SELECT status, age, verification_status FROM `userdata` WHERE `Id` = '$user_id' AND `standard` = 'voter'";
    $current_user_result = mysqli_query($conn, $current_user_sql);

    if ($current_user_result && mysqli_num_rows($current_user_result) > 0) {
        $current_user_data = mysqli_fetch_assoc($current_user_result);
        $fresh_status = intval($current_user_data['status']);
        $fresh_age = $current_user_data['age'];
        $fresh_verification = $current_user_data['verification_status'];

        // Update local data
        $data['status'] = $fresh_status;
        $data['age'] = $fresh_age;
        $data['verification_status'] = $fresh_verification;

        // Update session data
        $_SESSION['status'] = $fresh_status;
        $_SESSION['data']['status'] = $fresh_status;
        $_SESSION['data']['age'] = $fresh_age;
        $_SESSION['data']['verification_status'] = $fresh_verification;
    }
}

// Debug output - remove after testing
// echo "<pre>User photo: '" . $data['photo'] . "'</pre>";
// echo "<pre>Full path: '../uploads/" . $data['photo'] . "'</pre>";
// echo "<pre>File exists: " . (file_exists("../uploads/" . $data['photo']) ? "YES" : "NO") . "</pre>";
// if (!empty($candidates)) {
//     echo "<pre>Candidate photo: '" . $candidates[0]['photo'] . "'</pre>";
//     echo "<pre>Candidate file exists: " . (file_exists("../uploads/" . $candidates[0]['photo']) ? "YES" : "NO") . "</pre>";
// }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Voting System - Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4f46e5;
            --secondary-color: #06b6d4;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --dark-color: #1f2937;
            --light-color: #f8fafc;
        }
        
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .dashboard-header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
            padding: 2rem;
        }
        
        .candidate-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border: none;
            transition: all 0.3s ease;
            margin-bottom: 1.5rem;
            overflow: hidden;
        }
        
        .candidate-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
        }
        
        .profile-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            padding: 1rem;
            text-align: center;
        }
        
        .profile-image, .candidate-image {
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
        }
        
        .profile-image:hover, .candidate-image:hover {
            transform: scale(1.05);
        }
        
        .vote-btn {
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            border: none;
            border-radius: 25px;
            padding: 0.75rem 2rem;
            color: white;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(79, 70, 229, 0.3);
        }
        
        .vote-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(79, 70, 229, 0.4);
            background: linear-gradient(45deg, var(--secondary-color), var(--primary-color));
        }
        
        .badge-custom {
            background: linear-gradient(45deg, var(--success-color), #34d399);
            color: white;
            border-radius: 20px;
            padding: 0.5rem 1rem;
            font-weight: 600;
            box-shadow: 0 2px 10px rgba(16, 185, 129, 0.3);
        }
        
        .stats-card {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 15px;
            padding: 1.5rem;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 1rem;
        }
        
        .stats-number {
            font-size: 2rem;
            font-weight: bold;
            color: var(--primary-color);
        }
        
        .logout-btn {
            background: linear-gradient(45deg, var(--danger-color), #f87171);
            border: none;
            border-radius: 25px;
            padding: 0.5rem 1.5rem;
            color: white;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .logout-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(239, 68, 68, 0.4);
        }
        
        .section-title {
            color: var(--dark-color);
            font-weight: 700;
            margin-bottom: 1.5rem;
            position: relative;
            padding-left: 1rem;
        }
        
        .section-title::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 4px;
            height: 100%;
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            border-radius: 2px;
        }
        
        .candidate-name {
            color: var(--dark-color) !important;
            font-weight: 600;
        }
        
        .candidate-votes {
            color: var(--primary-color);
            font-weight: 700;
        }
    </style>
    <script>
        function confirmVote(candidateName) {
            return confirm('Are you sure you want to vote for ' + candidateName + '?\n\nNote: You can only vote once and this action cannot be undone.');
        }
        
        // Auto-refresh vote counts every 30 seconds (disabled for testing)
        // setInterval(function() {
        //     location.reload();
        // }, 30000);
    </script>
</head>
<body>
    <div class="container my-5">
        <div class="dashboard-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="mb-0"><i class="fas fa-vote-yea me-3"></i>Online Voting System</h1>
                    <p class="text-muted mb-0">Democratic participation made easy</p>
                </div>
                <div>
                    <a href="../"><button class="btn btn-outline-primary me-2"><i class="fas fa-arrow-left me-2"></i>Back</button></a>
                    <a href="../partials/logout.php"><button class="logout-btn"><i class="fas fa-sign-out-alt me-2"></i>Logout</button></a>
                </div>
            </div>
        </div>
        <div class="row my-5">
            <div class="col-md-5">
                <div class="profile-card">
                    <h5 class="section-title text-center"><i class="fas fa-id-card me-2"></i>Profile Information</h5>
                    
                    <!-- Account Type Badge -->
                    <div class="mb-3">
                        <?php if ($data['standard'] == 'admin'): ?>
                            <span class="badge" style="background: linear-gradient(45deg, #dc3545, #c82333); color: white; border-radius: 20px; padding: 0.75rem 1.5rem; font-size: 0.9rem; font-weight: 600;"><i class="fas fa-cog me-2"></i>ADMINISTRATOR ACCOUNT</span>
                        <?php elseif ($data['standard'] == 'candidate'): ?>
                            <span class="badge" style="background: linear-gradient(45deg, var(--success-color), #34d399); color: white; border-radius: 20px; padding: 0.75rem 1.5rem; font-size: 0.9rem; font-weight: 600;"><i class="fas fa-crown me-2"></i>CANDIDATE ACCOUNT</span>
                        <?php else: ?>
                            <span class="badge" style="background: linear-gradient(45deg, var(--primary-color), var(--secondary-color)); color: white; border-radius: 20px; padding: 0.75rem 1.5rem; font-size: 0.9rem; font-weight: 600;"><i class="fas fa-vote-yea me-2"></i>VOTER ACCOUNT</span>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Profile Image -->
                    <div class="mb-3">
                        <?php if (!empty($data['photo'])): ?>
                            <?php 
                            $userFileExt = strtolower(pathinfo($data['photo'], PATHINFO_EXTENSION));
                            $userVideoTypes = ['mp4', 'avi', 'mov', 'wmv', 'flv', 'mkv'];
                            $userImageTypes = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'svg'];
                            $isUserVideo = in_array($userFileExt, $userVideoTypes);
                            $isUserImage = in_array($userFileExt, $userImageTypes);
                            ?>
                            <?php if ($isUserVideo): ?>
                                <video class="profile-image rounded-circle mx-auto d-block" style="width: 100px; height: 100px; object-fit: cover;" muted>
                                    <source src="../uploads/<?php echo htmlspecialchars($data['photo']); ?>" type="video/<?php echo $userFileExt; ?>">
                                    <div class="profile-placeholder mx-auto" style="width: 100px; height: 100px; background: linear-gradient(45deg, var(--primary-color), var(--secondary-color)); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 24px; font-weight: bold;">
                                        <?php echo strtoupper(substr($data['username'], 0, 2)); ?>
                                    </div>
                                </video>
                            <?php elseif ($isUserImage): ?>
                                <img src="../uploads/<?php echo htmlspecialchars($data['photo']); ?>" alt="user image" class="profile-image rounded-circle mx-auto d-block" style="width: 100px; height: 100px; object-fit: cover;">
                            <?php else: ?>
                                <div class="profile-placeholder mx-auto" style="width: 100px; height: 100px; background: linear-gradient(45deg, var(--primary-color), var(--secondary-color)); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 24px; font-weight: bold;">
                                    <?php echo strtoupper(substr($data['username'], 0, 2)); ?>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="profile-placeholder mx-auto" style="width: 100px; height: 100px; background: linear-gradient(45deg, var(--primary-color), var(--secondary-color)); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 24px; font-weight: bold;">
                                <?php echo strtoupper(substr($data['username'], 0, 2)); ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Profile Details -->
                    <div class="profile-details">
                        <div class="mb-2">
                            <h6 class="text-muted mb-1"><i class="fas fa-user me-2"></i>Name</h6>
                            <p class="h6 mb-0"><?php echo isset($data['username']) ? htmlspecialchars($data['username']) : 'N/A'; ?></p>
                        </div>
                        
                        <div class="mb-2">
                            <h6 class="text-muted mb-1"><i class="fas fa-phone me-2"></i>Mobile</h6>
                            <p class="mb-0"><?php echo isset($data['mobile']) ? htmlspecialchars($data['mobile']) : 'N/A'; ?></p>
                        </div>
                        
                        <div class="mb-2">
                            <h6 class="text-muted mb-3"><i class="fas fa-info-circle me-2"></i>Status</h6>
                            <?php
                            if ($data['standard'] == 'admin') {
                                echo '<span class="badge" style="background: linear-gradient(45deg, #dc3545, #c82333); color: white; border-radius: 20px; padding: 0.5rem 1rem;"><i class="fas fa-cog me-1"></i>Administrator</span>';
                            } elseif ($data['standard'] == 'candidate') {
                                echo '<span class="badge-custom"><i class="fas fa-crown me-1"></i>Candidate</span>';
                            } else {
                                if ($data['status'] == '1') {
                                    echo '<span class="badge-custom"><i class="fas fa-check me-1"></i>Voted</span>';
                                } else {
                                    echo '<span class="badge" style="background: linear-gradient(45deg, var(--warning-color), #fbbf24); color: white; border-radius: 20px; padding: 0.5rem 1rem;"><i class="fas fa-clock me-1"></i>Not Voted</span>';
                                }
                            }
                            ?>
                        </div>

                        <!-- Age and Verification Status for Voters -->
                        <?php if ($data['standard'] == 'voter'): ?>
                        <div class="mb-2">
                            <h6 class="text-muted mb-1"><i class="fas fa-birthday-cake me-2"></i>Age</h6>
                            <p class="mb-0"><?php echo (isset($data['age']) && $data['age'] !== NULL) ? htmlspecialchars($data['age']) . ' years old' : 'Not applicable (Candidate)'; ?></p>
                        </div>

                        <div class="mb-2">
                            <h6 class="text-muted mb-1"><i class="fas fa-shield-alt me-2"></i>Verification Status</h6>
                            <?php
                            $verification_status = isset($data['verification_status']) ? $data['verification_status'] : 'pending';
                            switch ($verification_status) {
                                case 'verified':
                                    echo '<span class="badge" style="background: linear-gradient(45deg, var(--success-color), #34d399); color: white; border-radius: 20px; padding: 0.5rem 1rem;"><i class="fas fa-check-circle me-1"></i>Verified</span>';
                                    break;
                                case 'rejected':
                                    echo '<span class="badge" style="background: linear-gradient(45deg, var(--danger-color), #f87171); color: white; border-radius: 20px; padding: 0.5rem 1rem;"><i class="fas fa-times-circle me-1"></i>Rejected</span>';
                                    break;
                                default:
                                    echo '<span class="badge" style="background: linear-gradient(45deg, var(--warning-color), #fbbf24); color: white; border-radius: 20px; padding: 0.5rem 1rem;"><i class="fas fa-clock me-1"></i>Pending Verification</span>';
                                    break;
                            }
                            ?>
                        </div>
                        <?php endif; ?>

                        <?php if ($data['standard'] == 'admin'): ?>
                        <div class="stats-card mt-2" style="padding: 1rem;">
                            <h6 class="text-muted mb-2"><i class="fas fa-chart-pie me-2"></i>System Overview</h6>
                            <div class="row text-center">
                                <div class="col-6">
                                    <div class="stats-number" style="font-size: 1.5rem;"><?php echo count($candidates); ?></div>
                                    <small class="text-muted">Total Users</small>
                                </div>
                                <div class="col-6">
                                    <div class="stats-number" style="font-size: 1.5rem;"><?php echo $pending_verifications ?? 0; ?></div>
                                    <small class="text-muted">Pending Reviews</small>
                                </div>
                            </div>
                        </div>
                        <?php elseif ($data['standard'] == 'voter'): ?>
                        <div class="stats-card mt-2" style="padding: 1rem;">
                            <h6 class="text-muted mb-2"><i class="fas fa-chart-pie me-2"></i>Quick Stats</h6>
                            <div class="row text-center">
                                <div class="col-6">
                                    <div class="stats-number" style="font-size: 1.5rem;"><?php echo count($candidates); ?></div>
                                    <small class="text-muted">Candidates</small>
                                </div>
                                <div class="col-6">
                                    <div class="stats-number" style="font-size: 1.5rem;"><?php echo $data['status'] == 1 ? '1' : '0'; ?></div>
                                    <small class="text-muted">Votes Cast</small>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-md-7">
                <!-- candidates -->
                <?php if ($data['standard'] == 'candidate'): ?>
                    <!-- Show current user's candidate info prominently -->
                    <div class="candidate-card p-4">
                        <h4 class="section-title"><i class="fas fa-user-tie me-2"></i>Your Candidate Profile</h4>
                        <div class="row">
                            <div class="col-md-4 text-center">
                                <?php if (!empty($data['photo'])): ?>
                                    <?php 
                                    $fileExt = strtolower(pathinfo($data['photo'], PATHINFO_EXTENSION));
                                    $videoTypes = ['mp4', 'avi', 'mov', 'wmv', 'flv', 'mkv'];
                                    $imageTypes = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'svg'];
                                    $isVideo = in_array($fileExt, $videoTypes);
                                    $isImage = in_array($fileExt, $imageTypes);
                                    ?>
                                    <?php if ($isVideo): ?>
                                        <video class="candidate-image" style="width: 120px; height: 120px; object-fit: cover;" muted>
                                            <source src="../uploads/<?php echo htmlspecialchars($data['photo']); ?>" type="video/<?php echo $fileExt; ?>">
                                            <div class="profile-placeholder" style="width: 120px; height: 120px; background: linear-gradient(45deg, var(--success-color), #34d399); color: white; border-radius: 15px; display: flex; align-items: center; justify-content: center; font-size: 28px; font-weight: bold;">
                                                <?php echo strtoupper(substr($data['username'], 0, 2)); ?>
                                            </div>
                                        </video>
                                    <?php elseif ($isImage): ?>
                                        <img src="../uploads/<?php echo htmlspecialchars($data['photo']); ?>" alt="your candidate image" class="candidate-image" style="width: 120px; height: 120px; object-fit: cover;">
                                    <?php else: ?>
                                        <div class="profile-placeholder" style="width: 120px; height: 120px; background: linear-gradient(45deg, var(--success-color), #34d399); color: white; border-radius: 15px; display: flex; align-items: center; justify-content: center; font-size: 28px; font-weight: bold;">
                                            <?php echo strtoupper(substr($data['username'], 0, 2)); ?>
                                        </div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <div style="width: 100px; height: 100px; background-color: #28a745; color: white; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 24px; font-weight: bold; border: 2px solid #ddd;">
                                        <?php echo strtoupper(substr($data['username'], 0, 2)); ?>
                                    </div>
                                <?php endif; ?>
                                <div class="mt-3">
                                    <h5 class="candidate-name mb-1"><i class="fas fa-user me-2"></i><?php echo htmlspecialchars($data['username']); ?></h5>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <span class="badge-custom"><i class="fas fa-crown me-1"></i>Candidate</span>
                                </div>
                                <div class="stats-card">
                                    <div class="stats-number"><?php echo isset($data['votes']) ? htmlspecialchars($data['votes']) : '0'; ?></div>
                                    <small class="text-muted">Total Votes</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    </div>
                    <h5 class="section-title mt-4"><i class="fas fa-users me-2"></i>All Candidates</h5>
                <?php endif; ?>
                
                <?php if (!empty($candidates)): ?>
                    <?php foreach ($candidates as $candidate): ?>
                        <div class="candidate-card p-3 <?php echo ($data['standard'] == 'candidate' && isset($candidate['Id']) && isset($data['Id']) && $candidate['Id'] == $data['Id']) ? 'border border-primary' : ''; ?>">
                            <div class="row align-items-center">
                            <div class="col-md-3 text-center">
                                <?php if (!empty($candidate['photo'])): ?>
                                    <?php 
                                    $candidateFileExt = strtolower(pathinfo($candidate['photo'], PATHINFO_EXTENSION));
                                    $candidateVideoTypes = ['mp4', 'avi', 'mov', 'wmv', 'flv', 'mkv'];
                                    $candidateImageTypes = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'svg'];
                                    $isCandidateVideo = in_array($candidateFileExt, $candidateVideoTypes);
                                    $isCandidateImage = in_array($candidateFileExt, $candidateImageTypes);
                                    ?>
                                    <?php if ($isCandidateVideo): ?>
                                        <video class="candidate-image" style="width: 80px; height: 80px; object-fit: cover;" muted>
                                            <source src="../uploads/<?php echo htmlspecialchars($candidate['photo']); ?>" type="video/<?php echo $candidateFileExt; ?>">
                                            <div class="profile-placeholder" style="width: 80px; height: 80px; background: linear-gradient(45deg, var(--primary-color), var(--secondary-color)); color: white; border-radius: 15px; display: flex; align-items: center; justify-content: center; font-size: 20px; font-weight: bold;">
                                                <?php echo strtoupper(substr($candidate['username'], 0, 2)); ?>
                                            </div>
                                        </video>
                                    <?php elseif ($isCandidateImage): ?>
                                        <img src="../uploads/<?php echo htmlspecialchars($candidate['photo']); ?>" alt="candidate image" class="candidate-image" style="width: 80px; height: 80px; object-fit: cover;">
                                    <?php else: ?>
                                        <div class="profile-placeholder" style="width: 80px; height: 80px; background: linear-gradient(45deg, var(--primary-color), var(--secondary-color)); color: white; border-radius: 15px; display: flex; align-items: center; justify-content: center; font-size: 20px; font-weight: bold;">
                                            <?php echo strtoupper(substr($candidate['username'], 0, 2)); ?>
                                        </div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <div class="profile-placeholder" style="width: 80px; height: 80px; background: linear-gradient(45deg, var(--primary-color), var(--secondary-color)); color: white; border-radius: 15px; display: flex; align-items: center; justify-content: center; font-size: 20px; font-weight: bold;">
                                        <?php echo strtoupper(substr($candidate['username'], 0, 2)); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <h6 class="candidate-name mb-2"><i class="fas fa-user me-2"></i><?php echo htmlspecialchars($candidate['username']); ?></h6>
                                <div class="candidate-votes"><i class="fas fa-chart-bar me-1"></i><?php echo isset($candidate['votes']) ? htmlspecialchars($candidate['votes']) : '0'; ?> votes</div>
                                <?php if ($data['standard'] == 'candidate' && isset($candidate['Id']) && isset($data['Id']) && $candidate['Id'] == $data['Id']): ?>
                                    <span class="badge-custom mt-1"><i class="fas fa-star me-1"></i>Your Profile</span>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-3 text-end">
                                <?php if ($data['standard'] == 'voter' && $data['status'] == 0): ?>
                                    <form action="../actions/voting.php" method="POST" onsubmit="return confirmVote('<?php echo htmlspecialchars($candidate['username']); ?>')">
                                        <input type="hidden" name="candidate_id" value="<?php echo isset($candidate['Id']) ? htmlspecialchars($candidate['Id']) : ''; ?>">
                                        <button type="submit" class="vote-btn"><i class="fas fa-vote-yea me-2"></i>Vote</button>
                                    </form>
                                <?php elseif ($data['standard'] == 'voter' && $data['status'] == 1): ?>
                                    <span class="badge-custom"><i class="fas fa-check me-1"></i>Voted</span>
                                <?php else: ?>
                                    <a href="../candidate_profile.php?id=<?php echo htmlspecialchars($candidate['Id']); ?>" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-eye me-1"></i>View Profile
                                    </a>
                                <?php endif; ?>
                            </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="alert alert-warning">
                        <p>No candidates available at the moment.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>