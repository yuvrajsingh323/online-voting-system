
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>online voting system</title>
    <!-- bootstrap css link -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" 
    integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
</head>
<body class="bg-dark">
    <h1 class="text-info text-center p-3">online voting system</h1>
    <div class="bg-info py-4">
        <h2 class="text-center p-3">Admin Login</h2>
        <div class="container text-center">
            <form action="./actions/login.php" method="POST">
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
                    <select name="std" class="form-select w-50 m-auto">
                    <option value="">select login type</option>
                    <option value="candidate">candidate</option>
                    <option value="voter">voter</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-dark my-4">login</button>
                <p>not registered? <a href="./partials/registration.php" class="text-white">  register here </a></p>
            </form>
        </div>
    </div>
</body>
</html>