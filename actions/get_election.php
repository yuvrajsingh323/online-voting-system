<?php
include('connect.php');

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "SELECT * FROM elections WHERE id = '$id'";
    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        $election = mysqli_fetch_assoc($result);
        echo json_encode($election);
    } else {
        echo json_encode(['error' => 'Election not found']);
    }
} else {
    echo json_encode(['error' => 'No election ID provided']);
}
?>