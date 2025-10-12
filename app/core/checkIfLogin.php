<?php 
if(!isset($_SESSION['username']) || empty($_SESSION['username'])) {
    header("Location: ../views/login.php");
    exit();
}
?>