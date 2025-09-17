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

    // Check file size (25MB limit)
    if ($file_size > 25 * 1024 * 1024) {
        return ['success' => false, 'message' => 'File size too large. Maximum 25MB allowed.'];
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

// ID proof upload function
function uploadIdProof($file) {
    $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'bmp', 'tiff', 'tif', 'webp'];

    $filename = $file['name'];
    $tmp_name = $file['tmp_name'];
    $file_size = $file['size'];
    $file_error = $file['error'];

    // Check if file was uploaded
    if ($file_error !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'ID proof upload error occurred.'];
    }

    // Check file size (25MB limit for ID proofs to accommodate various document types)
    if ($file_size > 25 * 1024 * 1024) {
        return ['success' => false, 'message' => 'ID proof file size too large. Maximum 25MB allowed.'];
    }

    // Get file extension
    $file_extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

    // Check if file type is allowed
    if (!in_array($file_extension, $allowed_types)) {
        return ['success' => false, 'message' => 'ID proof file type not supported. Please upload image files (JPG, PNG, GIF, BMP, WEBP, TIFF) or documents (PDF, DOC, DOCX).'];
    }

    // Generate unique filename with ID prefix
    $unique_filename = 'id_' . uniqid() . '_' . $filename;
    $upload_path = "../uploads/" . $unique_filename;

    // Move uploaded file
    if (move_uploaded_file($tmp_name, $upload_path)) {
        return ['success' => true, 'filename' => $unique_filename];
    } else {
        return ['success' => false, 'message' => 'Failed to upload ID proof.'];
    }
}

// Calculate age from date of birth
function calculateAge($dateOfBirth) {
    $today = new DateTime();
    $birthDate = new DateTime($dateOfBirth);
    $age = $today->diff($birthDate);
    return $age->y;
}

$username = $_POST['username'];
$mobile = $_POST['mobile'];
$password = $_POST['password'];
$cpassword = $_POST['cpassword'];
$standard = $_POST['std'];
$date_of_birth = $_POST['date_of_birth'];

if ($password != $cpassword) {
    echo '<script>
        alert("Passwords do not match. Please try again.");
        window.location.href = "../partials/registration.php";
        </script>';
    exit;
}

// Validate date of birth and calculate age
if (empty($date_of_birth)) {
    echo '<script>
        alert("Date of birth is required.");
        window.location.href = "../partials/registration.php";
        </script>';
    exit;
}

$age = calculateAge($date_of_birth);

if ($age < 18) {
    echo '<script>
        alert("You must be at least 18 years old to register. Your age: ' . $age . ' years.");
        window.location.href = "../partials/registration.php";
        </script>';
    exit;
}

$photo_filename = '';
$id_proof_filename = '';

// Handle profile photo upload
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

// Handle ID proof upload (only required for voters)
$id_proof_filename = '';

if ($standard == 'voter') {
    // ID proof is required for voters
    if (!isset($_FILES['id_proof']) || $_FILES['id_proof']['error'] === UPLOAD_ERR_NO_FILE) {
        echo '<script>
            alert("ID proof is required for voter age verification.");
            window.location.href = "../partials/registration.php";
            </script>';
        exit;
    }

    $id_upload_result = uploadIdProof($_FILES['id_proof']);

    if (!$id_upload_result['success']) {
        echo '<script>
            alert("' . $id_upload_result['message'] . '");
            window.location.href = "../partials/registration.php";
            </script>';
        exit;
    }

    $id_proof_filename = $id_upload_result['filename'];
    $verification_status = 'pending'; // Voters need verification
} else {
    // Candidates don't need ID proof, auto-verify them
    $verification_status = 'verified';
}

// Secure password storage
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Insert user data with age verification fields
$sql = "INSERT INTO `userdata` (`username`, `mobile`, `password`, `standard`, `photo`, `status`, `votes`, `age`, `id_proof`, `verification_status`, `date_of_birth`)
        VALUES ('$username', '$mobile', '$hashed_password', '$standard', '$photo_filename', '0', '0', '$age', '$id_proof_filename', '$verification_status', '$date_of_birth')";

$result = mysqli_query($conn, $sql);

if ($result) {
    if ($standard == 'voter') {
        echo '<script>
            alert("Registration successful! Your ID proof will be verified within 24 hours. You can log in once verified.");
            window.location.href = "../index.php";
            </script>';
    } else {
        echo '<script>
            alert("Registration successful! You can now log in as a candidate.");
            window.location.href = "../index.php";
            </script>';
    }
} else {
    echo '<script>
        alert("Registration failed. Please try again.");
        window.location.href = "../partials/registration.php";
        </script>';
}
?>