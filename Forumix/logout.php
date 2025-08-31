<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (isset($_SESSION['user_name'])) {
    unset($_SESSION['user_name']);   
}
header("Location: /index.php");
exit();
?>