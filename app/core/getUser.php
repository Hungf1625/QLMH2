<?php 
    $user = "SELECT * FROM users WHERE username = ?";
    $stmt = $pdo->prepare($user);
    $stmt->execute([$_SESSION['username']]);
    $userInfo = $stmt->fetch(PDO::FETCH_ASSOC);
?>