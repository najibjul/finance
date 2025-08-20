<?php
session_start();
include './config/database.php';
include './middleware/auth.php';

ob_start();

$title = 'Profile';

$query = "SELECT name FROM roles WHERE id = '$auth->role_id'";
$role_auth = mysqli_query($db, $query);
$result = mysqli_fetch_object($role_auth);

if (isset($_POST['password'])) {
    $password = mysqli_real_escape_string($db, $_POST['password']);

    if (empty($password)) {
        $_SESSION['error-password'] = "Field password is required";
    }

    if (isset($_SESSION['error-password'])) {
        mysqli_close($db);
        $_SESSION['old'] = $_POST;
        return header('location:profile.php');
    }

    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    $query = "UPDATE users SET password = '$hashedPassword' WHERE id = '$auth->id'";
    mysqli_query($db, $query);

    $_SESSION['success'] = "Password successfully updated";
    mysqli_close($db);
    return header('location:profile.php');
}

?>
<div class="card">
    <div class="card-header">
        <h4>Profile</h4>
    </div>
    <div class="card-body">
        <?php
        if (isset($_SESSION['success'])) {
            include "./component/success.php";
        }
        ?>

        <div class="row">
            <div class="col-12 col-md-6 mb-3">
                <label for="name">Name</label>
                <input type="text" id="name" value="<?= $auth->name ?>" class="form-control form-control-lg" disabled>
            </div>
            <div class="col-12 col-md-6 mb-3">
                <label for="email">Email</label>
                <input type="email" id="email" value="<?= $auth->email ?>" class="form-control form-control-lg" disabled>
            </div>
            <div class="col-12 col-md-6 mb-3">
                <label for="role">Role</label>
                <input type="text" id="role" value="<?= $result->name ?>" class="form-control form-control-lg" disabled>
            </div>
            <div class="col-12 col-md-6 mb-3">
                <label for="password">New Password</label>
                <form action="profile.php" method="POST">
                    <input type="password" id="password" name="password" class="form-control form-control-lg <?= isset($_SESSION['error-password']) ? 'is-invalid' : '' ?>" placeholder="Type password here ...">
                    <?php if (isset($_SESSION['error-password'])) : ?>
                        <span class="text-danger"><?= $_SESSION['error-password'] ?></span><br>
                    <?php endif; ?>
                    <button class="btn btn-lg btn-primary mt-3">Change password</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include "./template.php";
mysqli_close($db);
unset($_SESSION['success']);
unset($_SESSION['error-password']);
?>