<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>online voting system -registration page</title>
     <!-- bootstrap css link -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" 
    integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    
</head>
<body class="bg-dark">
    <h1 class="text-info text-center p-3">online voting system</h1>
    <div class="bg-info py-4">
        <h2 class="text-center p-3">Registration Page</h2>
        <div class="container text-center">
            <form action="../actions/register.php" method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <input type="text" class="form-control w-50 m-auto" name="username" placeholder="enter username" required="required">
                </div>
                 <div class="mb-3">
                    <input type="text" class="form-control w-50 m-auto" name="mobile" placeholder="enter mobile number" required="required" 
                     maxlength="10" minlength="10">
                </div>
                 <div class="mb-3">
                    <input type="password" class="form-control w-50 m-auto" name="password" placeholder="enter password" required="required">
                </div>
                <div class="mb-3">
                    <input type="password" class="form-control w-50 m-auto" name="cpassword" placeholder="confirm password" required="required">
                </div>
                <div class="mb-3">
                    <input type="file" class="form-control w-50 m-auto" name="photo" accept="image/*,video/*" title="Upload any image or video file">
                    <small class="text-dark">Supported formats: JPG, PNG, GIF, BMP, WEBP, SVG, MP4, AVI, MOV</small>
                </div>
                <div class="mb-3">
                    <select name="std" class="form-select w-50 m-auto">
                    <option value="">select login type</option>
                    <option value="candidate">candidate</option>
                    <option value="voter">voter</option>
                    </select>
                </div>
                <button type="register" class="btn btn-dark my-4">register</button>
                <p>already registered? <a href="../index.php" class="text-white">  login here </a></p>
            </form>
        </div>
    </div>
    
    
</body>
</html>