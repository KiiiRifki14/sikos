<?php
session_start();
require 'inc/koneksi.php';

if(isset($_SESSION['id_pengguna'])){
    $db = new Database();
    $db->catat_log($_SESSION['id_pengguna'], 'LOGOUT', "User logout dari sistem.");
}

session_unset();
session_destroy();
header('Location: login.php');
?>