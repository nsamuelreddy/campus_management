<?php
if ($_SESSION['role'] !== 'admin') {
    die("Unauthorized Access");
}


?>