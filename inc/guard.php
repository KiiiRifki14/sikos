<?php
function is_admin() { return ($_SESSION['peran'] ?? '') == 'ADMIN'; }
function is_owner() { return ($_SESSION['peran'] ?? '') == 'OWNER'; }
function is_penghuni() { return ($_SESSION['peran'] ?? '') == 'PENGHUNI'; }
?>