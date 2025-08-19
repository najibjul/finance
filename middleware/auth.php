<?php
$auth = $_SESSION['auth'];

if (!isset($auth)) {
    $_SESSION['error'] = "Please login first";
    return header('Location: ../');
}

$auth = (object) $_SESSION['auth'];