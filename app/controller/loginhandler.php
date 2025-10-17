<?php
session_start();
include '../core/database.php'; // File kết nối CSDL

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']); // Lấy và làm sạch tên đăng nhập từ form
    $password = trim($_POST['password']); // Lấy và làm sạch mật khẩu từ form
    $role = trim($_POST['role_id']); // Lấy role từ form

    // Kiểm tra thông tin đăng nhập
    if (!empty($username) && !empty($password) && !empty($role)) {
        // Thực hiện truy vấn để kiểm tra người dùng (lấy id, username, password, role_id)
        $sql = "SELECT id, username, password, role_id FROM users WHERE username = ? AND role_id = ?";
        $stmt = $conn->prepare($sql);
        
        if ($stmt) {
            $stmt->bind_param("ss", $username, $role); // Bind cả username và role
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();
                
                // Verify password using password_verify() thay vì so sánh trực tiếp
                if (password_verify($password, $user['password'])) {
                    // Đăng nhập thành công
                    $_SESSION['user_id'] = $user['id']; 
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role_id'] = $user['role_id']; // Thêm role vào session
                    
                    // Regenerate session ID để tránh session fixation
                    session_regenerate_id(true);
                    
                    echo "<script>                     
                        window.location.href = '../../public/index.php'; // Chuyển hướng về trang chính
                    </script>";
                    exit();
                } else {
                    // Mật khẩu không đúng
                    echo "<script>
                        alert('Tên đăng nhập hoặc mật khẩu không đúng. Vui lòng thử lại.');
                        window.location.href = '../views/login.php';
                    </script>";
                }
            } else {
                // Không tìm thấy username với role tương ứng
                echo "<script>
                    alert('Tên đăng nhập, vai trò hoặc mật khẩu không đúng. Vui lòng thử lại.');
                    window.location.href = '../views/login.php';
                </script>";
            }
            $stmt->close();
        } else {
            echo "<script>
                alert('Lỗi hệ thống. Vui lòng thử lại sau.');
                window.location.href = '../../public/login.php';
            </script>";
        }
    } else {
        echo "<script>
            alert('Vui lòng điền đầy đủ thông tin.');
            window.location.href = '../../public/login.php';
        </script>";
    }
}

// Hàm để hash password khi tạo tài khoản mới (sử dụng trong registration script)
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

?>