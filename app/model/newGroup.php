<?php 
require_once '../core/databasePDO.php';
require_once '../core/database.php';
session_start();

$userInfo = null;

if(isset($_SESSION['username'])) {
    $user = "SELECT * FROM users WHERE username = ?";
    $stmt = $pdo->prepare($user);
    $stmt->execute([$_SESSION['username']]);
    $userInfo = $stmt->fetch(PDO::FETCH_ASSOC);
} else {
    // Redirect to login page if not logged in
    header("Location: ../views/login.php");
    exit();
}


if($_SERVER["REQUEST_METHOD"] == "POST"){
    $groupname = trim($_POST['groupname'] ?? '');
    
    if(!empty($groupname) && !empty($userInfo['id'])){
        $stmt1 = null;
        $stmt2 = null;
        
        try {
            // Bắt đầu transaction
            $conn->begin_transaction();
            
            $stmt1 = $conn->prepare("INSERT INTO groups (groupname, created_at) VALUES (?, NOW())");
            if (!$stmt1) {
                throw new Exception("Lỗi prepare statement 1: " . $conn->error);
            }
            $stmt1->bind_param("s", $groupname);
            
            if(!$stmt1->execute()){
                throw new Exception("Lỗi execute statement 1: " . $stmt1->error);
            }
            
            $newGroupId = $conn->insert_id;
            
            $stmt2 = $conn->prepare("INSERT INTO groupmember (group_id, user_id, role_in_group, status, joined_at) VALUES (?, ?, 'leader', 'approved', NOW())");
            if (!$stmt2) {
                throw new Exception("Lỗi prepare statement 2: " . $conn->error);
            }
            $stmt2->bind_param("is", $newGroupId, $userInfo['id']);
            
            if(!$stmt2->execute()){
                throw new Exception("Lỗi execute statement 2: " . $stmt2->error);
            }
            
            $conn->commit();
            
            echo "<script>
                alert('Tạo nhóm thành công! ID: ' + $newGroupId);
                window.location.href = '../views/groups.php';
            </script>";
            exit();
            
        } catch (Exception $e) {

            $conn->rollback();
            
            echo "<script>
                alert('Lỗi khi tạo nhóm: " . addslashes($e->getMessage()) . "');
                window.location.href = '../views/groups.php';
            </script>";
            exit();
        } finally {

            if ($stmt1) {
                $stmt1->close();
            }
            if ($stmt2) {
                $stmt2->close();
            }
        }
    } else {
        $error_msg = empty($groupname) ? 'Vui lòng điền tên nhóm.' : 'Vui lòng đăng nhập.';
        echo "<script>
            alert('$error_msg');

        </script>";
        exit();
    }
}
?>