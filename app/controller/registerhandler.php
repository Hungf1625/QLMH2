<?php
session_start();
include '../core/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Tạo ID duy nhất với prefix
    $id = 'USER_' . uniqid();
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $role_id = trim($_POST['role_id'] ?? 'SV'); // Mặc định role_id là 2 (Sinh Viên) nếu không có
    $confirm_password = trim($_POST['confirm_password'] ?? ''); // Nếu có confirm password field
    $gender = trim($_POST['gender']);

    // Validation chi tiết hơn
    $errors = [];
    
    // Kiểm tra thông tin cơ bản
    if (empty($username)) {
        $errors[] = "Tên đăng nhập không được để trống";
    } elseif (strlen($username) < 3 || strlen($username) > 20) {
        $errors[] = "Tên đăng nhập phải từ 3-20 ký tự";
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $errors[] = "Tên đăng nhập chỉ được chứa chữ cái, số và dấu gạch dưới";
    }
    
    if (empty($password)) {
        $errors[] = "Mật khẩu không được để trống";
    } elseif (strlen($password) < 6) {
        $errors[] = "Mật khẩu phải có ít nhất 6 ký tự";
    }
    
    // Kiểm tra confirm password nếu có
    if (!empty($confirm_password)) {
        if ($password !== $confirm_password) {
            $errors[] = "Mật khẩu xác nhận không khớp";
        }
    } else {
        $errors[] = "Vui lòng xác nhận mật khẩu";
    }
    
    // Nếu có lỗi validation, hiển thị và dừng
    if (!empty($errors)) {
        $error_message = implode("\\n", $errors);
        echo "<script>
            alert('{$error_message}');
            window.location.href = '../views/register.php';
        </script>";
        exit();
    }
    
    // Kiểm tra username và ID đã tồn tại (ĐÃ BỎ EMAIL)
    $check_sql = "SELECT id, username FROM users WHERE username = ? OR id = ?";
    $stmt = $conn->prepare($check_sql);
    
    if (!$stmt) {
        echo "<script>
            alert('Lỗi hệ thống. Vui lòng thử lại sau.');
            window.location.href = '../views/register.php';
        </script>";
        exit();
    }
    
    $stmt->bind_param("ss", $username, $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $existing = $result->fetch_assoc();
        
        if (strcasecmp($existing['username'], $username) === 0) {
            echo "<script>
                alert('Tên đăng nhập đã tồn tại!');
                window.location.href = '../views/register.php';
            </script>";
        } else {
            // Trường hợp ID trùng (rất hiếm nhưng vẫn kiểm tra)
            echo "<script>
                alert('Lỗi hệ thống. Vui lòng thử lại sau.');
                window.location.href = '../views/register.php';
            </script>";
        }
        $stmt->close();
        exit();
    }
    $stmt->close();
    
    // Hash password và insert user mới VỚI ID ĐÃ GENERATE (ĐÃ BỎ EMAIL)
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    $insert_sql = "INSERT INTO users (id, username, password, role_id, gender) VALUES (?, ?, ?, ?, ?)";
    $stmt2 = $conn->prepare($insert_sql);
    
    if (!$stmt2) {
        echo "<script>
            alert('Lỗi hệ thống. Vui lòng thử lại sau.');
            window.location.href = '../views/register.php';
        </script>";
        exit();
    }
    
    $stmt2->bind_param("sssss", $id, $username, $hashed_password, $role_id, $gender);
    
    if ($stmt2->execute()) {
        // Sử dụng ID đã generate thay vì insert_id
        $new_user_id = $id;
        
        echo "<script>
            alert('Đăng ký thành công! Bạn có thể đăng nhập ngay bây giờ.');
            window.location.href = '../views/login.php';
        </script>";
        
        // Log successful registration (optional)
        error_log("New user registered: ID=$new_user_id, Username=$username");
        
    } else {
        echo "<script>
            alert('Đăng ký thất bại. Vui lòng thử lại sau.');
            window.location.href = '../views/register.php';
        </script>";
        
        // Log error (không log sensitive data)
        error_log("Registration failed for username: $username, Error: " . $stmt2->error);
    }
    
    $stmt2->close();
}

$conn->close();
?>