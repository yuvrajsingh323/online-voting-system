<?php
session_start();
include('connect.php');

// Check if user is logged in
if (!isset($_SESSION['data']) || !isset($_SESSION['id'])) {
    header("Location: ../index.php");
    exit();
}

// Check if user has already voted
if ($_SESSION['status'] == 1) {
    echo '<script>
        alert("You have already voted!");
        window.location.href = "../partials/dashboard.php";
        </script>';
    exit();
}

// Check if user is a voter (not a candidate)
if ($_SESSION['data']['standard'] != 'voter') {
    echo '<script>
        alert("Only voters can cast votes!");
        window.location.href = "../partials/dashboard.php";
        </script>';
    exit();
}

// Check age verification (must be 18 or older)
if (!isset($_SESSION['data']['age']) || $_SESSION['data']['age'] < 18) {
    echo '<script>
        alert("You must be at least 18 years old to vote. Your current age: ' . ($_SESSION['data']['age'] ?? 'Not verified') . ' years.");
        window.location.href = "../partials/dashboard.php";
        </script>';
    exit();
}

// Check ID verification status
if (!isset($_SESSION['data']['verification_status']) || $_SESSION['data']['verification_status'] != 'verified') {
    $status_message = $_SESSION['data']['verification_status'] ?? 'pending';
    echo '<script>
        alert("Your ID verification is ' . $status_message . '. You can only vote after your ID is verified by our administrators.");
        window.location.href = "../partials/dashboard.php";
        </script>';
    exit();
}

// Get the candidate ID from POST
if (!isset($_POST['candidate_id']) || empty($_POST['candidate_id'])) {
    echo '<script>
        alert("Invalid vote request!");
        window.location.href = "../partials/dashboard.php";
        </script>';
    exit();
}

$candidate_id = mysqli_real_escape_string($conn, $_POST['candidate_id']);
$voter_id = mysqli_real_escape_string($conn, $_SESSION['id']);

// Verify candidate exists and is actually a candidate
$check_candidate = "SELECT id, username FROM `userdata` WHERE `id` = '$candidate_id' AND `standard` = 'candidate'";
$candidate_result = mysqli_query($conn, $check_candidate);

if (!$candidate_result || mysqli_num_rows($candidate_result) == 0) {
    echo '<script>
        alert("Invalid candidate selected!");
        window.location.href = "../partials/dashboard.php";
        </script>';
    exit();
}

$candidate_data = mysqli_fetch_assoc($candidate_result);

// Double-check voter hasn't voted (database level check)
$check_voter = "SELECT status FROM `userdata` WHERE `id` = '$voter_id' AND `standard` = 'voter'";
$voter_result = mysqli_query($conn, $check_voter);

if (!$voter_result || mysqli_num_rows($voter_result) == 0) {
    echo '<script>
        alert("Invalid voter!");
        window.location.href = "../partials/dashboard.php";
        </script>';
    exit();
}

$voter_data = mysqli_fetch_assoc($voter_result);
if ($voter_data['status'] == 1) {
    echo '<script>
        alert("You have already voted!");
        window.location.href = "../partials/dashboard.php";
        </script>';
    exit();
}

// Begin transaction for atomic voting
mysqli_autocommit($conn, FALSE);

try {
    // Update candidate votes atomically (prevents race conditions)
    $update_votes = "UPDATE `userdata` SET `votes` = `votes` + 1 WHERE `id` = '$candidate_id' AND `standard` = 'candidate'";
    $result1 = mysqli_query($conn, $update_votes);

    if (!$result1) {
        throw new Exception("Failed to update candidate votes: " . mysqli_error($conn));
    }

    // Check if any row was actually updated
    if (mysqli_affected_rows($conn) == 0) {
        throw new Exception("No candidate found with ID: $candidate_id");
    }

    // Update voter status
    $update_status = "UPDATE `userdata` SET `status` = 1 WHERE `id` = '$voter_id' AND `standard` = 'voter'";
    $result2 = mysqli_query($conn, $update_status);

    if (!$result2) {
        throw new Exception("Failed to update voter status: " . mysqli_error($conn));
    }

    // Get the new vote count for confirmation message
    $get_new_votes = "SELECT votes FROM `userdata` WHERE `id` = '$candidate_id' AND `standard` = 'candidate'";
    $votes_result = mysqli_query($conn, $get_new_votes);

    if (!$votes_result) {
        throw new Exception("Failed to retrieve updated vote count: " . mysqli_error($conn));
    }

    $votes_row = mysqli_fetch_assoc($votes_result);
    $new_vote_count = $votes_row['votes'];

    // Commit transaction
    mysqli_commit($conn);

    // Update session status
    $_SESSION['status'] = 1;
    $_SESSION['data']['status'] = 1;

    // If the candidate who received the vote is currently logged in, update their session vote count
    if (isset($_SESSION['data']['id']) && isset($_SESSION['data']['standard']) &&
        $_SESSION['data']['id'] == $candidate_id && $_SESSION['data']['standard'] == 'candidate') {
        $_SESSION['data']['votes'] = $new_vote_count;
        if (isset($_SESSION['data'])) {
            $_SESSION['data']['votes'] = $new_vote_count;
        }
    }

    echo '<script>
        alert("Vote cast successfully for ' . htmlspecialchars($candidate_data['username']) . '! New vote count: ' . $new_vote_count . '");
        window.location.href = "../partials/dashboard.php";
        </script>';

} catch (Exception $e) {
    // Rollback transaction
    mysqli_rollback($conn);

    echo '<script>
        alert("Error casting vote: ' . $e->getMessage() . '");
        window.location.href = "../partials/dashboard.php";
        </script>';
}

// Restore autocommit
mysqli_autocommit($conn, TRUE);
?>
