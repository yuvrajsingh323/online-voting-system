<?php
session_start();
include('connect.php');

$username = $_POST['username'];
$mobile = $_POST['mobile'];
$password = $_POST['password'];
$standard = $_POST['std'];

// Fetch user by username, mobile, and standard
$sql = "SELECT * FROM `userdata` WHERE `username`='$username' AND `mobile`='$mobile' AND `standard`='$standard'";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) > 0) {
    $user_data = mysqli_fetch_array($result);

    // Verify password using password_verify (since passwords are hashed in registration)
    if (password_verify($password, $user_data['password'])) {
        // Check verification status for voters
        if ($user_data['standard'] == 'voter') {
            if ($user_data['verification_status'] != 'verified') {
                $status_msg = $user_data['verification_status'] == 'pending' ?
                    "Your ID verification is pending. Please wait for admin approval." :
                    "Your ID verification was rejected. Please contact support.";

                echo '<script>
                    alert("' . $status_msg . '");
                    window.location.href = "../index.php";
                    </script>';
                exit;
            }
        }

        // Redirect admin users to admin dashboard
        if ($user_data['standard'] == 'admin') {
            // Fetch candidates for admin dashboard
            $sql = "SELECT username, photo, votes, id FROM `userdata` WHERE `standard`='candidate'";
            $resultcandidate = mysqli_query($conn, $sql);
            if (mysqli_num_rows($resultcandidate) > 0) {
                $candidates = mysqli_fetch_all($resultcandidate, MYSQLI_ASSOC);
                $_SESSION['candidate'] = $candidates;
            }

            $_SESSION['id'] = $user_data['id'];
            $_SESSION['status'] = $user_data['status'];
            $_SESSION['data'] = $user_data;

            echo '<script>
                window.location.href = "../admin_dashboard.php";
                </script>';
            exit;
        }

        // Password is correct, proceed with login

        // Fetch candidates
        $sql = "SELECT username, photo, votes, id FROM `userdata` WHERE `standard`='candidate'";
        $resultcandidate = mysqli_query($conn, $sql);
        if (mysqli_num_rows($resultcandidate) > 0) {
            $candidates = mysqli_fetch_all($resultcandidate, MYSQLI_ASSOC);
            $_SESSION['candidate'] = $candidates;
        }

        $_SESSION['id'] = $user_data['id'];
        $_SESSION['status'] = $user_data['status'];
        $_SESSION['data'] = $user_data;

        echo '<script>
            window.location.href = "../partials/dashboard.php";
            </script>';
    } else {
        // Password is incorrect
        echo '<script>
            alert("Login failed. Incorrect password.");
            window.location.href = "../index.php";
            </script>';
    }
} else {
    // User not found
    echo '<script>
        alert("Login failed. User not found.");
        window.location.href = "../index.php";
        </script>';
}

?>