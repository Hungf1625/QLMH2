<?php 
require_once '../core/databasePDO.php';

// Kiểm tra có đủ tham số không
if(isset($_GET['group_id']) && isset($_GET['user_id'])) {
    $group_id = $_GET['group_id'];
    $user_id = $_GET['user_id'];
    
    try {
        // Kiểm tra thành viên có tồn tại trong nhóm không
        $query = 'SELECT * FROM groupmember WHERE group_id = ? AND user_id = ?';
        $stmt = $pdo->prepare($query);
        $stmt->execute([$group_id, $user_id]);
        $currentmember = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if(!$currentmember) {
            echo "<script>alert('Thành viên không tồn tại trong nhóm!'); window.history.back();</script>";
            exit;
        }
        
        // Kiểm tra nếu là trưởng nhóm thì không cho xóa
        if($currentmember['role_in_group'] == 'leader') {
            echo "<script>alert('Không thể xóa trưởng nhóm!'); window.history.back();</script>";
            exit;
        }
        
        // Thực hiện xóa thành viên
        $deleteQuery = 'DELETE FROM groupmember WHERE group_id = ? AND user_id = ?';
        $deleteStmt = $pdo->prepare($deleteQuery);
        $deleteStmt->execute([$group_id, $user_id]);
        
        if($deleteStmt->rowCount() > 0) {
            // Xóa thành công, hiện alert và chuyển hướng
            echo "<script>
                alert('Xóa thành viên thành công!');
                window.location.href = '../views/groupDetail.php?group_id=' + " . $group_id . ";
            </script>";
            exit;
        } else {
            echo "<script>alert('Xóa thất bại!'); window.history.back();</script>";
        }
        
    } catch(PDOException $e) {
        echo "<script>alert('Lỗi: " . addslashes($e->getMessage()) . "'); window.history.back();</script>";
    }
} else {
    echo "<script>alert('Thiếu thông tin group_id hoặc user_id!'); window.history.back();</script>";
}
?>