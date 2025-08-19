<?php
session_start();

include './config/database.php';
include './middleware/auth.php';
include './middleware/admin.php';

ob_start();

$title = 'User Menu';

$query = "SELECT a.id AS id, a.name AS user_name, email, role_id, b.name AS role_name FROM users AS a LEFT JOIN roles AS b ON a.role_id = b.id";
$data = mysqli_query($db, $query);
$i = 1;

$query = "SELECT id, name FROM roles";
$data_roles = mysqli_query($db, $query);

if (isset($_POST['name_store'])) {
    $name_store = mysqli_real_escape_string($db, $_POST['name_store']);
    $email_store = mysqli_real_escape_string($db, $_POST['email_store']);
    $password_store = mysqli_real_escape_string($db, $_POST['password_store']);
    $role_store = mysqli_real_escape_string($db, $_POST['role_store']);

    if (empty($name_store)) {
        $_SESSION['error-name-store'] = "Field name is required";
    }

    if (empty($email_store)) {
        $_SESSION['error-email-store'] = "Field email is required";
    }

    if (empty($password_store)) {
        $_SESSION['error-password-store'] = "Field password is required";
    }

    if (empty($role_store)) {
        $_SESSION['error-role-store'] = "Field role is required";
    }

    $query = "SELECT email FROM users WHERE email = '$email_store'";
    $data = mysqli_query($db, $query);

    if ($data->num_rows > 0) {
        $_SESSION['error-email-store'] = "Email already exist";
    }

    if (isset($_SESSION['error-name-store']) || isset($_SESSION['error-email-store']) || isset($_SESSION['error-password-store']) || isset($_SESSION['error-role-store'])) {
        mysqli_close($db);
        $_SESSION['old'] = $_POST;
        return header('location:user-menu.php');
    }

    $hashedPassword = password_hash($password_store, PASSWORD_BCRYPT);

    $query = "INSERT INTO users (name, email, password, role_id, created_at, updated_at) VALUES ('$name_store', '$email_store', '$hashedPassword', '$role_store', NOW(), NOW())";
    mysqli_query($db, $query);

    $_SESSION['success'] = "User successfully added";
    mysqli_close($db);
    return header('location:user-menu.php');
}

if (isset($_POST['name_update'])) {
    $id_update = mysqli_real_escape_string($db, $_POST['id_update']);
    $name_update = mysqli_real_escape_string($db, $_POST['name_update']);
    $email_update = mysqli_real_escape_string($db, $_POST['email_update']);
    $password_update = mysqli_real_escape_string($db, $_POST['password_update']);
    $role_update = mysqli_real_escape_string($db, $_POST['role_update']);

    if (empty($name_update)) {
        $_SESSION['error-name-update'] = "Field name is required";
    }

    if (empty($email_update)) {
        $_SESSION['error-email-update'] = "Field email is required";
    }

    if (empty($role_update)) {
        $_SESSION['error-role-update'] = "Field role is required";
    }

    $query = "SELECT email FROM users WHERE email = '$email_update' and id != '$id_update'";
    $data = mysqli_query($db, $query);

    if ($data->num_rows > 0) {
        $_SESSION['error-email-update'] = "Email already exist";
    }

    if (isset($_SESSION['error-name-update']) || isset($_SESSION['error-email-update']) || isset($_SESSION['error-role-update'])) {
        mysqli_close($db);
        $_SESSION['old'] = $_POST;
        return header('location:user-menu.php');
    }

    if (!empty($password_update)) {
        $hashedPassword = password_hash($password_update, PASSWORD_BCRYPT);

        $query = "UPDATE users SET name = '$name_update', email = '$email_update', role_id = '$role_update', password = '$password_update', updated_at = NOW() WHERE id = '$id_update'";
    } else {
        $query = "UPDATE users SET name = '$name_update', email = '$email_update', role_id = '$role_update', updated_at = NOW() WHERE id = '$id_update'";
    }

    mysqli_query($db, $query);

    $_SESSION['success'] = "User successfully updated";
    mysqli_close($db);
    return header('location:user-menu.php');
}

if (isset($_POST['id_destroy'])) {
    $id_destroy = mysqli_real_escape_string($db, $_POST['id_destroy']);

    $query = "DELETE FROM users WHERE id = '$id_destroy'";
    mysqli_query($db, $query);

    $_SESSION['success'] = "User successfully deleted";
    mysqli_close($db);
    return header('location:user-menu.php');
}

