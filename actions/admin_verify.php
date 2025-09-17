<?php
session_start();
include('connect.php');

// Check if user is admin
if (!isset($_SESSION['data']) || $_SESSION['data']['standard'] != 'admin') {
    header("Location: ../index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = mysqli_real_escape_string($conn, $_POST['user_id']);
    $action = $_POST['action'];

    // Get user information
    $user_sql = "SELECT * FROM userdata WHERE id = '$user_id'";
    $user_result = mysqli_query($conn, $user_sql);

    if ($user_result && mysqli_num_rows($user_result) > 0) {
        $user = mysqli_fetch_assoc($user_result);

        if ($action == 'verify') {
            // Update verification status to verified
            $update_sql = "UPDATE userdata SET verification_status = 'verified' WHERE id = '$user_id'";
            if (mysqli_query($conn, $update_sql)) {
                // Log the action (you can create a logs table for this)
                $log_message = "User {$user['username']} (ID: $user_id) verified by admin {$_SESSION['data']['username']}";
                error_log($log_message);

                $_SESSION['message'] = "User verified successfully!";
                $_SESSION['message_type'] = "success";
            } else {
                $_SESSION['message'] = "Error verifying user: " . mysqli_error($conn);
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
        }
    } else {
        $_SESSION['message'] = "User not found!";
        $_SESSION['message_type'] = "error";
    }
}

header("Location: ../admin_dashboard.php");
exit();
?>