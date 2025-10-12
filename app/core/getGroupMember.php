<?php
//lay tat ca thanh vien theo group_id 
require_once 'getUser.php';
$group_id = $_GET['group_id'];
$query = "SELECT * FROM groupmember WHERE group_id = ? AND user_id = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$group_id,$userInfo['id']]);
$member = $stmt->fetch(PDO::FETCH_ASSOC);
?>