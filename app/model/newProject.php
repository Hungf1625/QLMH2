<?php 
header('Content-Type: application/json');

ob_start();

session_start();
require_once '../core/databasePDO.php';
require_once '../core/getUser.php';

try {

    if(!isset($_SESSION['username'])) {
        throw new Exception('Vui lòng đăng nhập');
    }

    $user = "SELECT * FROM users WHERE username = ?";
    $stmt = $pdo->prepare($user);
    $stmt->execute([$_SESSION['username']]);
    $userInfo = $stmt->fetch(PDO::FETCH_ASSOC);

    if(!$userInfo) {
        throw new Exception('Thông tin người dùng không tồn tại');
    }

    if($_SERVER["REQUEST_METHOD"] == "POST"){
        $projectname = trim($_POST['projectname'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $deadline = trim($_POST['deadline'] ?? '');
        
        if(empty($projectname)) {
            throw new Exception('Vui lòng điền tên dự án.');
        }

        if(empty($userInfo['id'])) {
            throw new Exception('Thông tin người dùng không hợp lệ.');
        }

        if(!empty($deadline)) {
            $currentDateTime = date('Y-m-d H:i:s');
            $deadlineDateTime = $deadline;
            
            if (strlen($deadline) <= 10) {
                $deadlineDateTime = $deadline . ' 23:59:59';
            }
            
            if (strtotime($deadlineDateTime) <= strtotime($currentDateTime)) {
                throw new Exception('Hạn chót không được bằng hoặc nhỏ hơn thời gian hiện tại.');
            }
        }

        $pdo->beginTransaction();

        $stmt1 = $pdo->prepare("INSERT INTO projectdetail (projectname, description, deadline, created_at, user_id) VALUES (?, ?, ?, NOW(), ?)");
        
        if(!$stmt1->execute([$projectname, $description, $deadline, $userInfo['id']])){
            throw new Exception("Lỗi execute statement: " . implode(", ", $stmt1->errorInfo()));
        }
        
        $newProjectId = $pdo->lastInsertId();
        $pdo->commit();
        
        ob_clean();
        echo json_encode([
            'success' => true,
            'message' => 'Tạo đề tài thành công!',
            'project_id' => $newProjectId
        ]);
        exit;
        
    } else {
        throw new Exception('Method not allowed');
    }
    
} catch (Exception $e) {

    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    ob_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage()
    ]);
    exit;
}
?>