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
    case 'uploadFile':
        uploadFile($task_id, $project_id, $group_id);
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
        // Dùng LEFT JOIN để lấy cả tasks không có file
        $selectQuery = "SELECT 
                        T.*,
                        TF.taskfile_id, 
                        TF.filepath, 
                        TF.user_id as file_uploader_id,
                        TF.filename
                    FROM tasks T
                    LEFT JOIN taskfiles TF ON T.task_id = TF.task_id
                    WHERE T.group_id = ? AND T.project_id = ? 
                    ORDER BY T.created_at DESC";
        
        $selectStmt = $pdo->prepare($selectQuery);
        $selectStmt->execute([$group_id, $project_id]);
        $tasks = $selectStmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach($tasks as &$task){
            if(!isset($task['status']) || empty($task['status'])){
                $task['status'] = 'pending'; 
            }

            if($task['taskfile_id'] !== null){
                $task['status'] = 'submitted';
            }
        }
        unset($task); 
        
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
        // Lấy thông tin task cơ bản
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

        // Lấy danh sách files của task
        $fileQuery = "SELECT 
                        tf.*,
                        u.fullname as uploader_name,
                        u.id as uploader_id
                      FROM taskfiles tf
                      LEFT JOIN users u ON tf.user_id = u.id
                      WHERE tf.task_id = ? 
                      AND tf.project_id = ? 
                      AND tf.group_id = ?
                      ORDER BY tf.taskfile_id DESC";
        
        $fileStmt = $pdo->prepare($fileQuery);
        $fileStmt->execute([$task_id, $project_id, $group_id]);
        $files = $fileStmt->fetch(PDO::FETCH_ASSOC);

        $task['files'] = $files;

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
        $deleteQuerry1= "DELETE from taskfiles WHERE task_id = ? AND project_id = ? AND group_id = ?";
        $deleteStmt1 = $pdo->prepare($deleteQuerry1);
        $deleteStmt1->execute([$task_id, $project_id, $group_id]);

        $deleteQuery2 = "DELETE FROM tasks WHERE task_id = ? AND project_id = ? AND group_id = ?";
        $deleteStmt = $pdo->prepare($deleteQuery2);
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

function uploadFile($task_id, $project_id, $group_id){
    global $pdo;
    header('Content-Type: application/json');

    $uploadDir = '../uploads/';
    $maxFileSize = 30 * 1024 * 1024;
    $allowedTypes = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'txt'];

    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    try {

        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('Lỗi upload file. Mã lỗi: ' . $_FILES['file']['error']);
        }

        $file = $_FILES['file'];
        $fileName = $file['name'];
        $fileTmpName = $file['tmp_name'];
        $fileSize = $file['size'];
        $fileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $description = $_POST['description'] ?? '';
        $user_id = $_SESSION['user_id'] ?? ''; 

        if ($fileSize > $maxFileSize) {
            throw new Exception('File quá lớn. Kích thước tối đa: 30MB');
        }

        if (!in_array($fileType, $allowedTypes)) {
            throw new Exception('Loại file không được hỗ trợ. Chấp nhận: ' . implode(', ', $allowedTypes));
        }

        $newFileName = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $fileName);
        $uploadPath = $uploadDir . $newFileName;

        if (!move_uploaded_file($fileTmpName, $uploadPath)) {
            throw new Exception('Lỗi khi lưu file');
        }

        $insertQuery = "INSERT INTO taskfiles (
            task_id, 
            project_id, 
            group_id,
            user_id,
            filename, 
            filepath,
            filesize,
            filetype,
            description
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $insertStmt = $pdo->prepare($insertQuery);
        $insertStmt->execute([
            $task_id,
            $project_id,
            $group_id,
            $user_id,
            $newFileName,     
            $uploadPath,       
            $fileSize,         
            $fileType,         
            $description       
        ]);

        $file_id = $pdo->lastInsertId();

        echo json_encode([
            'success' => true,
            'message' => 'Upload file thành công!',
            'file_id' => $file_id,
            'filename' => $newFileName
        ]);

    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}