$old = isset($_SESSION['old']) ? $_SESSION['old'] : [];
?>

<div class="card">
    <div class="card-header">
        <h4>User Menu</h4>
    </div>
    <div class="card-body">
        <div class="d-flex justify-content-between">
            <div>
                <?php if (isset($_SESSION['success'])) {
                    include "./component/success.php";
                }
                ?>
            </div>
            <div class="d-flex align-items-end">
                <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#exampleModal">
                    <i class="align-middle" data-feather="plus"></i> New User
                </button>
            </div>
        </div>

        <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="exampleModalLabel">New User</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="user-menu.php" method="POST">

                        <div class="modal-body">
                            <div class="row">
                                <div class="col-12 col-md-6 mb-4">
                                    <label for="name" class="mb-2">Name</label>
                                    <input type="text" name="name_store" id="name" class="form-control form-control-lg <?= isset($_SESSION['error-name-store']) ? 'is-invalid' : '' ?>" placeholder="Type name here ..." value="<?= isset($old['name_store']) ? $old['name_store'] : ''  ?>">
                                    <?php if (isset($_SESSION['error-name-store'])) : ?>
                                        <span class="text-danger"><?= $_SESSION['error-name-store'] ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="col-12 col-md-6 mb-4">
                                    <label for="email" class="mb-2">Email</label>
                                    <input type="email" name="email_store" id="email" class="form-control form-control-lg <?= isset($_SESSION['error-email-store']) ? 'is-invalid' : '' ?>" placeholder="Type email here ..." value="<?= isset($old['email_store']) ? $old['email_store'] : '' ?>">
                                    <?php if (isset($_SESSION['error-email-store'])) : ?>
                                        <span class="text-danger"><?= $_SESSION['error-email-store'] ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="col-12 col-md-6 mb-4">
                                    <label for="role" class="mb-2">Role</label>
                                    <select name="role_store" id="role" class="form-select form-select-lg <?= isset($_SESSION['error-role-store']) ? 'is-invalid' : '' ?>">
                                        <option value="">-Choose role-</option>
                                        <?php while ($row = mysqli_fetch_object($data_roles)) : ?>
                                            <option <?= isset($old['role_store']) ? ($old['role_store'] == $row->id ? 'selected' : '') : '' ?> value="<?= $row->id; ?>"><?= $row->name; ?></option>
                                        <?php endwhile; ?>
                                    </select>
                                    <?php if (isset($_SESSION['error-role-store'])) : ?>
                                        <span class="text-danger"><?= $_SESSION['error-role-store'] ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="col-12 col-md-6 mb-4">
                                    <label for="password" class="mb-2">Password</label>
                                    <input type="password" name="password_store" id="password" class="form-control form-control-lg <?= isset($_SESSION['error-password-store']) ? 'is-invalid' : '' ?>" placeholder="Type password here ...">
                                    <?php if (isset($_SESSION['error-password-store'])) : ?>
                                        <span class="text-danger"><?= $_SESSION['error-password-store'] ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Save user</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered table-hover" id="usersTable" class="display">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_object($data)) : ?>
                        <tr>
                            <td><?= $i++; ?></td>
                            <td><?= $row->user_name; ?></td>
                            <td><?= $row->email; ?></td>
                            <td><?= $row->role_name; ?></td>
                            <td class="d-flex justify-content-center gap-2">
                                <a href="#" data-bs-toggle-2="tooltip" data-bs-placement="top" data-bs-title="Edit" data-bs-toggle="modal" data-bs-target="#edit<?= $row->id ?>"><i class="align-middle" data-feather="edit"></i></a>
                                <a href="#" data-bs-toggle-2="tooltip" data-bs-placement="top" data-bs-title="Delete" data-bs-toggle="modal" data-bs-target="#delete<?= $row->id ?>"><i class="align-middle" data-feather="trash-2"></i></a>
                            </td>
                        </tr>

                        <div class="modal fade" id="edit<?= $row->id ?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h1 class="modal-title fs-5" id="exampleModalLabel">Edit user</h1>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <form action="user-menu.php" method="POST">
                                        <input type="text" name="id_update" value="<?= $row->id; ?>" hidden>
                                        <div class="modal-body">
                                            <div class="row">
                                                <div class="col-12 col-md-6 mb-4">
                                                    <label for="name" class="mb-2">Name</label>
                                                    <input type="text" name="name_update" id="name" class="form-control form-control-lg <?= isset($_SESSION['error-name-update']) ? 'is-invalid' : '' ?>" placeholder="Type name here ..." value="<?= $row->user_name ?>">
                                                    <?php if (isset($_SESSION['error-name-update'])) : ?>
                                                        <span class="text-danger"><?= $_SESSION['error-name-update'] ?></span>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="col-12 col-md-6 mb-4">
                                                    <label for="email" class="mb-2">Email</label>
                                                    <input type="email" name="email_update" id="email" class="form-control form-control-lg <?= isset($_SESSION['error-email-update']) ? 'is-invalid' : '' ?>" placeholder="Type email here ..." value="<?= $row->email ?>">
                                                    <?php if (isset($_SESSION['error-email-update'])) : ?>
                                                        <span class="text-danger"><?= $_SESSION['error-email-update'] ?></span>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="col-12 col-md-6 mb-4">
                                                    <label for="role" class="mb-2">Role</label>
                                                    <select name="role_update" id="role" class="form-select form-select-lg <?= isset($_SESSION['error-role-update']) ? 'is-invalid' : '' ?>">
                                                        <?php mysqli_data_seek($data_roles, 0); ?>
                                                        <?php while ($role = mysqli_fetch_object($data_roles)) : ?>
                                                            <option <?= $row->role_id == $role->id ? 'selected' : '' ?> value="<?= $role->id; ?>"><?= $role->name; ?></option>
                                                        <?php endwhile; ?>
                                                    </select>
                                                    <?php if (isset($_SESSION['error-role-update'])) : ?>
                                                        <span class="text-danger"><?= $_SESSION['error-role-update'] ?></span>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="col-12 col-md-6 mb-4">
                                                    <label for="password" class="mb-2">Password</label>
                                                    <input type="password" name="password_update" id="password" class="form-control form-control-lg <?= isset($_SESSION['error-password-update']) ? 'is-invalid' : '' ?>" placeholder="Type password here ...">
                                                    <?php if (isset($_SESSION['error-password-update'])) : ?>
                                                        <span class="text-danger"><?= $_SESSION['error-password-update'] ?></span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                            <button type="submit" class="btn btn-warning">Update changes</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="modal fade" id="delete<?= $row->id ?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h1 class="modal-title fs-5" id="exampleModalLabel">Delete user</h1>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <form action="user-menu.php" method="POST">
                                        <input type="text" name="id_destroy" value="<?= $row->id ?>" hidden>
                                        <div class="modal-body">
                                            Delete user <b><?= $row->user_name ?></b>?
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                            <button type="submit" class="btn btn-danger">Delete user</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

    </div>
