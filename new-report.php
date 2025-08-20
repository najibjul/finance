<?php
session_start();
include './config/database.php';
include './middleware/auth.php';
include './middleware/admin.php';

ob_start();
$title = 'New Report';

$query = "SELECT id, name, extension FROM document_types ORDER BY id DESC";
$data_types = mysqli_query($db, $query);

if (isset($_POST['invoice'])) {
    $invoice = mysqli_real_escape_string($db, $_POST['invoice']);
    $upload_date = mysqli_real_escape_string($db, $_POST['upload_date']);
    $document_date = mysqli_real_escape_string($db, $_POST['document_date']);
    $document_name = mysqli_real_escape_string($db, $_POST['document_name']);
    $document_type = mysqli_real_escape_string($db, $_POST['document_type']);
    $document = $_FILES['document'];
    $note = mysqli_real_escape_string($db, $_POST['note']);

    if (empty($invoice)) {
        $_SESSION['error-invoice'] = "Field invoice is required";
    } else {
        $query = "SELECT invoice FROM reports WHERE invoice = '$invoice'";
        $data = mysqli_query($db, $query);
        if ($data->num_rows > 0) {
            $_SESSION['error-invoice'] = "Invoice already exist";
        }
    }

    if (empty($upload_date)) {
        $_SESSION['error-upload-date'] = "Field upload date is required";
    }

    if (empty($document_date)) {
        $_SESSION['error-document-date'] = "Field document date is required";
    }

    if (empty($document_name)) {
        $_SESSION['error-document-name'] = "Field document name is required";
    }

    if (empty($document_type)) {
        $_SESSION['error-document-type'] = "Field document type is required";
    }

    if (!empty($document['name']) && !empty($document_type)) {

        $query = "SELECT extension FROM document_types WHERE id = '$document_type'";
        $data = mysqli_query($db, $query);
        $result = mysqli_fetch_object($data);
        $extensions = json_decode($result->extension, true);
        $allowed = array_keys(array_filter($extensions));

        $fileName   = $_FILES['document']['name'];
        $tmpName    = $_FILES['document']['tmp_name'];

        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed)) {
            $_SESSION['error-document'] = "Invalid extension";
        }
    } else {
        if (!empty($document_type)) {
            $_SESSION['error-document'] = "Field document is required";
        }
    }

    if (isset($_SESSION['error-invoice']) || isset($_SESSION['error-upload-date']) || isset($_SESSION['error-document-date']) || isset($_SESSION['error-document-name']) || isset($_SESSION['error-document-type']) || isset($_SESSION['error-document'])) {
        mysqli_close($db);
        $_SESSION['old'] = $_POST;
        return header('location:new-report.php');
    }

    date_default_timezone_set('Asia/Jakarta');
    $title = $document_name . "_" . date('ymdHis', strtotime('now')) . "." . $ext;

    $folder     = './public/report/';
    $saved = move_uploaded_file($tmpName, $folder . $title);

    $query = "INSERT INTO reports (reported_by, reporter_role_id, invoice, upload_date, document_date, document_name, document_type_id, extension, note, created_at, updated_at ) VALUES ('$auth->id', '$auth->role_id', '$invoice', '$upload_date', '$document_date', '$title', '$document_type', '$ext', '$note', NOW(), NOW())";
    mysqli_query($db, $query);

    $_SESSION['success'] = "Report successfully added";
    mysqli_close($db);
    return header('location:monitoring-report.php');
}

