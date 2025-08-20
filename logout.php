<?php 
session_start();
session_destroy();

return header('location:./index.php');