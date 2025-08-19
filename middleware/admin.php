<?php 
if ($auth->role_id != 1) {
    return header('location:../');
}