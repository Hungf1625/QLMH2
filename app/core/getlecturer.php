<?php
    session_start();
    require_once 'databasePDO.php';

    $query = "SELECT * FROM users WHERE role_id = 'GV'";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $lecturers = $stmt->fetchALL(PDO::FETCH_ASSOC);


    $query = "SELECT * FROM users WHERE role_id = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$_GET['role_id']]);
    $lecturer = $stmt->fetch(PDO::FETCH_ASSOC);

?>