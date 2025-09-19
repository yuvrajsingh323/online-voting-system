// Enable comprehensive error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/voting_errors.log');

<?php
session_start();

// Set session configuration for reliability
ini_set('session.gc_maxlifetime', 3600);
ini_set('session.cookie_lifetime', 3600);
ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS
ini_set('session.cookie_httponly', 1);

include('connect.php');

// Enhanced database connection check
if (!$conn) {
    $error_msg = "Database connection failed: " . mysqli_connect_error();
    error_log("VOTING ERROR: " . $error_msg);
    die($error_msg);
}

// Test database connection with a simple query
$test_query = mysqli_query($conn, "SELECT 1");
if (!$test_query) {
    $error_msg = "Database test query failed: " . mysqli_error($conn);
    error_log("VOTING ERROR: " . $error_msg);
    die($error_msg);
}

// Enhanced session validation
if (!isset($_SESSION['data']) || !isset($_SESSION['id'])) {
    error_log("VOTING ERROR: User not logged in - redirecting to login");
    header("Location: ../index.php");
    exit();
}

// Validate session data structure
if (!is_array($_SESSION['data']) || empty($_SESSION['data'])) {
    error_log("VOTING ERROR: Invalid session data structure");
    session_destroy();
    header("Location: ../index.php");
    exit();
}

