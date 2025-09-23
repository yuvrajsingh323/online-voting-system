<?php
session_start();
include('actions/connect.php');

// Check if there's an active election
$check_election = "SELECT id, name, start_time, end_time FROM elections WHERE status = 'active' AND NOW() BETWEEN start_time AND end_time LIMIT 1";
$election_result = mysqli_query($conn, $check_election);
$election_active = false;
$election_data = null;

if ($election_result && mysqli_num_rows($election_result) > 0) {
    $election_active = true;
    $election_data = mysqli_fetch_assoc($election_result);
}

// Get candidates and their votes
$candidates_sql = "SELECT username, votes, photo FROM userdata WHERE standard = 'candidate' ORDER BY votes DESC";
$candidates_result = mysqli_query($conn, $candidates_sql);
$candidates = mysqli_fetch_all($candidates_result, MYSQLI_ASSOC);

// Calculate total votes
$total_votes = array_sum(array_column($candidates, 'votes'));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Election Results - Online Voting System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .results-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            margin: 20px;
            padding: 30px;
        }

        .election-status {
            text-align: center;
            margin-bottom: 30px;
        }

        .status-active {
            background: linear-gradient(45deg, #10b981, #34d399);
            color: white;
            padding: 20px;
            border-radius: 15px;
        }

        .status-inactive {
            background: linear-gradient(45deg, #f59e0b, #fbbf24);
            color: white;
            padding: 20px;
            border-radius: 15px;
        }

        .candidate-card {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s;
        }

        .candidate-card:hover {
            transform: translateY(-2px);
        }

        .candidate-photo {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 15px;
        }

        .progress-bar {
            height: 20px;
            border-radius: 10px;
        }

        .vote-count {
            font-weight: bold;
            color: #667eea;
        }

        .countdown {
            font-size: 1.2rem;
            font-weight: bold;
            color: #ef4444;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="results-container">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="mb-0"><i class="fas fa-chart-bar me-3"></i>Election Results</h1>
                    <p class="text-muted mb-0">Live voting results and statistics</p>
                </div>
                <div>
                    <a href="partials/dashboard.php" class="btn btn-outline-primary me-2">
                        <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                    </a>
                </div>
            </div>

            <!-- Election Status -->
            <div class="election-status">
                <?php if ($election_active): ?>
                    <div class="status-active">
                        <h3><i class="fas fa-play-circle me-2"></i>Election in Progress</h3>
                        <p class="mb-0"><?php echo htmlspecialchars($election_data['name']); ?></p>
                        <p class="mb-0">Ends: <?php echo date('M j, Y g:i A', strtotime($election_data['end_time'])); ?></p>
                        <div class="countdown" id="countdown"></div>
                    </div>
                <?php else: ?>
                    <div class="status-inactive">
                        <h3><i class="fas fa-pause-circle me-2"></i>No Active Election</h3>
                        <p class="mb-0">Results will be displayed during active voting periods</p>
                    </div>
                <?php endif; ?>
            </div>

            <?php if ($election_active): ?>
                <!-- Results Summary -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body text-center">
                                <h5>Total Votes Cast</h5>
                                <h2 class="text-primary"><?php echo $total_votes; ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body text-center">
                                <h5>Candidates</h5>
                                <h2 class="text-success"><?php echo count($candidates); ?></h2>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Candidates Results -->
                <h3 class="mb-3"><i class="fas fa-trophy me-2"></i>Candidate Standings</h3>
                <div id="results-container">
                    <?php foreach ($candidates as $index => $candidate): ?>
                        <?php
                        $percentage = $total_votes > 0 ? round(($candidate['votes'] / $total_votes) * 100, 1) : 0;
                        $rank = $index + 1;
                        ?>
                        <div class="candidate-card">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <span class="badge bg-<?php echo $rank == 1 ? 'warning' : ($rank == 2 ? 'secondary' : 'light'); ?> fs-6">
                                        #<?php echo $rank; ?>
                                    </span>
                                </div>
                                <?php if (!empty($candidate['photo'])): ?>
                                    <img src="uploads/<?php echo htmlspecialchars($candidate['photo']); ?>"
                                         alt="Candidate" class="candidate-photo">
                                <?php else: ?>
                                    <div class="candidate-photo bg-primary text-white d-flex align-items-center justify-content-center">
                                        <span class="fw-bold"><?php echo strtoupper(substr($candidate['username'], 0, 2)); ?></span>
                                    </div>
                                <?php endif; ?>
                                <div class="flex-grow-1">
                                    <h5 class="mb-1"><?php echo htmlspecialchars($candidate['username']); ?></h5>
                                    <div class="progress mb-2">
                                        <div class="progress-bar bg-primary" role="progressbar"
                                             style="width: <?php echo $percentage; ?>%"
                                             aria-valuenow="<?php echo $percentage; ?>" aria-valuemin="0" aria-valuemax="100">
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <small class="vote-count"><?php echo $candidate['votes']; ?> votes</small>
                                        <small class="text-muted"><?php echo $percentage; ?>%</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="text-center mt-4">
                    <button class="btn btn-primary" onclick="refreshResults()">
                        <i class="fas fa-sync-alt me-2"></i>Refresh Results
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        <?php if ($election_active): ?>
        // Countdown timer
        function updateCountdown() {
            const endTime = new Date('<?php echo $election_data['end_time']; ?>').getTime();
            const now = new Date().getTime();
            const distance = endTime - now;

            if (distance > 0) {
                const hours = Math.floor(distance / (1000 * 60 * 60));
                const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((distance % (1000 * 60)) / 1000);

                document.getElementById('countdown').innerHTML =
                    'Time remaining: ' + hours + 'h ' + minutes + 'm ' + seconds + 's';
            } else {
                document.getElementById('countdown').innerHTML = 'Election ended!';
                location.reload();
            }
        }

        // Update countdown every second
        setInterval(updateCountdown, 1000);
        updateCountdown();

        // Auto-refresh results every 30 seconds
        setInterval(refreshResults, 30000);

        function refreshResults() {
            fetch('actions/get_live_results.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateResultsDisplay(data.candidates, data.total_votes);
                    }
                })
                .catch(error => console.error('Error refreshing results:', error));
        }

        function updateResultsDisplay(candidates, totalVotes) {
            const container = document.getElementById('results-container');
            container.innerHTML = '';

            candidates.forEach((candidate, index) => {
                const percentage = totalVotes > 0 ? Math.round((candidate.votes / totalVotes) * 100 * 10) / 10 : 0;
                const rank = index + 1;

                const card = document.createElement('div');
                card.className = 'candidate-card';
                card.innerHTML = `
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <span class="badge bg-${rank == 1 ? 'warning' : (rank == 2 ? 'secondary' : 'light')} fs-6">
                                #${rank}
                            </span>
                        </div>
                        ${candidate.photo ?
                            `<img src="uploads/${candidate.photo}" alt="Candidate" class="candidate-photo">` :
                            `<div class="candidate-photo bg-primary text-white d-flex align-items-center justify-content-center">
                                <span class="fw-bold">${candidate.username.substring(0, 2).toUpperCase()}</span>
                            </div>`
                        }
                        <div class="flex-grow-1">
                            <h5 class="mb-1">${candidate.username}</h5>
                            <div class="progress mb-2">
                                <div class="progress-bar bg-primary" role="progressbar"
                                     style="width: ${percentage}%"
                                     aria-valuenow="${percentage}" aria-valuemin="0" aria-valuemax="100">
                                </div>
                            </div>
                            <div class="d-flex justify-content-between">
                                <small class="vote-count">${candidate.votes} votes</small>
                                <small class="text-muted">${percentage}%</small>
                            </div>
                        </div>
                    </div>
                `;
                container.appendChild(card);
            });
        }
        <?php endif; ?>
    </script>
</body>
</html>