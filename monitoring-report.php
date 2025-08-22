<?php
session_start();
include './config/database.php';
include './middleware/auth.php';

ob_start();

$title = 'Monitoring Report';

if (isset($_GET['document_name'])) {
    $document_name = mysqli_real_escape_string($db, $_GET['document_name']);
    $invoice = mysqli_real_escape_string($db, $_GET['invoice']);
    $start_date = mysqli_real_escape_string($db, $_GET['start_date']);
    $end_date = mysqli_real_escape_string($db, $_GET['end_date']);

    if (!empty($start_date) && empty($end_date)) {
        mysqli_close($db);
        $_SESSION['old'] = $_GET;
        $_SESSION['error-end-date'] = 'Field end date cannot empty';
        return header('location: search-report.php');
    }

    if (empty($start_date) && !empty($end_date)) {
        mysqli_close($db);
        $_SESSION['old'] = $_GET;
        $_SESSION['error-start-date'] = 'Field start date cannot empty';
        return header('location: search-report.php');
    }

    $query = "SELECT id, invoice, upload_date, document_name, approved_at FROM reports WHERE 1 ";
    if (!empty($document_name)) {
        $query .= "AND document_name LIKE '%$document_name%' ";
    }
    if (!empty($invoice)) {
        $query .= "AND invoice LIKE '%$invoice%' ";
    }
    if (!empty($start_date)) {
        $query .= "AND upload_date BETWEEN '$start_date' AND '$end_date' ";
    }
    $query .= "ORDER BY upload_date DESC, created_at DESC";
} else {
    $query = "SELECT id, invoice, upload_date, document_name, approved_at FROM reports ORDER BY upload_date DESC, created_at DESC";
}

$data_reports = mysqli_query($db, $query);
$i = 1;

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

?>
<div class="card">
    <div class="card-header">
        <h4>Monitoring Report</h4>
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

        <?php if (isset($_GET['document_name'])) : ?>
            <div class="alert alert-warning w-auto">
                <div><u>KEYWORD :</u></div>
                <div class="mt-2">Document name : <?= $_GET['document_name'] ?></div>
                <div class="">Invoice : <?= $_GET['invoice'] ?></div>
                <div class="">Start date : <?= $_GET['start_date'] ?></div>
                <div class="">End date : <?= $_GET['end_date'] ?></div>
                <div class="mt-3">
                    <a href="monitoring-report.php" class="btn btn-outline-primary bt-lg rounded-pill">Reset</a>
                </div>
            </div>
        <?php endif; ?>
            <div class="table-responsive">
                <table id="reportsTable" class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th class="text-center">No</th>
                            <th class="text-center">Invoice</th>
                            <th class="text-center">Upload date</th>
                            <th class="text-center">Document name</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_object($data_reports)) : ?>
                            <tr>
                                <td class="text-center"><?= $i++ ?></td>
                                <td class="text-center"><?= $row->invoice ?></td>
                                <td class="text-center"><?= $row->upload_date ?></td>
                                <td class="text-center"><?= $row->document_name ?></td>
                                <td class="d-flex justify-content-center gap-2">
                                    <a href="show-report.php?invoice=<?= $row->invoice ?>" id="show" class="text-info" data-bs-toggle-2="tooltip" data-bs-placement="top" data-bs-title="Show"><i class="align-middle" data-feather="eye"></i></a>

                                    <a href="download.php?report=<?= $row->document_name ?>" id="download" class="text-success" data-bs-toggle-2="tooltip" data-bs-placement="top" data-bs-title="Download"><i class="align-middle" data-feather="download"></i></a>

                                    <?php if (empty($row->approved_at) && $auth->role_id == 1) : ?>
                                        <a href="#" id="destroy" class="text-danger" data-bs-toggle-2="tooltip" data-bs-placement="top" data-bs-title="Delete" data-bs-toggle="modal" data-bs-target="#delete<?= $row->id ?>"><i class="align-middle" data-feather="trash-2"></i></a>
                                    <?php endif; ?>
                                </td>
                            </tr>

                            <div class="modal fade" id="delete<?= $row->id ?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h1 class="modal-title fs-5" id="exampleModalLabel">Delete report</h1>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <form action="monitoring-report.php" method="POST">
                                            <input type="text" name="id_destroy" value="<?= $row->id ?>" hidden>
                                            <div class="modal-body">
                                                Delete report <b><?= $row->invoice ?></b>?
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                <button type="submit" class="btn btn-danger">Delete report</button>
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

<style>
    #show:hover {
        color: #0d8af0 !important;
    }

    #destroy:hover {
        color: #dc3535 !important;
    }
</style>

<?php
$css = ob_get_clean();

ob_start();
?>
<script src="https://cdn.datatables.net/v/bs5/jq-3.7.0/dt-2.3.2/datatables.min.js" integrity="sha384-F5wD4YVHPFcdPbOt91vfXz6ZUTjeWsy4mJlvR4duPvlQdnq704Bh6DxV1BJy3gA2" crossorigin="anonymous"></script>

<script>
    $(document).ready(function() {
        $('#reportsTable').DataTable({
            'searching': false
        });

        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle-2="tooltip"]')
        const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))
    });
</script>
<?php
$js = ob_get_clean();
include "./template.php";
mysqli_close($db);
unset($_SESSION['success'], $_SESSION['error']);
?>