<?php

function is_admin() {
    return isset($_SESSION['peran']) && $_SESSION['peran'] == 'ADMIN';
}

function is_owner() {
    return isset($_SESSION['peran']) && $_SESSION['peran'] == 'OWNER';
}

?>