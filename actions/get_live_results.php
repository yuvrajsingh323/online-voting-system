<?php
include('connect.php');

// Check if there's an active election
$check_election = "SELECT id FROM elections WHERE status = 'active' AND NOW() BETWEEN start_time AND end_time LIMIT 1";
$election_result = mysqli_query($conn, $check_election);

if (!$election_result || mysqli_num_rows($election_result) == 0) {
    echo json_encode(['success' => false, 'message' => 'No active election']);
    exit;
}

// Get candidates and their votes
$candidates_sql = "SELECT username, votes, photo FROM userdata WHERE standard = 'candidate' ORDER BY votes DESC";
$candidates_result = mysqli_query($conn, $candidates_sql);

if (!$candidates_result) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
    exit;
}

$candidates = mysqli_fetch_all($candidates_result, MYSQLI_ASSOC);

// Calculate total votes
$total_votes = array_sum(array_column($candidates, 'votes'));

echo json_encode([
    'success' => true,
    'candidates' => $candidates,
    'total_votes' => $total_votes
]);
?>