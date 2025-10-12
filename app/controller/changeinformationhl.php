<?php
session_start();

include '../core/database.php';
include '../core/databasePDO.php';
include '../core/getUser.php';


class UserHandler {
    private $conn;
    private $table = 'users';

    public function __construct($db) {
        $this->conn = $db;
    }

    // Lấy thông tin người dùng theo ID (SỬA CHO MYSQLI)
    public function getUserById($id) {
        $query = "SELECT * FROM {$this->table} WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc(); // Sửa thành fetch_assoc() cho MySQLi
    }

    // Cập nhật thông tin người dùng (SỬA CHO MYSQLI)
    public function updateUserInfo($id, $fullname, $phonenumber, $email) {
        $query = "UPDATE " . $this->table . " 
                  SET 
                      fullname = ?,
                      phonenumber = ?,
                      email = ?,
                      updated_at = NOW()
                  WHERE id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ssss", $fullname, $phonenumber, $email, $id);
        return $stmt->execute();
    }

    // Ghi log hoạt động (SỬA CHO MYSQLI)
    public function logActivity($id, $activity, $description = null) {
        // Kiểm tra nếu bảng log tồn tại
        $query = "INSERT INTO user_activity_log (user_id, activity, description, ip_address, user_agent, created_at) 
                  VALUES (?, ?, ?, ?, ?, NOW())";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("sssss", $id, $activity, $description, $_SERVER['REMOTE_ADDR'] ?? 'unknown', $_SERVER['HTTP_USER_AGENT'] ?? 'unknown');
        return $stmt->execute();
    }
}

// Hàm validate và sanitize input
function validateAndSanitize($data) {
    $errors = [];
    $clean_data = [];

    // Validate fullname
    $fullname = trim($data['fullname'] ?? '');
    if (!empty($fullname)) {
        if (strlen($fullname) > 100) {
            $errors[] = "Họ và tên không được vượt quá 100 ký tự";
        } else {
            $clean_data['fullname'] = htmlspecialchars($fullname, ENT_QUOTES, 'UTF-8');
        }
    } else {
        $clean_data['fullname'] = null;
    }

    // Validate phone number
    $phone = trim($data['phonenumber'] ?? '');
    if (!empty($phone)) {
        // Loại bỏ các ký tự không phải số
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($phone) < 10 || strlen($phone) > 15) {
            $errors[] = "Số điện thoại phải có từ 10-15 ký tự số";
        } else {
            $clean_data['phonenumber'] = $phone;
        }
    } else {
        $clean_data['phonenumber'] = null;
    }

    // Validate email
    $email = trim($data['email'] ?? '');
    if (!empty($email)) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Định dạng email không hợp lệ";
        } elseif (strlen($email) > 150) {
            $errors[] = "Email không được vượt quá 150 ký tự";
        } else {
            $clean_data['email'] = $email;
        }
    } else {
        $clean_data['email'] = null;
    }

    return ['errors' => $errors, 'data' => $clean_data];
}

// Hàm hiển thị alert và redirect
function showAlertAndRedirect($message, $isSuccess = true) {
    echo "<script>";
    echo "alert('" . addslashes($message) . "');";
    if ($isSuccess) {
        echo "window.location.href = '../../public/index.php';";
    } else {
        echo "window.location.href = '../views/profileEdit.php';";
    }
    echo "</script>";
    exit;
}

// Kiểm tra phương thức request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    showAlertAndRedirect('Phương thức không được hỗ trợ', false);
}

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    showAlertAndRedirect('Bạn cần đăng nhập để thực hiện thao tác này', false);
}

try {
    // Khởi tạo UserHandler với kết nối MySQLi
    $userHandler = new UserHandler($conn);
    
    $user_id = $_SESSION['user_id'];
    
    // Lấy thông tin người dùng hiện tại
    $current_user = $userHandler->getUserById($user_id);
    if (!$current_user) {
        showAlertAndRedirect('Không tìm thấy thông tin người dùng', false);
    }

    // Validate và sanitize dữ liệu đầu vào
    $validation = validateAndSanitize($_POST);
    
    if (!empty($validation['errors'])) {
        $error_message = 'Dữ liệu không hợp lệ:\\n' . implode('\\n', $validation['errors']);
        showAlertAndRedirect($error_message, false);
    }

    $clean_data = $validation['data'];

    // Kiểm tra xem có thay đổi gì không
    $changes = [];
    
    // So sánh fullname
    $current_fullname = $current_user['fullname'] ?? null;
    $new_fullname = $clean_data['fullname'] ?? null;
    if ($current_fullname !== $new_fullname) {
        $old_fullname = $current_fullname ?: '(trống)';
        $new_fullname_display = $new_fullname ?: '(trống)';
        $changes[] = 'Họ và tên: ' . $old_fullname . ' → ' . $new_fullname_display;
    }
    
    // So sánh phonenumber
    $current_phone = $current_user['phonenumber'] ?? null;
    $new_phone = $clean_data['phonenumber'] ?? null;
    if ($current_phone !== $new_phone) {
        $old_phone = $current_phone ?: '(trống)';
        $new_phone_display = $new_phone ?: '(trống)';
        $changes[] = 'Số điện thoại: ' . $old_phone . ' → ' . $new_phone_display;
    }
    
    // So sánh email
    $current_email = $current_user['email'] ?? null;
    $new_email = $clean_data['email'] ?? null;
    if ($current_email !== $new_email) {
        $old_email = $current_email ?: '(trống)';
        $new_email_display = $new_email ?: '(trống)';
        $changes[] = 'Email: ' . $old_email . ' → ' . $new_email_display;
    }

    if (empty($changes)) {
        showAlertAndRedirect('Không có thay đổi nào để cập nhật', false);
    }

    // Kiểm tra email trùng (nếu email được thay đổi)
    if ($new_email && $new_email !== $current_email) {
        $check_email_sql = "SELECT id FROM users WHERE email = ? AND id != ?";
        $stmt = $conn->prepare($check_email_sql);
        $stmt->bind_param("ss", $new_email, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            showAlertAndRedirect('Email đã được sử dụng bởi người dùng khác', false);
        }
        $stmt->close();
    }

    // Cập nhật thông tin
    $update_success = $userHandler->updateUserInfo(
        $user_id,
        $clean_data['fullname'],
        $clean_data['phonenumber'],
        $clean_data['email']
    );

    if (!$update_success) {
        throw new Exception('Không thể cập nhật thông tin');
    }

    // Ghi log hoạt động (nếu có bảng log)
    try {
        $activity_description = 'Cập nhật thông tin: ' . implode(', ', $changes);
        $userHandler->logActivity($user_id, 'update_profile', $activity_description);
    } catch (Exception $e) {
        // Log activity thất bại không ảnh hưởng đến việc cập nhật chính
        error_log('Log activity failed: ' . $e->getMessage());
    }

    // Cập nhật session nếu cần
    $_SESSION['fullname'] = $clean_data['fullname'] ?? $_SESSION['fullname'];

    // Hiển thị thông báo thành công và redirect
    showAlertAndRedirect('Cập nhật thông tin thành công!', true);

} catch (Exception $e) {
    error_log('Update Profile Error: ' . $e->getMessage());
    showAlertAndRedirect('Có lỗi xảy ra: ' . $e->getMessage(), false);
}

?>