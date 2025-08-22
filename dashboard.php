<?php
session_start();

include './config/database.php';
include './middleware/auth.php';

if (!isset($_GET['month']) || !isset($_GET['year'])) {
    mysqli_close($db);
    date_default_timezone_set('Asia/Jakarta');
    $year = date('Y', strtotime('now'));
    return header('location: dashboard.php?month=All&year=' . $year);
}

if ($_GET['month'] == "All") {
    $year = intval($_GET['year']);
    $month = "All";
    if (!preg_match('/^(\d{4})$/', $year, $matches)) {
        mysqli_close($db);
        date_default_timezone_set('Asia/Jakarta');
        $year = date('Y', strtotime('now'));
        return header('location: dashboard.php?month=All&year=' . $year);
    }
} else {
    $year = intval($_GET['year']);
    $month = $_GET['month'];
    $my = $month . '-' . $year;
    if (!preg_match('/^(0[1-9]|1[0-2])-(\d{4})$/', $my, $matches)) {
        mysqli_close($db);
        date_default_timezone_set('Asia/Jakarta');
        $year = date('Y', strtotime('now'));
        return header('location: dashboard.php?year=' . $year);
    }
}

ob_start();

$query = "SELECT id FROM reports WHERE DATE(upload_date) = CURDATE()";
$data = mysqli_query($db, $query);
$daily = $data->num_rows;

$query = "SELECT id FROM reports WHERE MONTH(upload_date) = MONTH(CURDATE())";
$data = mysqli_query($db, $query);
$monthly = $data->num_rows;

$query = "SELECT id FROM reports WHERE approved_at IS NULL";
$data = mysqli_query($db, $query);
$waiting = $data->num_rows;

$query = "SELECT extension, COUNT(*) total FROM reports WHERE YEAR(upload_date) = '$year'";
if ($month != "All") {
    $query .= " AND MONTH(upload_date) = '$month'";
}
$query .= " GROUP BY extension";
$data_pie = mysqli_query($db, $query);

if ($month == 'All') {
    $query = "SELECT name, total FROM months a LEFT JOIN ( SELECT CONVERT(DATE_FORMAT(upload_date, '%m'), UNSIGNED) AS period_id, COUNT(*) AS total FROM reports WHERE YEAR(upload_date) = '$year' GROUP BY period_id ORDER BY period_id ) b ON a.id = b.period_id";
} else {
    $query = "SELECT * FROM ( SELECT DAY AS name, case when total IS NULL AND (DAY <= DAY(CURDATE()) OR '$month' < MONTH(CURDATE())) then 0 ELSE total END AS total from ( SELECT day , total FROM ( SELECT CONVERT(DATE_FORMAT(upload_date, '%d'), UNSIGNED) AS day_id, COUNT(*) total FROM reports WHERE YEAR(upload_date) = '$year' AND MONTH(upload_date) = '$month' GROUP BY day_id ) a RIGHT JOIN dates b ON a.day_id = b.day ORDER BY day ASC ) c ) d WHERE NAME <= DATE(CURDATE())";

}
$data_line = mysqli_query($db, $query);

$query = "SELECT invoice, upload_date, document_name FROM reports WHERE YEAR(upload_date) = '$year' ";
if ($month != 'All') {
    $query .= "AND MONTH(upload_date) = '$month'";
}
$query .= "ORDER BY upload_date DESC, created_at DESC LIMIT 10";
$data_last = mysqli_query($db, $query);
$i = 1;

$title = 'Dashboard';

$query = "SELECT * FROM months";
$data_months = mysqli_query($db, $query);

$query = "SELECT * FROM years";
$data_years = mysqli_query($db, $query);
?>

<h3 class="fw-bold">Dashboard</h3>
<div class="d-flex justify-content-end">
    <form action="dashboard.php" method="GET">
        <div class="d-flex gap-2 my-4">
            <select name="month" class="form-select form-select-lg w-auto">
                <option value="All">All</option>
                <?php foreach ($data_months as $row) : ?>
                    <option <?= $row['id'] == $_GET['month'] ? 'selected' : '' ?> value="<?= $row['id'] ?>"><?= $row['name'] ?></option>
                <?php endforeach; ?>
            </select>
            <select name="year" class="form-select form-select-lg w-auto">
                <?php foreach ($data_years as $row) : ?>
                    <option <?= $row['id'] == $_GET['year'] ? 'selected' : '' ?> value="<?= $row['name'] ?>"><?= $row['name'] ?></option>
                <?php endforeach; ?>
            </select>
            <button class="btn btn-lg btn-primary">Filter</button>
        </div>
    </form>
