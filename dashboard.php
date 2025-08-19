<?php 
session_start();

include './config/database.php';
include './middleware/auth.php';

ob_start();

$title = 'Dashboard';
?>

<h2>Welcome to Home Page</h2>
<p>Ini adalah konten halaman home.</p>

<?php 
$content = ob_get_clean();
include "./template.php";
