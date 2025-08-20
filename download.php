<?php
session_start();
$file = 'public/report/' . $_GET['report'];

if (!file_exists($file)) {
    $_SESSION['error'] = "File not found";
    return header('Location: ' . $_SERVER["HTTP_REFERER"] ?? "dashboard.php");
}

header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="'.basename($file).'"');
header('Content-Length: ' . filesize($file));
readfile($file);
exit;
