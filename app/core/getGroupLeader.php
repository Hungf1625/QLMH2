<?php
$query = 'SELECT * FROM groupmember WHERE role_in_group = "leader"';
$stmt = $pdo->prepare($query);
$stmt->execute();
$leader = $stmt->fetch(PDO::FETCH_ASSOC);
?>