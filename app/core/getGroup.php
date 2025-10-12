<?php
$check = $_GET['group_id'] ?? '';

if(empty($check)) {
    die("Không tìm thấy ID nhóm");
}

$group = "SELECT * FROM groups WHERE group_id = ?";
$stmt = $pdo->prepare($group);
$stmt->execute([$check]); 
$group = $stmt->fetch(PDO::FETCH_ASSOC);


?>