<?php
session_start();
include('connect.php');

// Check if user is admin
if (!isset($_SESSION['data']) || $_SESSION['data']['standard'] != 'admin') {
    header("Location: ../index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $election_id = $_POST['election_id'] ?? '';
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $status = $_POST['status'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];

    if (empty($election_id)) {
        // Add new election
        $sql = "INSERT INTO elections (name, description, status, start_time, end_time) VALUES ('$name', '$description', '$status', '$start_time', '$end_time')";
        $message = "Election added successfully!";
    } else {
        // Update existing election
        $sql = "UPDATE elections SET name='$name', description='$description', status='$status', start_time='$start_time', end_time='$end_time' WHERE id='$election_id'";
        $message = "Election updated successfully!";
    }

    if (mysqli_query($conn, $sql)) {
        $_SESSION['message'] = $message;
        $_SESSION['message_type'] = 'success';
    } else {
        $_SESSION['message'] = "Error: " . mysqli_error($conn);
        $_SESSION['message_type'] = 'error';
    }
} elseif (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "DELETE FROM elections WHERE id='$id'";

    if (mysqli_query($conn, $sql)) {
        $_SESSION['message'] = "Election deleted successfully!";
        $_SESSION['message_type'] = 'success';
    } else {
        $_SESSION['message'] = "Error: " . mysqli_error($conn);
        $_SESSION['message_type'] = 'error';
    }
}

header("Location: ../admin_simple.php");
exit();
?>