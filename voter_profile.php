<?php
session_start();
include('actions/connect.php');

// Check if user is logged in
if (!isset($_SESSION['data'])) {
    header("Location: index.php");
    exit();
}

// Get voter ID from URL parameter
$voter_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($voter_id <= 0) {
    echo '<script>
        alert("Invalid voter ID");
        window.location.href = "partials/dashboard.php";
    </script>';
    exit();
}

// Fetch voter data
$sql = "SELECT * FROM `userdata` WHERE `id` = '$voter_id' AND `standard` = 'voter'";
$result = mysqli_query($conn, $sql);

if (!$result || mysqli_num_rows($result) == 0) {
    echo '<script>
        alert("Voter not found");
        window.location.href = "partials/dashboard.php";
    </script>';
    exit();
}

$voter = mysqli_fetch_assoc($result);
$current_user = $_SESSION['data'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($voter['username']); ?> - Voter Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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

        .profile-header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            margin-bottom: 1.5rem;
            padding: 1.5rem;
        }

        .voter-profile-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            padding: 2.5rem;
            text-align: center;
            margin-top: 1rem;
        }

        .profile-image-large {
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
            max-width: 200px;
            max-height: 200px;
            object-fit: cover;
        }

        .profile-image-large:hover {
            transform: scale(1.05);
        }

        .stats-card {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 15px;
            padding: 1.5rem;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            margin: 1rem 0;
        }

        .stats-number {
            font-size: 3rem;
            font-weight: bold;
            color: var(--primary-color);
        }

        .badge-custom {
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            color: white;
            border-radius: 20px;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            box-shadow: 0 2px 10px rgba(79, 70, 229, 0.3);
        }

        .back-btn {
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            border: none;
            border-radius: 25px;
            padding: 0.75rem 2rem;
            color: white;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .back-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(79, 70, 229, 0.4);
            background: linear-gradient(45deg, var(--secondary-color), var(--primary-color));
            color: white;
            text-decoration: none;
        }

        .verification-status {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 600;
            display: inline-block;
            margin-top: 1rem;
        }

        .verified { background: #d1fae5; color: #065f46; }
        .pending { background: #fef3c7; color: #92400e; }
        .rejected { background: #fee2e2; color: #991b1b; }
    </style>
</head>
<body>
    <div class="container my-5">
        <div class="profile-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-1"><i class="fas fa-user-check me-2"></i>Voter Profile</h4>
                    <small class="text-muted">View detailed voter information</small>
                </div>
                <div>
                    <a href="admin_simple.php" class="back-btn"><i class="fas fa-arrow-left me-2"></i>Back to Admin</a>
                </div>
            </div>
        </div>

        <div class="voter-profile-card">
            <div class="row">
                <div class="col-md-4 text-center">
                    <!-- Profile Image -->
                    <?php if (!empty($voter['photo'])): ?>
                        <?php
                        $fileExt = strtolower(pathinfo($voter['photo'], PATHINFO_EXTENSION));
                        $videoTypes = ['mp4', 'avi', 'mov', 'wmv', 'flv', 'mkv'];
                        $imageTypes = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'svg'];
                        $isVideo = in_array($fileExt, $videoTypes);
                        $isImage = in_array($fileExt, $imageTypes);
                        ?>
                        <?php if ($isVideo): ?>
                            <video class="profile-image-large mx-auto d-block" style="width: 200px; height: 200px; object-fit: cover;" muted controls>
                                <source src="uploads/<?php echo htmlspecialchars($voter['photo']); ?>" type="video/<?php echo $fileExt; ?>">
                                <div class="profile-placeholder mx-auto" style="width: 200px; height: 200px; background: linear-gradient(45deg, var(--primary-color), var(--secondary-color)); color: white; border-radius: 20px; display: flex; align-items: center; justify-content: center; font-size: 36px; font-weight: bold;">
                                    <?php echo strtoupper(substr($voter['username'], 0, 2)); ?>
                                </div>
                            </video>
                        <?php elseif ($isImage): ?>
                            <img src="uploads/<?php echo htmlspecialchars($voter['photo']); ?>" alt="voter image" class="profile-image-large mx-auto d-block">
                        <?php else: ?>
                            <div class="profile-placeholder mx-auto" style="width: 200px; height: 200px; background: linear-gradient(45deg, var(--primary-color), var(--secondary-color)); color: white; border-radius: 20px; display: flex; align-items: center; justify-content: center; font-size: 36px; font-weight: bold;">
                                <?php echo strtoupper(substr($voter['username'], 0, 2)); ?>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="profile-placeholder mx-auto" style="width: 200px; height: 200px; background: linear-gradient(45deg, var(--primary-color), var(--secondary-color)); color: white; border-radius: 20px; display: flex; align-items: center; justify-content: center; font-size: 36px; font-weight: bold;">
                            <?php echo strtoupper(substr($voter['username'], 0, 2)); ?>
                        </div>
                    <?php endif; ?>

                    <div class="mt-4">
                        <h3 class="mb-3"><?php echo htmlspecialchars($voter['username']); ?></h3>
                        <span class="badge-custom"><i class="fas fa-vote-yea me-2"></i>Voter</span>
                        <div class="verification-status <?php echo $voter['verification_status']; ?>">
                            <i class="fas fa-shield-alt me-1"></i><?php echo ucfirst($voter['verification_status']); ?>
                        </div>
                    </div>
                </div>

                <div class="col-md-8">
                    <div class="mt-4">
                        <h5 class="mb-4"><i class="fas fa-info-circle me-2"></i>Voter Information</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-4">
                                    <h6 class="text-muted mb-2"><i class="fas fa-user me-2"></i>Full Name</h6>
                                    <p class="h6 mb-0"><?php echo htmlspecialchars($voter['username']); ?></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-4">
                                    <h6 class="text-muted mb-2"><i class="fas fa-phone me-2"></i>Contact</h6>
                                    <p class="mb-0"><?php echo htmlspecialchars($voter['mobile']); ?></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-4">
                                    <h6 class="text-muted mb-2"><i class="fas fa-birthday-cake me-2"></i>Age</h6>
                                    <p class="mb-0"><?php echo isset($voter['age']) && $voter['age'] ? $voter['age'] . ' years old' : 'Not specified'; ?></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-4">
                                    <h6 class="text-muted mb-2"><i class="fas fa-id-card me-2"></i>ID Proof</h6>
                                    <p class="mb-0"><?php echo !empty($voter['id_proof']) ? 'Submitted' : 'Not submitted'; ?></p>
                                    <?php if (!empty($voter['id_proof'])): ?>
                                        <button class="btn btn-sm btn-info mt-1" onclick="viewIdProof('<?php echo htmlspecialchars($voter['id_proof']); ?>')">
                                            <i class="fas fa-eye me-1"></i>View ID
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="stats-card">
                                <div class="stats-number"><?php echo $voter['status'] == 1 ? '1' : '0'; ?></div>
                                <small class="text-muted">Votes Cast</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="stats-card">
                                <div class="stats-number">
                                    <?php
                                    $status_text = $voter['verification_status'] == 'verified' ? '✓' :
                                                 ($voter['verification_status'] == 'pending' ? '⏳' : '✗');
                                    echo $status_text;
                                    ?>
                                </div>
                                <small class="text-muted">Verification</small>
                            </div>
                        </div>
                    </div>
                </div>
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
        function viewIdProof(filename) {
            document.getElementById('idProofImage').src = 'uploads/' + filename;
            new bootstrap.Modal(document.getElementById('idProofModal')).show();
        }
    </script>
</body>
</html>