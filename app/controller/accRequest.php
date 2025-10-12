<?php
require_once '../core/databasePDO.php';

$group_id = $_GET['group_id'] ?? '';
$target_user_id = $_GET['user_id'] ?? '';

if (empty($group_id) || empty($target_user_id)) {
    echo "<script>
        alert('Thiếu thông tin nhóm hoặc người dùng');
        window.location.href = '../views/groupDetail.php?group_id=$group_id';
    </script>";
    exit;
}

// Kiểm tra và chấp nhận yêu cầu
$query = "UPDATE groupmember SET status = 'approved' WHERE group_id = ? AND user_id = ? AND status = 'pending'";
$stmt = $pdo->prepare($query);
$result = $stmt->execute([$group_id, $target_user_id]);

if ($result && $stmt->rowCount() > 0) {
    echo "<script>
        alert('Đã chấp nhận yêu cầu tham gia nhóm thành công!');
        window.location.href = '../views/groupDetail.php?group_id=$group_id';
    </script>";
} else {
    echo "<script>
        alert('Không thể chấp nhận yêu cầu hoặc yêu cầu không tồn tại');
        window.location.href = '../views/groupDetail.php?group_id=$group_id';
    </script>";
}
exit;
?>