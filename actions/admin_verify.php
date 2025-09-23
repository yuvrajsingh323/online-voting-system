<?php
session_start();
include('connect.php');

// Check if user is admin
if (!isset($_SESSION['data']) || $_SESSION['data']['standard'] != 'admin') {
    header("Location: ../index.php");
    exit();
}

if ((isset($_POST['user_id']) && isset($_POST['action'])) || (isset($_GET['user_id']) && isset($_GET['action']))) {
    $user_id = isset($_POST['user_id']) ? $_POST['user_id'] : (isset($_GET['user_id']) ? $_GET['user_id'] : '');
    $action = isset($_POST['action']) ? $_POST['action'] : (isset($_GET['action']) ? $_GET['action'] : '');

    // Sanitize inputs
    $user_id = mysqli_real_escape_string($conn, trim($user_id));
    $action = trim($action);

    // Debug logging
    error_log("Admin action: '$action' for user ID: '$user_id'");
    error_log("Request method: " . $_SERVER['REQUEST_METHOD']);
    error_log("POST data: " . print_r($_POST, true));
    error_log("GET data: " . print_r($_GET, true));

    // Check database connection
    if (!$conn) {
        error_log("Database connection failed: " . mysqli_connect_error());
        $_SESSION['message'] = "Database connection failed!";
        $_SESSION['message_type'] = "error";
        header("Location: ../admin_working.php");
        exit();
    }

    // Get user information
    $user_sql = "SELECT * FROM userdata WHERE id = '$user_id'";
    error_log("Executing SQL: $user_sql");

    $user_result = mysqli_query($conn, $user_sql);

    if (!$user_result) {
        error_log("SQL Error: " . mysqli_error($conn));
        $_SESSION['message'] = "Database query failed: " . mysqli_error($conn);
        $_SESSION['message_type'] = "error";
        header("Location: ../admin_simple.php");
        exit();
    }

    if ($user_result && mysqli_num_rows($user_result) > 0) {
        $user = mysqli_fetch_assoc($user_result);
        error_log("User found: " . $user['username'] . " (ID: " . $user['id'] . ")");

        if ($action == 'verify') {
            // Update verification status to verified
            $update_sql = "UPDATE userdata SET verification_status = 'verified' WHERE id = '$user_id'";
            error_log("Executing query: $update_sql");

            if (mysqli_query($conn, $update_sql)) {
                // Verify the update worked
                $check_sql = "SELECT verification_status FROM userdata WHERE id = '$user_id'";
                $check_result = mysqli_query($conn, $check_sql);
                $check_data = mysqli_fetch_assoc($check_result);

                error_log("Verification status after update: " . $check_data['verification_status']);

                // Log the action (you can create a logs table for this)
                $log_message = "User {$user['username']} (ID: $user_id) verified by admin {$_SESSION['data']['username']}";
                error_log($log_message);

                $_SESSION['message'] = "User verified successfully! Status changed to: " . $check_data['verification_status'];
                $_SESSION['message_type'] = "success";
            } else {
                $error = mysqli_error($conn);
                error_log("Database error: $error");
                $_SESSION['message'] = "Error verifying user: " . $error;
                $_SESSION['message_type'] = "error";
            }
        } elseif ($action == 'reject') {
            // Update verification status to rejected
            $update_sql = "UPDATE userdata SET verification_status = 'rejected' WHERE id = '$user_id'";
            if (mysqli_query($conn, $update_sql)) {
                $log_message = "User {$user['username']} (ID: $user_id) rejected by admin {$_SESSION['data']['username']}";
                error_log($log_message);

                $_SESSION['message'] = "User verification rejected!";
                $_SESSION['message_type'] = "warning";
            } else {
                $_SESSION['message'] = "Error rejecting user: " . mysqli_error($conn);
                $_SESSION['message_type'] = "error";
            }
        } elseif ($action == 'delete') {
            // Delete user and their files
            $delete_sql = "DELETE FROM userdata WHERE id = '$user_id'";
            if (mysqli_query($conn, $delete_sql)) {
                // Delete uploaded files
                if (!empty($user['photo']) && file_exists('../uploads/' . $user['photo'])) {
                    unlink('../uploads/' . $user['photo']);
                }
                if (!empty($user['id_proof']) && file_exists('../uploads/' . $user['id_proof'])) {
                    unlink('../uploads/' . $user['id_proof']);
                }

                $log_message = "User {$user['username']} (ID: $user_id) deleted by admin {$_SESSION['data']['username']}";
                error_log($log_message);

                $_SESSION['message'] = "User deleted successfully!";
                $_SESSION['message_type'] = "success";
            } else {
                $_SESSION['message'] = "Error deleting user: " . mysqli_error($conn);
                $_SESSION['message_type'] = "error";
            }
        } elseif ($action == 'reset_verification') {
            // Reset verification status to pending
            $update_sql = "UPDATE userdata SET verification_status = 'pending' WHERE id = '$user_id'";
            if (mysqli_query($conn, $update_sql)) {
                $log_message = "User {$user['username']} (ID: $user_id) verification reset by admin {$_SESSION['data']['username']}";
                error_log($log_message);

                $_SESSION['message'] = "User verification reset to pending!";
                $_SESSION['message_type'] = "info";
            } else {
                $_SESSION['message'] = "Error resetting verification: " . mysqli_error($conn);
                $_SESSION['message_type'] = "error";
            }
        } elseif ($action == 'reset_votes') {
            // Reset candidate votes to 0
            $update_sql = "UPDATE userdata SET votes = 0 WHERE id = '$user_id' AND standard = 'candidate'";
            if (mysqli_query($conn, $update_sql)) {
                $log_message = "Candidate {$user['username']} (ID: $user_id) votes reset to 0 by admin {$_SESSION['data']['username']}";
                error_log($log_message);

                $_SESSION['message'] = "Candidate votes reset to 0!";
                $_SESSION['message_type'] = "success";
            } else {
                $_SESSION['message'] = "Error resetting votes: " . mysqli_error($conn);
                $_SESSION['message_type'] = "error";
            }
        } elseif ($action == 'demote_candidate') {
            // Demote candidate to voter
            $update_sql = "UPDATE userdata SET standard = 'voter', votes = 0, verification_status = 'pending' WHERE id = '$user_id' AND standard = 'candidate'";
            if (mysqli_query($conn, $update_sql)) {
                $log_message = "Candidate {$user['username']} (ID: $user_id) demoted to voter by admin {$_SESSION['data']['username']}";
                error_log($log_message);

                $_SESSION['message'] = "Candidate demoted to voter!";
                $_SESSION['message_type'] = "warning";
            } else {
                $_SESSION['message'] = "Error demoting candidate: " . mysqli_error($conn);
                $_SESSION['message_type'] = "error";
            }
        }
    } else {
        $row_count = mysqli_num_rows($user_result);
        error_log("User not found. SQL returned $row_count rows for ID: $user_id");

        // Check if user exists with different ID
        $check_all_sql = "SELECT id, username FROM userdata WHERE username LIKE '%yuvraj%'";
        $check_result = mysqli_query($conn, $check_all_sql);
        if ($check_result && mysqli_num_rows($check_result) > 0) {
            $existing_user = mysqli_fetch_assoc($check_result);
            error_log("Found user with similar name: " . $existing_user['username'] . " (ID: " . $existing_user['id'] . ")");
            $_SESSION['message'] = "User not found with ID $user_id. Found similar user: " . $existing_user['username'] . " (ID: " . $existing_user['id'] . ")";
        } else {
            $_SESSION['message'] = "User not found with ID $user_id. No users found in database.";
        }
        $_SESSION['message_type'] = "error";
    }
} else {
    // If accessed without proper POST parameters, redirect silently
    error_log("Admin verify accessed without proper parameters");
}

header("Location: ../admin_working.php");
exit();
?>