</div>
<div class="row">
    <div class="col-12 col-md-6">
        <div class="row row-cols-1 row-cols-md-2">
            <div class="col">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col mt-0">
                                <h5 class="card-title">Reports Today</h5>
                            </div>

                            <div class="col-auto">
                                <div class="stat text-primary">
                                    <i class="align-middle" data-feather="tag"></i>
                                </div>
                            </div>
                        </div>
                        <h1 class="mt-1 mb-3"><?= $daily ?></h1>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col mt-0">
                                <h5 class="card-title">Reports Monthly</h5>
                            </div>

                            <div class="col-auto">
                                <div class="stat text-primary">
                                    <i class="align-middle" data-feather="calendar"></i>
                                </div>
                            </div>
                        </div>
                        <h1 class="mt-1 mb-3"><?= $monthly ?></h1>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col mt-0">
                                <h5 class="card-title">Pending Report</h5>
                            </div>

                            <div class="col-auto">
                                <div class="stat text-primary">
                                    <i class="align-middle" data-feather="clock"></i>
                                </div>
                            </div>
                        </div>
                        <h1 class="mt-1 mb-3"><?= $waiting ?></h1>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6">
        <div class="card flex-fill w-100">
            <div class="card-header">
                <h5 class="card-title mb-0">Reports Timeline</h5>
            </div>
            <div class="card-body py-3">
                <div class="chart chart-sm">
                    <canvas id="chartjs-dashboard-line"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12 col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Latest Report</h5>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-end mb-2 mx-3">
                    <a href="monitoring-report.php"><u>SEE ALL</u></a>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th class="text-center">No</th>
                                <th class="text-center">Invoice</th>
                                <th class="text-center">Upload Date</th>
                                <th class="text-center">Document Name</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data_last as $row) : ?>
                                <tr>
                                    <td class="text-center"><?= $i++ ?></td>
                                    <td class="text-center"><?= $row['invoice'] ?></td>
                                    <td class="text-center"><?= $row['upload_date'] ?></td>
                                    <td class="text-center"><?= $row['document_name'] ?></td>
                                </tr>
                            <?php endforeach ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="card flex-fill w-100">
            <div class="card-header">

                <h5 class="card-title mb-0">Trends of Extension</h5>
            </div>
            <div class="card-body d-flex">
                <div class="align-self-center w-100">
                    <div class="py-3">
                        <div class="chart chart-xs">
                            <canvas id="chartjs-dashboard-pie"></canvas>
                        </div>
                    </div>
                    <table class="table mb-0">
                        <tbody>
                            <?php foreach ($data_pie as $row) : ?>
                                <tr>
                                    <td><?= $row['extension'] ?></td>
                                    <td class="text-end"><?= $row['total'] ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();

ob_start();
?>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        var ctx = document.getElementById("chartjs-dashboard-line").getContext("2d");
        var gradient = ctx.createLinearGradient(0, 0, 0, 225);
        gradient.addColorStop(0, "rgba(215, 227, 244, 1)");
        gradient.addColorStop(1, "rgba(215, 227, 244, 0)");
        new Chart(document.getElementById("chartjs-dashboard-line"), {
            type: "line",
            data: {
                labels: [<?php foreach ($data_line as $row) : ?><?= '"' . $row['name'] . '"' . ', ' ?><?php endforeach; ?>],
                datasets: [{
                    label: "Report",
                    fill: true,
                    backgroundColor: gradient,
                    borderColor: window.theme.primary,
                    data: [<?php foreach ($data_line as $row) : ?><?= $row['total'] . ', ' ?><?php endforeach; ?>]
                }]
            },
            options: {
                maintainAspectRatio: false,
                legend: {
                    display: false
                },
                tooltips: {
                    intersect: false
                },
                hover: {
                    intersect: true
                },
                plugins: {
                    filler: {
                        propagate: false
                    }
                },
                scales: {
                    xAxes: [{
                        reverse: true,
                        gridLines: {
                            color: "rgba(0,0,0,0.0)"
                        }
                    }],
                    yAxes: [{
                        ticks: {
                            stepSize: 1000
                        },
                        display: true,
                        borderDash: [3, 3],
                        gridLines: {
                            color: "rgba(0,0,0,0.0)"
                        }
                    }]
                }
            }
        });
    });
</script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        new Chart(document.getElementById("chartjs-dashboard-pie"), {
            type: "pie",
            data: {
                labels: [<?php foreach ($data_pie as $row) : ?><?= '"' . $row['extension'] . '"' . ', ' ?><?php endforeach; ?>],
                datasets: [{
                    data: [<?php foreach ($data_pie as $row) : ?><?= $row['total'] . ', ' ?><?php endforeach; ?>],
                    backgroundColor: [
                        window.theme.danger,
                        window.theme.success,
                        window.theme.primary,
                        window.theme.warning,
                    ],
                    borderWidth: 5
                }]
            },
            options: {
                responsive: !window.MSInputMethodContext,
                maintainAspectRatio: false,
                legend: {
                    display: false
                },
                cutoutPercentage: 75
            }
        });
    });
</script>
<?php
$js = ob_get_clean();
mysqli_close($db);
include "./template.php";
?>