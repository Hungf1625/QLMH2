<?php 
session_start();
header('Content-Type: application/json');
require_once '../core/databasePDO.php';
require_once '../core/getUser.php';

if (!isset($userInfo)) {
    echo json_encode([
        'success' => false,
        'message' => 'Người dùng chưa đăng nhập'
    ]);
    exit;
}

$action = $_GET['action'] ?? '';
$group_id = $_GET['group_id'] ?? null;
$project_id = $_GET['project_id'] ?? null;
// $task_id = $_GET['task_id'] ?? null;
if(isset($_GET['role_id'])){
    $role_id = $_GET['role_id'];
}

switch($action){
    case 'getSubmittedPJ':
        getSubmittedPJ();
        break;
    case 'insertResultPJ':
        insertResultPJ($project_id,$group_id);
        break;
}

function getSubmittedPJ(){
    global $pdo;
    try {
        $query = 'SELECT PD.*,U.fullname,G.groupname
                  FROM projectdetail PD
                  INNER JOIN users U ON PD.user_id = U.id
                  INNER JOIN groups G ON PD.group_id = G.group_id
                  WHERE status = "submitted"';
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if ($projects) {
            echo json_encode([
                'success' => true,
                'project' => $projects,
                'message' => 'Đã tìm thấy project đã nộp'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Không tìm thấy project đã nộp'
            ]);
        }
        
    } catch(Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Lỗi hệ thống: ' . $e->getMessage()
        ]);
    }
}

function insertResultPJ($project_id, $group_id) {
    global $pdo;
    global $userInfo;
    
    $result = trim($_POST['result']);
    
    if (empty($result)) {
        throw new Exception("Kết quả không được để trống");
    }
    
    if (empty($project_id) || empty($group_id)) {
        throw new Exception("Project ID và Group ID không được để trống");
    }
    
    try {
        $query = 'UPDATE projectdetail SET result = ?, lecturer_grade = ?, status = "approved" WHERE project_id = ? AND group_id = ?';
        $stmt = $pdo->prepare($query);
        
        $success = $stmt->execute([
            $result,
            $userInfo['id'], 
            $project_id,
            $group_id
        ]);
        
        if ($success && $stmt->rowCount() > 0) {
            echo "<script>
                alert('Cập nhật kết quả thành công');
                window.location.href = '../views/re-evaluation.php';
            </script>";
            exit();
        } else {
            throw new Exception("Cập nhật kết quả thất bại. Có thể bản ghi không tồn tại.");
        }
        
    } catch (Exception $e) {
        echo "<script>
            alert('Lỗi: " . addslashes($e->getMessage()) . "');
            window.location.href = '../views/re-evaluation.php';
        </script>";
        exit();
    }
}
?>