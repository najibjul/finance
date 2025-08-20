<?php 
session_start();

if (isset($_SESSION['error'])) {
    $error = $_SESSION['error'];
} else {
    unset($_SESSION['error']);
}

if (isset($_POST['email'])) {
    include "./config/database.php";

    $email = mysqli_real_escape_string($db, $_POST['email']);
    $password = mysqli_real_escape_string($db, $_POST['password']);

    $query = "SELECT * FROM users where email = '$email'";
    $data = mysqli_query($db, $query);

    if ( $data->num_rows == 0 ) {
        $_SESSION['error'] = "Wrong email & password";
        mysqli_close($db);
        return header('Location: index.php');    
    }

    $result = mysqli_fetch_object($data);

    if (!password_verify($password, $result->password)) {
        $_SESSION['error'] = "Wrong email & password";
        mysqli_close($db);
        return header('Location: index.php');   
    }

    $_SESSION['auth'] = [
        'id' => $result->id,
        'name' => $result->name,
        'email' => $result->email,
        'role_id' => $result->role_id
    ];

    mysqli_close($db);
    return header('Location: dashboard.php');
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link rel="shortcut icon" href="./public/img/icons/icon-48x48.png" />

    <link rel="canonical" href="https://demo-basic.adminkit.io/pages-sign-in.html" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <title>Finance | Login</title>

    <link href="./public/css/app.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
</head>

<body>
    <main class="d-flex w-100">
        <div class="container d-flex flex-column">
            <div class="row vh-100">
                <div class="col-sm-10 col-md-8 col-lg-6 col-xl-5 mx-auto d-table h-100">
                    <div class="d-table-cell align-middle">

                        <div class="text-center mt-4">
                            <h1 class="h2">Welcome</h1>
                            <p class="lead">
                                Sign in to your account to continue
                            </p>
                        </div>

                        <div class="card">
                            <div class="card-body">
                                <div class="m-sm-3">
                                    <form method="POST" action="index.php">
                                        <div class="mb-3">
                                            <label class="form-label">Email</label>
                                            <input class="form-control form-control-lg" type="email" name="email" placeholder="Enter your email" required />
                                        </div>
                                        <div class="mb-4">
                                            <label class="form-label">Password</label>
                                            <input class="form-control form-control-lg" type="password" name="password" placeholder="Enter your password" required />
                                        </div>
                                        <div class="d-grid gap-2 mt-3 mb-3">
                                            <button type="submit" class="btn btn-lg btn-primary">Sign in</button>
                                        </div>
                                        <?php 
                                        if (isset($error)) {
                                            include "./component/error.php";
                                        }
                                        ?>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="./public/js/app.js"></script>
</body>

</html>

<?php 
unset($_SESSION['error']);
?>