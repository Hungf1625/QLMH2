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
$task_id = $_GET['task_id'] ?? null;

// Validate input
if (!$group_id || !$project_id) {
    echo json_encode([
        'success' => false,
        'message' => 'Thiếu thông tin nhóm hoặc dự án'
    ]);
    exit;
}

switch($action) {
    case 'newTask':
        newTask($group_id, $project_id);
        break;
    case 'getTasks':
        getTasks($group_id, $project_id);
        break;
    case 'getTaskDetail':
        getTaskDetail($task_id, $project_id, $group_id);
        break;
    case 'delTask':
        delTask($task_id, $project_id, $group_id);
        break;
    default:
        echo json_encode([
            'success' => false,
            'message' => 'Action không hợp lệ'
        ]);
}

function getTasks($group_id, $project_id) {
    global $pdo;
    try {
        $selectQuery = "SELECT * FROM tasks WHERE group_id = ? AND project_id = ? ORDER BY created_at DESC";
        $selectStmt = $pdo->prepare($selectQuery);
        $selectStmt->execute([$group_id, $project_id]);
        $tasks = $selectStmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'tasks' => $tasks ?? []
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Lỗi khi lấy công việc: ' . $e->getMessage()
        ]);
    }
}

function getTaskDetail($task_id, $project_id, $group_id) {
    global $pdo;
    try {
        $selectQuery = "SELECT t.*, u.fullname as creator_name 
                       FROM tasks t
                       LEFT JOIN users u ON t.user_id = u.id
                       WHERE t.task_id = ? 
                       AND t.project_id = ? 
                       AND t.group_id = ?";
        $selectStmt = $pdo->prepare($selectQuery);
        $selectStmt->execute([$task_id, $project_id, $group_id]);
        $task = $selectStmt->fetch(PDO::FETCH_ASSOC);

        if (!$task) {
            throw new Exception("Không tìm thấy task");
        }

        echo json_encode([
            'success' => true,
            'task' => $task
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Lỗi khi lấy chi tiết công việc: ' . $e->getMessage()
        ]);
    }
}

function newTask($group_id, $project_id) {
    global $pdo;
    $tasktitle = $_POST['tasktitle'] ?? '';
    $description = $_POST['description'] ?? '';
    $deadline = $_POST['deadline'] ?? '';
    $user_id = $_SESSION['user_id'] ?? '';
    
    if (!$tasktitle || !$deadline) {
        echo json_encode([
            'success' => false,
            'message' => 'Vui lòng điền đầy đủ thông tin'
        ]);
        return;
    }

    try {
   
        $projectQuery = "SELECT deadline FROM projectdetail WHERE project_id = ? AND group_id = ?";
        $projectStmt = $pdo->prepare($projectQuery);
        $projectStmt->execute([$project_id, $group_id]);
        $project = $projectStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$project) {
            echo json_encode([
                'success' => false,
                'message' => 'Không tìm thấy thông tin dự án'
            ]);
            return;
        }
        
        $projectDeadline = $project['deadline'];
        $currentDate = date('Y-m-d H:i:s');
        $taskDeadline = date('Y-m-d H:i:s', strtotime($deadline));
        
  
        if ($taskDeadline < $currentDate) {
            echo json_encode([
                'success' => false,
                'message' => 'Deadline không được nhỏ hơn ngày hiện tại'
            ]);
            return;
        }
        
  
        if ($projectDeadline && $taskDeadline > $projectDeadline) {
            echo json_encode([
                'success' => false,
                'message' => 'Deadline của task không được vượt quá hạn chót của dự án ('.date('d/m/Y', strtotime($projectDeadline)).')'
            ]);
            return;
        }
        
        $insertQuery = "INSERT INTO tasks (tasktitle, description, deadline, group_id, project_id, user_id , status , created_at) 
                       VALUES (?, ?, ?, ?, ?, ? , 'pending', NOW())";
        $insertStmt = $pdo->prepare($insertQuery);
        $insertStmt->execute([$tasktitle, $description, $taskDeadline, $group_id, $project_id, $user_id]);

        echo json_encode([
            'success' => true,
            'message' => 'Tạo công việc thành công'
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Lỗi khi tạo công việc: ' . $e->getMessage()
        ]);
    }
}

function delTask($task_id, $project_id, $group_id){
    global $pdo;
    try {
        $deleteQuery = "DELETE FROM tasks WHERE task_id = ? AND project_id = ? AND group_id = ?";
        $deleteStmt = $pdo->prepare($deleteQuery);
        $deleteStmt->execute([$task_id, $project_id, $group_id]);

        echo json_encode([
            'success' => true,
            'message' => 'Xóa công việc thành công'
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Lỗi khi xóa công việc: ' . $e->getMessage()
        ]);
    }
}