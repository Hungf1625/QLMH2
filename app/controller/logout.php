
<?php
session_start();

// Xóa tất cả các biến session
$_SESSION = array();

// Xóa session cookie nếu có
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Hủy session
session_destroy();

// Ngăn cache trang để tránh quay lại sau khi đăng xuất
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Chuyển hướng về trang đăng nhập hoặc trang chủ
header("Location: ../views/login.php"); // Thay đổi đường dẫn phù hợp với trang đăng nhập của bạn
exit();
?>