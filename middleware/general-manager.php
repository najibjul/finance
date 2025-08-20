<?php 
if ($auth->role_id != 3) {
    $url = $_SERVER['HTTP_REFERER'] ?? 'index.php';
    return header('Location: ' . $url);
}