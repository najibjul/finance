<?php
session_start();
include './middleware/auth.php';

$old = isset($_SESSION['old']) ? $_SESSION['old'] : [];
ob_start();

$title = 'Search Report';
$old = isset($_SESSION['old']) ? $_SESSION['old'] : [];
?>
<div class="card">
    <div class="card-header">
        <h4>Searching Report</h4>
    </div>
    <div class="card-body">
        <form action="monitoring-report.php" method="GET">
            <div class="row">
                <div class="col-12 col-md-6 mb-3">
                    <label for="document-name">Document name</label>
                    <input type="text" name="document_name" id="document-name" class="form-control form-control-lg" placeholder="Type document name here..." value="<?= isset($old['document_name']) ? $old['document_name'] : ''  ?>">
                </div>
                <div class="col-12 col-md-6 mb-3">
                    <label for="document-name">Invoice</label>
                    <input type="text" name="invoice" id="invoice" class="form-control form-control-lg" placeholder="Type invoice here..." value="<?= isset($old['invoice']) ? $old['invoice'] : ''  ?>">
                </div>
                <div class="col-12 col-md-6 mb-3">
                    <label for="date">Date</label>
                    <div class="d-flex gap-2">
                        <div class="w-100">
                            <input type="date" name="start_date" id="date" class="form-control form-control-lg <?= isset($_SESSION['error-start-date']) ? 'is-invalid' : '' ?>" value="<?= isset($old['start_date']) ? $old['start_date'] : ''  ?>">
                            <?php if (isset($_SESSION['error-start-date'])) : ?>
                                <span class="text-danger"><?= $_SESSION['error-start-date'] ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="mx-2 mt-1 text-muted">to</div>
                        <div class="w-100">
                            <input type="date" name="end_date" id="date" class="form-control form-control-lg <?= isset($_SESSION['error-end-date']) ? 'is-invalid' : '' ?>" value="<?= isset($old['end_date']) ? $old['end_date'] : ''  ?>">
                            <?php if (isset($_SESSION['error-end-date'])) : ?>
                                <span class="text-danger"><?= $_SESSION['error-end-date'] ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-lg btn-primary w-100 mt-4">Search</button>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
include "./template.php";
unset($_SESSION['error-start-date'], $_SESSION['error-end-date']);
unset($_SESSION['old']);
?>