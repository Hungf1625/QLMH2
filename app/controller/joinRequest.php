<?php
session_start();
require_once '../core/databasePDO.php';
require_once '../core/getUser.php';
require_once '../core/getGroup.php';

if (!isset($userInfo) || !isset($group)) {
    echo "<script>
        alert('Không tìm thấy thông tin người dùng hoặc nhóm');
        window.location.href = '../views/groups.php';
    </script>";
    exit;
}

$group_id = $group['group_id'];
$user_id = $userInfo['id'];

// Kiểm tra xem đã là thành viên hoặc đã gửi yêu cầu chưa
$query = 'SELECT * FROM groupmember WHERE group_id = ? AND user_id = ?';
$stmt = $pdo->prepare($query);
$stmt->execute([$group_id, $user_id]);
$request = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$request) {
    try {
        // Bắt đầu transaction
        $pdo->beginTransaction();

        // Thêm yêu cầu gia nhập mới
        $insertQuery = "INSERT INTO groupmember (group_id, user_id, role_in_group, status, joined_at) 
                       VALUES (?, ?, 'member', 'pending', NOW())";
        $stmt = $pdo->prepare($insertQuery);
        $result = $stmt->execute([$group_id, $user_id]);

        if ($result) {
            $pdo->commit();
            echo "<script>
                alert('Đã gửi yêu cầu gia nhập nhóm thành công!');
                window.location.href = '../views/groups.php';
            </script>";
        } else {
            throw new Exception("Không thể thêm yêu cầu");
        }

    } catch (Exception $e) {
        $pdo->rollBack();
        echo "<script>
            alert('Có lỗi xảy ra: " . addslashes($e->getMessage()) . "');
            window.location.href = '../views/groups.php';
        </script>";
    }
} else {
    // Kiểm tra trạng thái hiện tại
    $status = $request['status'];
    $message = '';
    
    switch($status) {
        case 'approved':
            $message = 'Bạn đã là thành viên của nhóm này!';
            break;
        case 'pending':
            $message = 'Bạn đã gửi yêu cầu gia nhập nhóm này rồi!';
            break;
        case 'rejected':
            $message = 'Yêu cầu gia nhập của bạn đã bị từ chối!';
            break;
        default:
            $message = 'Không thể xác định trạng thái yêu cầu!';
    }
    
    echo "<script>
        alert('$message');
        window.location.href = '../views/groups.php';
    </script>";
}

exit;
?>