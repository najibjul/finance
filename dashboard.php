<?php 
session_start();

include './config/database.php';
include './middleware/auth.php';

ob_start();

$query = "SELECT id FROM reports WHERE DATE(upload_date) = CURDATE()";
$data = mysqli_query($db, $query);
$daily = $data->num_rows;

$query = "SELECT id FROM reports WHERE MONTH(upload_date) = MONTH(CURDATE())";
$data = mysqli_query($db, $query);
$month = $data->num_rows;

$query = "SELECT id FROM reports WHERE approved_at IS NULL";
$data = mysqli_query($db, $query);
$waiting = $data->num_rows;

$query = "SELECT extension, COUNT(*) total FROM reports GROUP BY extension";
$data_pie = mysqli_query($db, $query);

$query = "SELECT name, total FROM months a LEFT JOIN ( SELECT CONVERT(DATE_FORMAT(upload_date, '%m'), UNSIGNED) AS period_id, COUNT(*) AS total FROM reports GROUP BY period_id ORDER BY period_id ) b ON a.id = b.period_id";
$data_line = mysqli_query($db, $query);

$query = "SELECT invoice, upload_date, document_name FROM reports ORDER BY upload_date DESC LIMIT 10";
$data_last = mysqli_query($db, $query);

$title = 'Dashboard';
?>

<h2>Welcome to Home Page</h2>
<p>Ini adalah konten halaman home.</p>

<?php 
$content = ob_get_clean();
include "./template.php";
