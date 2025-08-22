<?php
session_start();
include './config/database.php';
include './middleware/auth.php';

if (isset($_POST['id_approve'])) {

    include './middleware/general-manager.php';

    $id_approve = mysqli_real_escape_string($db, $_POST['id_approve']);
    $invoice_approve = mysqli_real_escape_string($db, $_POST['invoice_approve']);

    $query = "UPDATE reports SET approved_by = '$auth->id', approved_at = NOW() WHERE id ='$id_approve'";
    mysqli_query($db, $query);

    $_SESSION['success'] = "Report successfully approved";
    mysqli_close($db);
    return header('location:show-report.php?invoice=' . $invoice_approve);
}

if (isset($_POST['id_destroy'])) {
    $id_destroy = mysqli_real_escape_string($db, $_POST['id_destroy']);

    $query = "SELECT document_name FROM reports WHERE id = '$id_destroy'";
    $data = mysqli_query($db, $query);
    $result = mysqli_fetch_object($data);

    $document_name = $result->document_name;
    $path = "./public/report/" . $document_name;

    if (file_exists($path)) {
        unlink($path);
    }

    $query = "DELETE FROM reports WHERE id = '$id_destroy'";
    mysqli_query($db, $query);

    $_SESSION['success'] = "Report successfully deleted";
    mysqli_close($db);
    return header('location:monitoring-report.php');
}

if (!isset($_GET['invoice'])) {
    mysqli_close($db);
    return header('location: monitoring-report.php');
}

$invoice = $_GET['invoice'];

ob_start();

$title = 'Show Report';

$query = "SELECT a.id, b.name reporter_name, invoice, upload_date, document_date, document_name, c.name document_type, a.extension, note, d.name approver_name, approved_at FROM reports a LEFT JOIN users b on a.reported_by = b.id LEFT JOIN document_types c ON a.document_type_id = c.id LEFT JOIN users d ON a.approved_by = d.id WHERE invoice = '$invoice'";
$data_reports = mysqli_query($db, $query);
$result = mysqli_fetch_object($data_reports);

?>
<div class="card">
    <div class="card-header">
        <h4>Detail report</h4>
    </div>
    <div class="card-body">
        <?php
        if (isset($_SESSION['success'])) {
            include "./component/success.php";
        }
        if (isset($_SESSION['error'])) {
            include "./component/error.php";
        }
        ?>
        <div class="m-3 d-flex justify-content-end gap-2">
            <a href="download.php?report=<?= $result->document_name ?>" class="btn btn-lg btn-success"><i data-feather="download"></i> Download</a>
            <?php if (empty($result->approved_at) && $auth->role_id == 1) : ?>
                <button class="btn btn-lg btn-danger" data-bs-toggle="modal" data-bs-target="#delete"><i data-feather="trash"></i> Delete</button>
            <?php endif; ?>
        </div>

        <div class="modal fade" id="delete" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="exampleModalLabel">Delete report</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="show-report.php" method="POST">
                        <input type="text" name="id_destroy" value="<?= $result->id ?>" hidden>
                        <div class="modal-body">
                            Delete report <b><?= $result->invoice ?></b>?
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-danger">Delete report</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12 col-md-6 mb-3">
                <label for="reporter_name">Reported By</label>
                <input type="text" id="reporter_name" value="<?= $result->reporter_name ?>" class="form-control form-control-lg" readonly>
            </div>
            <div class="col-12 col-md-6 mb-3">
                <label for="invoice">Invoice</label>
                <input type="text" id="invoice" value="<?= $result->invoice ?>" class="form-control form-control-lg" readonly>
            </div>
            <div class="col-12 col-md-6 mb-3">
                <label for="upload_date">Upload date</label>
                <input type="text" id="upload_date" value="<?= $result->upload_date ?>" class="form-control form-control-lg" readonly>
            </div>
            <div class="col-12 col-md-6 mb-3">
                <label for="document_date">Document date</label>
                <input type="text" id="document_date" value="<?= $result->document_date ?>" class="form-control form-control-lg" readonly>
            </div>
            <div class="col-12 col-md-6 mb-3">
                <label for="document_name">Document name</label>
                <input type="text" id="document_name" value="<?= $result->document_name ?>" class="form-control form-control-lg" readonly>
            </div>
            <div class="col-12 col-md-6 mb-3">
                <label for="document_type">Document type</label>
                <input type="text" id="document_type" value="<?= $result->document_type ?>" class="form-control form-control-lg" readonly>
            </div>
            <div class="col-12 col-md-6 mb-3">
                <label for="extension">Extension</label>
                <input type="text" id="extension" value="<?= $result->extension ?>" class="form-control form-control-lg" readonly>
            </div>
            <div class="col-12 col-md-6 mb-3">
                <label for="note">Note</label>
                <input type="text" id="note" value="<?= $result->note ?>" class="form-control form-control-lg" readonly>
            </div>
            <div class="col-12 col-md-6 mb-3">
                <label for="approver_name">Approver name</label>
                <input type="text" id="approver_name" value="<?= $result->approver_name ?>" class="form-control form-control-lg" readonly>
            </div>
            <div class="col-12 col-md-6 mb-3">
                <label for="approved_at">Approved at</label>
                <input type="text" id="approved_at" value="<?= $result->approved_at ?>" class="form-control form-control-lg" readonly>
            </div>
        </div>

        <?php if ($auth->role_id == 2 && empty($result->approved_at)) : ?>
            <div class="mt-3">
                <button class="btn btn-primary btn-lg w-100" data-bs-toggle="modal" data-bs-target="#approve">Approve report</button>

                <div class="modal fade" id="approve" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h1 class="modal-title fs-5" id="exampleModalLabel">Approve report</h1>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                Approve report <b><?= $result->invoice; ?></b>?
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <form action="show-report.php" method="POST">
                                    <input type="text" name="id_approve" value="<?= $result->id ?>" hidden>
                                    <input type="text" name="invoice_approve" value="<?= $result->invoice ?>" hidden>
                                    <button type="submit" class="btn btn-primary">Save changes</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
include "./template.php";
mysqli_close($db);
unset($_SESSION['success'], $_SESSION['error']);
?>