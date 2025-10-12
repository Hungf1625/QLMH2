<?php
session_start();
require_once '../core/databasePDO.php';
require_once '../core/getUser.php';

if(isset($_GET['group_id'])) {
    $group_id = $_GET['group_id'];
    $current_user_id = $userInfo['id'];
    
    if(empty($current_user_id)) {
        echo "<script>
            alert('Vui lòng đăng nhập!');
            window.location.href = '../views/groups.php';
        </script>";
        exit();
    }
    
    try {
        // Kiểm tra user có phải là leader của nhóm không
        $check_leader = $pdo->prepare("
            SELECT role_in_group 
            FROM groupmember 
            WHERE group_id = ? AND user_id = ? AND role_in_group = 'leader'
        ");
        $check_leader->execute([$group_id, $current_user_id]);
        
        if($check_leader->rowCount() > 0) {
            // User là leader, tiến hành xóa nhóm
            $pdo->beginTransaction();
            
            try {
                // 1. Xóa tất cả thành viên trong nhóm
                $delete_members = $pdo->prepare("DELETE FROM groupmember WHERE group_id = ?");
                $delete_members->execute([$group_id]);
                
                // 2. Xóa nhóm
                $delete_group = $pdo->prepare("DELETE FROM groups WHERE group_id = ?");
                $delete_group->execute([$group_id]);
                
                $pdo->commit();
                
                echo "<script>
                    alert('Xóa nhóm thành công!');
                    window.location.href = '../views/groups.php';
                </script>";
                
            } catch (Exception $e) {
                $pdo->rollBack();
                throw $e;
            }
            
        } else {
            echo "<script>
                alert('Bạn không có quyền xóa nhóm này!');
                window.location.href = '../views/groups.php';
            </script>";
        }
        
    } catch (Exception $e) {
        echo "<script>
            alert('Lỗi khi xóa nhóm: " . addslashes($e->getMessage()) . "');
            window.location.href = '../views/groups.php';
        </script>";
    }
} else {
    echo "<script>
        alert('Không tìm thấy nhóm!');
        window.location.href = '../views/groups.php';
    </script>";
}
?>