</div>

<?php
$content = ob_get_clean();


ob_start();
?>
<link href="https://cdn.datatables.net/v/bs5/jq-3.7.0/dt-2.3.2/datatables.min.css" rel="stylesheet" integrity="sha384-oy6ZmHnH9nTuDaccEOUPX5BSJbGKwDpz3u3XiFJBdNXDpAAZh28v/4zfMCU7o63p" crossorigin="anonymous">

<?php
$css = ob_get_clean();

ob_start();
?>
<script src="https://cdn.datatables.net/v/bs5/jq-3.7.0/dt-2.3.2/datatables.min.js" integrity="sha384-F5wD4YVHPFcdPbOt91vfXz6ZUTjeWsy4mJlvR4duPvlQdnq704Bh6DxV1BJy3gA2" crossorigin="anonymous"></script>

<script>
    $(document).ready(function() {
        $('#usersTable').DataTable();

        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle-2="tooltip"]')
        const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))
    });
</script>
<?php
$js = ob_get_clean();
include "./template.php";
mysqli_close($db);
unset($_SESSION['success']);
unset($_SESSION['old']);
unset($_SESSION['error-name-store'], $_SESSION['error-email-store'], $_SESSION['error-password-store'], $_SESSION['error-role-store']);
unset($_SESSION['error-name-update'], $_SESSION['error-email-update'], $_SESSION['error-password-update'], $_SESSION['error-role-update']);
?>