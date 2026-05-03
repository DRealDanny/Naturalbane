<?php
define('ADMIN_USER', 'Linus'); 
define('ADMIN_PASS', '12345@Linus'); 

session_start();

function check_auth() {
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        header("Location: index.php"); // Redirect to the login (index.php)
        exit;
    }
}
?>