$old = isset($_SESSION['old']) ? $_SESSION['old'] : [];
?>
<div class="card">
    <div class="card-header">
        <h4>New Report</h4>
    </div>
    <div class="card-body">
        <form action="new-report.php" method="POST" enctype="multipart/form-data">
            <div class="row">
                <div class="col-12 col-md-6 mb-3">
                    <label for="invoice">No. Invoice</label>
                    <input type="text" class="form-control form-control-lg <?= isset($_SESSION['error-invoice']) ? 'is-invalid' : '' ?>" name="invoice" id="invoice" placeholder="Type invoice here ..." value="<?= isset($old['invoice']) ? $old['invoice'] : ''  ?>">
                    <?php if (isset($_SESSION['error-invoice'])) : ?>
                        <span class="text-danger"><?= $_SESSION['error-invoice'] ?></span>
                    <?php endif; ?>
                </div>
                <div class="col-12 col-md-6 mb-3">
                    <label for="upload-date">Upload date</label>
                    <input type="date" class="form-control form-control-lg <?= isset($_SESSION['error-upload-date']) ? 'is-invalid' : '' ?>" name="upload_date" id="upload-date" value="<?= isset($old['upload_date']) ? $old['upload_date'] : ''  ?>">
                    <?php if (isset($_SESSION['error-upload-date'])) : ?>
                        <span class="text-danger"><?= $_SESSION['error-upload-date'] ?></span>
                    <?php endif; ?>
                </div>
                <div class="col-12 col-md-6 mb-3">
                    <label for="document-date">Document date</label>
                    <input type="date" class="form-control form-control-lg <?= isset($_SESSION['error-document-date']) ? 'is-invalid' : '' ?>" name="document_date" id="document-date" value="<?= isset($old['document_date']) ? $old['document_date'] : ''  ?>">
                    <?php if (isset($_SESSION['error-document-date'])) : ?>
                        <span class="text-danger"><?= $_SESSION['error-document-date'] ?></span>
                    <?php endif; ?>
                </div>
                <div class="col-12 col-md-6 mb-3">
                    <label for="document-name">Document name</label>
                    <input type="text" class="form-control form-control-lg <?= isset($_SESSION['error-document-name']) ? 'is-invalid' : '' ?>" name="document_name" id="document-name" placeholder="Type document here ..." value="<?= isset($old['document_name']) ? $old['document_name'] : ''  ?>">
                    <?php if (isset($_SESSION['error-document-name'])) : ?>
                        <span class="text-danger"><?= $_SESSION['error-document-name'] ?></span>
                    <?php endif; ?>
                </div>
                <div class="col-12 col-md-6 mb-3">
                    <label for="document-type">Document type</label>
                    <select name="document_type" id="document_type" class="form-select form-select-lg <?= isset($_SESSION['error-document-type']) ? 'is-invalid' : '' ?>">
                        <option value="">-Choose type-</option>
                        <?php while ($row = mysqli_fetch_object($data_types)) : ?>
                            <option <?= isset($old['document_type']) && $old['document_type'] == $row->id ? 'selected' : ''  ?> value="<?= $row->id ?>">
                                <?= $row->name ?> (<?php
                                    $extensions = json_decode($row->extension, true);
                                    $countExtension = count($extensions);
                                    $i = 1;
                                    foreach ($extensions as $k => $v) {
                                        echo $k;
                                        if ($i != $countExtension) {
                                            echo ", ";
                                        }
                                        $i++;
                                    }
                                    ?>)
                            </option>
                        <?php endwhile; ?>
                    </select>
                    <?php if (isset($_SESSION['error-document-type'])) : ?>
                        <span class="text-danger"><?= $_SESSION['error-document-type'] ?></span>
                    <?php endif; ?>
                </div>
                <div class="col-12 col-md-6 mb-3">
                    <label for="document">Document</label>
                    <input type="file" class="form-control form-control-lg <?= isset($_SESSION['error-document']) ? 'is-invalid' : '' ?>" name="document" id="document">
                    <?php if (isset($_SESSION['error-document'])) : ?>
                        <span class="text-danger"><?= $_SESSION['error-document'] ?></span>
                    <?php endif; ?>
                </div>
                <div class="col-12 col-md-6 mb-3">
                    <label for="note">Note</label>
                    <textarea name="note" id="note" class="form-control" placeholder="Type note here ..."><?= isset($old['note']) ? $old['note'] : ''  ?></textarea>
                </div>
            </div>

            <button type="button" class="btn btn-primary btn-lg w-100 mt-4" data-bs-toggle="modal" data-bs-target="#store">
                Add report
            </button>

            <div class="modal fade" id="store" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="exampleModalLabel">New report</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            Save report?
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Yes</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
<?php
$content = ob_get_clean();
include "./template.php";
mysqli_close($db);

unset($_SESSION['success']);
unset($_SESSION['old']);
unset($_SESSION['error-invoice'], $_SESSION['error-upload-date'], $_SESSION['error-document-date'], $_SESSION['error-document-name'], $_SESSION['error-document-type'], $_SESSION['error-document']);
unset($_SESSION['error-name-update'], $_SESSION['error-email-update'], $_SESSION['error-password-update'], $_SESSION['error-role-update']);
?>