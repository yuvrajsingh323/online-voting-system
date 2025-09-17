<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Voting System - Registration</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .registration-container {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            padding: 25px;
            margin: 10px;
            max-width: 500px;
            max-height: 90vh;
            overflow-y: auto;
        }

        .registration-header {
            text-align: center;
            margin-bottom: 30px;
            color: white;
        }

        .registration-header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }

        .registration-header h2 {
            font-size: 1.2rem;
            opacity: 0.9;
            font-weight: 300;
        }

        .form-floating {
            margin-bottom: 15px;
        }

        .form-control {
            background: rgba(255, 255, 255, 0.9);
            border: none;
            border-radius: 10px;
            padding: 15px 20px;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            background: rgba(255, 255, 255, 1);
            box-shadow: 0 0 0 0.2rem rgba(255, 255, 255, 0.25);
            transform: translateY(-2px);
        }

        .form-control[type="file"] {
            padding: 10px;
        }

        .form-select {
            background: rgba(255, 255, 255, 0.9);
            border: none;
            border-radius: 10px;
            padding: 15px 20px;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .form-select:focus {
            background: rgba(255, 255, 255, 1);
            box-shadow: 0 0 0 0.2rem rgba(255, 255, 255, 0.25);
            transform: translateY(-2px);
        }

        .file-upload {
            position: relative;
            overflow: hidden;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        .file-upload:hover {
            background: rgba(255, 255, 255, 1);
            transform: translateY(-2px);
        }

        .file-upload input[type="file"] {
            position: absolute;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }

        .file-upload-label {
            display: block;
            padding: 15px 20px;
            cursor: pointer;
            text-align: center;
            color: #666;
        }

        .file-upload-label i {
            margin-right: 10px;
            color: #667eea;
        }

        .btn-register {
            background: linear-gradient(45deg, #FF6B6B, #4ECDC4);
            border: none;
            border-radius: 25px;
            padding: 15px 40px;
            font-size: 18px;
            font-weight: 600;
            color: white;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .btn-register:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
            background: linear-gradient(45deg, #4ECDC4, #FF6B6B);
        }

        .login-link {
            text-align: center;
            margin-top: 20px;
            color: white;
        }

        .login-link a {
            color: #FFD93D;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .login-link a:hover {
            color: #FFB347;
            text-decoration: underline;
        }

        .input-group-text {
            background: rgba(255, 255, 255, 0.9);
            border: none;
            border-radius: 10px 0 0 10px;
        }

        .file-info {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.8);
            text-align: center;
            margin-top: 5px;
        }

        @media (max-width: 768px) {
            .registration-container {
                margin: 20px;
                padding: 30px 20px;
            }

            .registration-header h1 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <div class="container d-flex align-items-center justify-content-center min-vh-100">
        <div class="registration-container">
            <div class="registration-header">
                <h1><i class="fas fa-user-plus me-3"></i>Online Voting System</h1>
                <h2>Registration Page</h2>
            </div>
            <form action="../actions/register.php" method="POST" enctype="multipart/form-data" class="text-center">
                <div class="mb-2">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                        <input type="text" class="form-control" name="username" placeholder="Enter username" required>
                    </div>
                </div>
                <div class="mb-2">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-mobile-alt"></i></span>
                        <input type="text" class="form-control" name="mobile" placeholder="Enter mobile number" required maxlength="10" minlength="10">
                    </div>
                </div>
                <div class="mb-2">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-birthday-cake"></i></span>
                        <input type="date" class="form-control" name="date_of_birth" placeholder="Enter date of birth" required max="<?php echo date('Y-m-d', strtotime('-18 years')); ?>">
                    </div>
                    <div class="file-info">
                        <small>You must be 18 years or older to vote</small>
                    </div>
                </div>
                <div class="mb-2">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" class="form-control" name="password" placeholder="Enter password" required>
                    </div>
                </div>
                <div class="mb-2">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" class="form-control" name="cpassword" placeholder="Confirm password" required>
                    </div>
                </div>
                <div class="mb-2">
                    <div class="file-upload">
                        <input type="file" name="photo" accept="image/*,video/*" id="photo-upload" required>
                        <label for="photo-upload" class="file-upload-label">
                            <i class="fas fa-cloud-upload-alt"></i> Choose profile photo/video
                        </label>
                    </div>
                    <div class="file-info">
                        <small>Supported: JPG, PNG, GIF, BMP, WEBP, SVG, MP4, AVI, MOV, WMV, MKV</small>
                    </div>
                </div>
                <div class="mb-2">
                    <div class="file-upload">
                        <input type="file" name="id_proof" accept="image/*,.pdf,.doc,.docx,.bmp,.tiff,.tif,.webp" id="id-proof-upload" required>
                        <label for="id-proof-upload" class="file-upload-label">
                            <i class="fas fa-id-card"></i> Upload ID Proof (College ID, Aadhaar, Passport, etc.)
                        </label>
                    </div>
                    <div class="file-info">
                        <small>Supported: Images (JPG, PNG, GIF, BMP, WEBP, TIFF) and Documents (PDF, DOC, DOCX) - Max 10MB - Required for age verification</small>
                    </div>
                </div>
                <div class="mb-2">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-user-tag"></i></span>
                        <select name="std" class="form-select" required>
                            <option value="">Select account type</option>
                            <option value="candidate">Candidate</option>
                            <option value="voter">Voter</option>
                        </select>
                    </div>
                </div>
                <div class="text-center">
                    <button type="submit" class="btn btn-register">
                        <i class="fas fa-user-plus me-2"></i>Register
                    </button>
                </div>
                <div class="login-link">
                    <p>Already registered? <a href="../index.php">Login here</a></p>
                </div>
            </form>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-w76AqPfDkMBDXo30jS1Sgez6pr3x5MlQ1ZAGC+nuZB+EYdgRZgiwxhTBTkF7CXvN" crossorigin="anonymous"></script>
</body>
</html>