// Validate required session fields
$required_fields = ['id', 'username', 'standard', 'status'];
foreach ($required_fields as $field) {
    if (!isset($_SESSION['data'][$field])) {
        error_log("VOTING ERROR: Missing required session field: $field");
        session_destroy();
        header("Location: ../index.php");
        exit();
    }
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

// Check age verification (only for voters, candidates don't need age verification)
if ($_SESSION['data']['standard'] == 'voter') {
    if (!isset($_SESSION['data']['age']) || $_SESSION['data']['age'] === NULL || empty($_SESSION['data']['age']) || $_SESSION['data']['age'] < 18) {
        $age_display = isset($_SESSION['data']['age']) && $_SESSION['data']['age'] !== NULL ? $_SESSION['data']['age'] : 'Not set';
        echo '<script>
            alert("You must be at least 18 years old to vote. Your current age: ' . $age_display . ' years.");
            window.location.href = "../partials/dashboard.php";
            </script>';
        exit();
    }
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

// Enhanced POST data validation
if (!isset($_POST['candidate_id']) || empty($_POST['candidate_id'])) {
    error_log("VOTING ERROR: Missing or empty candidate_id in POST data");
    echo '<script>
        alert("Invalid vote request: No candidate selected!");
        window.location.href = "../partials/dashboard.php";
        </script>';
    exit();
}

// Debug: Log received POST data
error_log("VOTING DEBUG: POST data received - candidate_id: " . (isset($_POST['candidate_id']) ? $_POST['candidate_id'] : 'NOT SET'));
error_log("VOTING DEBUG: Session data - id: " . (isset($_SESSION['id']) ? $_SESSION['id'] : 'NOT SET') . ", status: " . (isset($_SESSION['status']) ? $_SESSION['status'] : 'NOT SET'));

// Validate candidate_id format (should be numeric)
$candidate_id = trim($_POST['candidate_id']);
if (!is_numeric($candidate_id) || $candidate_id <= 0) {
    error_log("VOTING ERROR: Invalid candidate_id format: $candidate_id");
    echo '<script>
        alert("Invalid candidate selection!");
        window.location.href = "../partials/dashboard.php";
        </script>';
    exit();
}

$candidate_id = mysqli_real_escape_string($conn, $candidate_id);
$voter_id = mysqli_real_escape_string($conn, $_SESSION['id']);

error_log("VOTING: Processing vote - Voter: $voter_id, Candidate: $candidate_id");

// Verify candidate exists and is actually a candidate
$check_candidate = "SELECT Id, username, votes FROM `userdata` WHERE `Id` = '$candidate_id' AND `standard` = 'candidate'";
$candidate_result = mysqli_query($conn, $check_candidate);

if (!$candidate_result) {
    error_log("VOTING ERROR: Candidate query failed: " . mysqli_error($conn));
    echo '<script>
        alert("Database error: Could not verify candidate!");
        window.location.href = "../partials/dashboard.php";
        </script>';
    exit();
}

if (mysqli_num_rows($candidate_result) == 0) {
    error_log("VOTING ERROR: Candidate not found - ID: $candidate_id");
    echo '<script>
        alert("Invalid candidate selected: Candidate does not exist!");
        window.location.href = "../partials/dashboard.php";
        </script>';
    exit();
}

$candidate_data = mysqli_fetch_assoc($candidate_result);
if (!$candidate_data) {
    error_log("VOTING ERROR: Could not fetch candidate data");
    echo '<script>
        alert("Error retrieving candidate information!");
        window.location.href = "../partials/dashboard.php";
        </script>';
    exit();
}

error_log("VOTING: Candidate verified - " . $candidate_data['username'] . " (ID: " . $candidate_data['Id'] . ")");

// Debug: Log candidate data
error_log("VOTING DEBUG: Candidate data - ID: " . $candidate_data['Id'] . ", Username: " . $candidate_data['username'] . ", Votes: " . $candidate_data['votes']);

// Double-check voter hasn't voted (database level check)
$check_voter = "SELECT status, username FROM `userdata` WHERE `Id` = '$voter_id' AND `standard` = 'voter'";
$voter_result = mysqli_query($conn, $check_voter);

if (!$voter_result) {
    error_log("VOTING ERROR: Voter query failed: " . mysqli_error($conn));
    echo '<script>
        alert("Database error: Could not verify voter!");
        window.location.href = "../partials/dashboard.php";
        </script>';
    exit();
}

if (mysqli_num_rows($voter_result) == 0) {
    error_log("VOTING ERROR: Voter not found in database - ID: $voter_id");
    echo '<script>
        alert("Invalid voter: Voter record not found!");
        window.location.href = "../partials/dashboard.php";
        </script>';
    exit();
}

$voter_data = mysqli_fetch_assoc($voter_result);
if (!$voter_data) {
    error_log("VOTING ERROR: Could not fetch voter data");
    echo '<script>
        alert("Error retrieving voter information!");
        window.location.href = "../partials/dashboard.php";
        </script>';
    exit();
}

if ($voter_data['status'] == 1) {
    error_log("VOTING ERROR: Voter has already voted - " . $voter_data['username']);
    echo '<script>
        alert("You have already voted!");
        window.location.href = "../partials/dashboard.php";
        </script>';
    exit();
}

error_log("VOTING: Voter verified - " . $voter_data['username'] . " (ID: $voter_id)");
error_log("VOTING DEBUG: Voter data - ID: " . $voter_data['Id'] . ", Username: " . $voter_data['username'] . ", Status: " . $voter_data['status']);

// Begin transaction for atomic voting
if (!mysqli_autocommit($conn, FALSE)) {
    error_log("Failed to disable autocommit");
    echo '<script>
        alert("Database error: Could not start transaction");
        window.location.href = "../partials/dashboard.php";
        </script>';
    exit();
}

// Debug logging
error_log("Starting vote transaction - Voter ID: $voter_id, Candidate ID: $candidate_id");

try {
    error_log("VOTING DEBUG: About to execute candidate vote update");
    // Update candidate votes atomically (prevents race conditions)
    $update_votes = "UPDATE `userdata` SET `votes` = `votes` + 1 WHERE `Id` = '$candidate_id' AND `standard` = 'candidate'";
    $result1 = mysqli_query($conn, $update_votes);

    if (!$result1) {
        $error = "Failed to update candidate votes: " . mysqli_error($conn);
        error_log("VOTING ERROR: $error");
        throw new Exception($error);
    }

    // Check if any row was actually updated
    $affected_rows = mysqli_affected_rows($conn);
    if ($affected_rows == 0) {
        $error = "No candidate found with ID: $candidate_id";
        error_log("VOTING ERROR: $error");
        throw new Exception($error);
    }

    error_log("VOTING: Candidate votes updated successfully (+1 vote, affected: $affected_rows)");

    // Update voter status
    $update_status = "UPDATE `userdata` SET `status` = 1 WHERE `Id` = '$voter_id' AND `standard` = 'voter'";
    $result2 = mysqli_query($conn, $update_status);

    if (!$result2) {
        $error = "Failed to update voter status: " . mysqli_error($conn);
        error_log("VOTING ERROR: $error");
        throw new Exception($error);
    }

    $voter_affected = mysqli_affected_rows($conn);
    if ($voter_affected == 0) {
        $error = "No voter found with ID: $voter_id";
        error_log("VOTING ERROR: $error");
        throw new Exception($error);
    }
    error_log("VOTING: Voter status updated successfully (affected: $voter_affected)");

    // Get the new vote count for confirmation message
    $get_new_votes = "SELECT votes FROM `userdata` WHERE `Id` = '$candidate_id' AND `standard` = 'candidate'";
    $votes_result = mysqli_query($conn, $get_new_votes);

    if (!$votes_result) {
        $error = "Failed to retrieve updated vote count: " . mysqli_error($conn);
        error_log("VOTING ERROR: $error");
        throw new Exception($error);
    }

    $votes_row = mysqli_fetch_assoc($votes_result);
    if (!$votes_row) {
        $error = "Could not retrieve vote count data";
        error_log("VOTING ERROR: $error");
        throw new Exception($error);
    }

    $new_vote_count = intval($votes_row['votes']);
    error_log("VOTING: New vote count retrieved: $new_vote_count");

    // Commit transaction
    if (!mysqli_commit($conn)) {
        $error = "Failed to commit transaction: " . mysqli_error($conn);
        error_log("VOTING ERROR: $error");
        throw new Exception($error);
    }
    error_log("VOTING: Transaction committed successfully");

    // Update session status
    $_SESSION['status'] = 1;
    $_SESSION['data']['status'] = 1;
    error_log("VOTING: Session status updated to voted");

    // If the candidate who received the vote is currently logged in, update their session vote count
    if (isset($_SESSION['data']['Id']) && isset($_SESSION['data']['standard']) &&
        $_SESSION['data']['Id'] == $candidate_id && $_SESSION['data']['standard'] == 'candidate') {
        $_SESSION['data']['votes'] = $new_vote_count;
        error_log("VOTING: Candidate session vote count updated to $new_vote_count");
    }

    // Session will be written automatically at script end
    error_log("VOTING: Redirecting to dashboard");
    echo '<script>
        alert("✅ Vote cast successfully for ' . htmlspecialchars($candidate_data['username']) . '!\\n\\n📊 New vote count: ' . $new_vote_count . '\\n\\n🎉 Thank you for participating in the democratic process!");
        window.location.href = "../partials/dashboard.php";
        </script>';
    exit();

} catch (Exception $e) {
    // Rollback transaction
    if (!mysqli_rollback($conn)) {
        error_log("Failed to rollback transaction: " . mysqli_error($conn));
    }
    error_log("Transaction rolled back: " . $e->getMessage());

    echo '<script>
        alert("Error casting vote: ' . $e->getMessage() . '");
        window.location.href = "../partials/dashboard.php";
        </script>';
}

// Restore autocommit
if (!mysqli_autocommit($conn, TRUE)) {
    error_log("Failed to restore autocommit: " . mysqli_error($conn));
}
?>
