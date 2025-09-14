<?php
session_start();
include('actions/connect.php');

// Check if user is logged in
if (!isset($_SESSION['data'])) {
    header("Location: index.php");
    exit();
}

// Get candidate ID from URL parameter
$candidate_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($candidate_id <= 0) {
    echo '<script>
        alert("Invalid candidate ID");
        window.location.href = "partials/dashboard.php";
    </script>';
    exit();
}

// Fetch candidate data
$sql = "SELECT * FROM `userdata` WHERE `id` = '$candidate_id' AND `standard` = 'candidate'";
$result = mysqli_query($conn, $sql);

if (!$result || mysqli_num_rows($result) == 0) {
    echo '<script>
        alert("Candidate not found");
        window.location.href = "partials/dashboard.php";
    </script>';
    exit();
}

$candidate = mysqli_fetch_assoc($result);
$current_user = $_SESSION['data'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($candidate['username']); ?> - Candidate Profile</title>
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

        .profile-header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            margin-bottom: 1.5rem;
            padding: 1.5rem;
        }

        .candidate-profile-card {
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
            background: linear-gradient(45deg, var(--success-color), #34d399);
            color: white;
            border-radius: 20px;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            box-shadow: 0 2px 10px rgba(16, 185, 129, 0.3);
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
    </style>
    <script>
        function confirmVote(candidateName) {
            return confirm('Are you sure you want to vote for ' + candidateName + '?\n\nNote: You can only vote once and this action cannot be undone.');
        }
    </script>
</head>
<body>
    <div class="container my-5">
        <div class="profile-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-1"><i class="fas fa-user-tie me-2"></i>Candidate Profile</h4>
                    <small class="text-muted">View detailed candidate information</small>
                </div>
                <div>
                    <a href="partials/dashboard.php" class="back-btn"><i class="fas fa-arrow-left me-2"></i>Back to Dashboard</a>
                </div>
            </div>
        </div>

        <div class="candidate-profile-card">
            <div class="row">
                <div class="col-md-4 text-center">
                    <!-- Profile Image -->
                    <?php if (!empty($candidate['photo'])): ?>
                        <?php
                        $fileExt = strtolower(pathinfo($candidate['photo'], PATHINFO_EXTENSION));
                        $videoTypes = ['mp4', 'avi', 'mov', 'wmv', 'flv', 'mkv'];
                        $imageTypes = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'svg'];
                        $isVideo = in_array($fileExt, $videoTypes);
                        $isImage = in_array($fileExt, $imageTypes);
                        ?>
                        <?php if ($isVideo): ?>
                            <video class="profile-image-large mx-auto d-block" style="width: 200px; height: 200px; object-fit: cover;" muted controls>
                                <source src="uploads/<?php echo htmlspecialchars($candidate['photo']); ?>" type="video/<?php echo $fileExt; ?>">
                                <div class="profile-placeholder mx-auto" style="width: 200px; height: 200px; background: linear-gradient(45deg, var(--primary-color), var(--secondary-color)); color: white; border-radius: 20px; display: flex; align-items: center; justify-content: center; font-size: 36px; font-weight: bold;">
                                    <?php echo strtoupper(substr($candidate['username'], 0, 2)); ?>
                                </div>
                            </video>
                        <?php elseif ($isImage): ?>
                            <img src="uploads/<?php echo htmlspecialchars($candidate['photo']); ?>" alt="candidate image" class="profile-image-large mx-auto d-block">
                        <?php else: ?>
                            <div class="profile-placeholder mx-auto" style="width: 200px; height: 200px; background: linear-gradient(45deg, var(--primary-color), var(--secondary-color)); color: white; border-radius: 20px; display: flex; align-items: center; justify-content: center; font-size: 36px; font-weight: bold;">
                                <?php echo strtoupper(substr($candidate['username'], 0, 2)); ?>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="profile-placeholder mx-auto" style="width: 200px; height: 200px; background: linear-gradient(45deg, var(--primary-color), var(--secondary-color)); color: white; border-radius: 20px; display: flex; align-items: center; justify-content: center; font-size: 36px; font-weight: bold;">
                            <?php echo strtoupper(substr($candidate['username'], 0, 2)); ?>
                        </div>
                    <?php endif; ?>

                    <div class="mt-4">
                        <h3 class="mb-3"><?php echo htmlspecialchars($candidate['username']); ?></h3>
                        <span class="badge-custom"><i class="fas fa-crown me-2"></i>Candidate</span>
                    </div>
                </div>

                <div class="col-md-8">
                    <div class="mt-4">
                        <h5 class="mb-4"><i class="fas fa-info-circle me-2"></i>Candidate Information</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-4">
                                    <h6 class="text-muted mb-2"><i class="fas fa-user me-2"></i>Full Name</h6>
                                    <p class="h6 mb-0"><?php echo htmlspecialchars($candidate['username']); ?></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-4">
                                    <h6 class="text-muted mb-2"><i class="fas fa-phone me-2"></i>Contact</h6>
                                    <p class="mb-0"><?php echo htmlspecialchars($candidate['mobile']); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="stats-card" style="max-width: 300px; margin: 0 auto;">
                                <div class="stats-number"><?php echo htmlspecialchars($candidate['votes']); ?></div>
                                <small class="text-muted">Total Votes</small>
                            </div>
                        </div>
                    </div>

                    <!-- Vote Button (only for voters who haven't voted) -->
                    <?php if ($current_user['standard'] == 'voter' && $current_user['status'] == '0'): ?>
                        <div class="mt-5 pt-3">
                            <form action="actions/voting.php" method="POST" onsubmit="return confirmVote('<?php echo htmlspecialchars($candidate['username']); ?>')">
                                <input type="hidden" name="candidate_id" value="<?php echo htmlspecialchars($candidate['id']); ?>">
                                <button type="submit" class="vote-btn"><i class="fas fa-vote-yea me-2"></i>Vote for <?php echo htmlspecialchars($candidate['username']); ?></button>
                            </form>
                        </div>
                    <?php elseif ($current_user['standard'] == 'voter' && $current_user['status'] == '1'): ?>
                        <div class="mt-5 pt-3">
                            <span class="badge-custom"><i class="fas fa-check me-1"></i>You have already voted</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>