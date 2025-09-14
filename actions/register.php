<?php
include('connect.php');

// Universal file upload function
function uploadFile($file) {
    $allowed_image_types = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'svg'];
    $allowed_video_types = ['mp4', 'avi', 'mov', 'wmv', 'flv', 'mkv'];
    $allowed_types = array_merge($allowed_image_types, $allowed_video_types);
    
    $filename = $file['name'];
    $tmp_name = $file['tmp_name'];
    $file_size = $file['size'];
    $file_error = $file['error'];
    
    // Check if file was uploaded
    if ($file_error !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'File upload error occurred.'];
    }
    
    // Check file size (10MB limit)
    if ($file_size > 10 * 1024 * 1024) {
        return ['success' => false, 'message' => 'File size too large. Maximum 10MB allowed.'];
    }
    
    // Get file extension
    $file_extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    
    // Check if file type is allowed
    if (!in_array($file_extension, $allowed_types)) {
        return ['success' => false, 'message' => 'File type not supported. Please upload images (JPG, PNG, GIF, BMP, WEBP, SVG) or videos (MP4, AVI, MOV, WMV, FLV, MKV).'];
    }
    
    // Generate unique filename to prevent conflicts
    $unique_filename = uniqid() . '_' . $filename;
    $upload_path = "../uploads/" . $unique_filename;
    
    // Move uploaded file
    if (move_uploaded_file($tmp_name, $upload_path)) {
        return ['success' => true, 'filename' => $unique_filename];
    } else {
        return ['success' => false, 'message' => 'Failed to upload file.'];
    }
}

$username = $_POST['username'];
$mobile = $_POST['mobile'];
$password = $_POST['password'];
$cpassword = $_POST['cpassword'];
$standard = $_POST['std'];

if ($password != $cpassword) {
    echo '<script>
        alert("Passwords do not match. Please try again.");
        window.location.href = "../partials/registration.php";
        </script>';
} else {
    $photo_filename = '';
    
    // Handle file upload if a file was selected
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] !== UPLOAD_ERR_NO_FILE) {
        $upload_result = uploadFile($_FILES['photo']);
        
        if (!$upload_result['success']) {
            echo '<script>
                alert("' . $upload_result['message'] . '");
                window.location.href = "../partials/registration.php";
                </script>';
            exit;
        }
        
        $photo_filename = $upload_result['filename'];
    }
    
    // Secure password storage
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $sql = "INSERT INTO `userdata` (`username`, `mobile`, `password`, `standard`, `photo`, `status`, `votes`) 
            VALUES ('$username', '$mobile', '$hashed_password', '$standard', '$photo_filename', '0', '0')";
    $result = mysqli_query($conn, $sql);
    
    if ($result) {
        echo '<script>
            alert("Registration successful. You can now log in.");
            window.location.href = "../index.php";
            </script>';
    } else {
        echo '<script>
            alert("Registration failed. Please try again.");
            window.location.href = "../partials/registration.php";
            </script>';
    }
}
?>