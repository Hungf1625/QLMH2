<?php
require_once '../core/databasePDO.php';

$group_id = $_GET['group_id'] ?? '';
$target_user_id = $_GET['user_id'] ?? ''; 

if (empty($group_id) || empty($target_user_id)) {
    echo "<script>
        alert('Thiếu thông tin nhóm hoặc người dùng');
        window.location.href = '../views/groups.php';
    </script>";
    exit;
}

// Hủy yêu cầu
$query = "DELETE FROM groupmember WHERE group_id = ? AND user_id = ?";
$stmt = $pdo->prepare($query);
$result = $stmt->execute([$group_id, $target_user_id]);

if ($result && $stmt->rowCount() > 0) {
    echo "<script>
        alert('Đã rời nhóm');
        window.location.href = '../views/groups.php';
    </script>";
} else {
    echo "<script>
        alert('Không thể thực hiện');
        window.location.href = '../views/groupDetail.php?group_id=$group_id';
    </script>";
}
exit;
?>