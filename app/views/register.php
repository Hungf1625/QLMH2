<?php 
  session_start();
  require_once '../core/databasePDO.php';

  if(isset($_SESSION['user_id'])){
      header("location: ../index.php");
      exit();
  }


?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Document</title>

    <style>
      body {
        font-family: "Segoe UI", Arial, sans-serif;
        background: linear-gradient(135deg, #e0e7ff 0%, #f2f2f2 100%);
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
        margin: 0;
      }
      .logo-container {
        display: flex;
        flex-direction: column;
        align-items: center;
        margin-bottom: 18px;
      }
      .logo-container img {
        width: 80px;
        height: 80px;
        object-fit: contain;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 123, 255, 0.1);
        margin-bottom: 0px;
      }
      .logo-container h1 {
        font-size: 22px;
        color: #007bff;
        font-weight: 700;
        margin: 0;
      }
      .login-container {
        background: #fff;
        padding: 40px 32px;
        border-radius: 16px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
        width: 350px;
        transition: box-shadow 0.3s;
      }
      .login-container:hover {
        box-shadow: 0 12px 40px rgba(0, 0, 0, 0.18);
      }
      .login-container h2 {
        text-align: center;
        margin-bottom: 28px;
        color: #007bff;
        letter-spacing: 1px;
        font-weight: 600;
      }
      .form-group {
        margin-bottom: 18px;
      }
      .form-group label {
        display: block;
        margin-bottom: 7px;
        font-weight: 500;
        color: #333;
        letter-spacing: 0.5px;
      }
      .form-group input,
      .form-group select {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #bfc8d6;
        border-radius: 6px;
        background: #f8fafc;
        font-size: 15px;
        transition: border-color 0.2s;
        outline: none;
      }
      .form-group input:focus,
      .form-group select:focus {
        border-color: #007bff;
        background: #eef6ff;
      }
      .login-btn {
        width: 100%;
        padding: 12px;
        background: linear-gradient(90deg, #007bff 60%, #0056b3 100%);
        color: #fff;
        border: none;
        border-radius: 6px;
        font-size: 17px;
        font-weight: 600;
        cursor: pointer;
        box-shadow: 0 2px 8px rgba(0, 123, 255, 0.08);
        transition: background 0.2s;
      }
      .login-btn:hover {
        background: linear-gradient(90deg, #0056b3 60%, #007bff 100%);
      }
      .error-message {
        color: #d32f2f;
        background: #ffeaea;
        border: 1px solid #f5c2c7;
        border-radius: 4px;
        text-align: center;
        margin-bottom: 14px;
        padding: 8px;
        display: none;
        font-size: 14px;
      }
      #forgot-password {
        color: #007bff;
        text-decoration: underline;
        font-size: 14px;
        transition: color 0.2s;
      }
      #forgot-password:hover {
        color: #0056b3;
        text-decoration: none;
      }
      @media (max-width: 480px) {
        .login-container {
          width: 95vw;
          padding: 24px 8vw;
        }
      }

      .background-image {
        position: absolute;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        z-index: 0;
        background: url("../../img/hinh-nen-powerpoint-chu-de-giao-duc-54.jpg")
          center center/cover no-repeat;
        opacity: 0.18;
        pointer-events: none;
      }
      body {
        position: relative;
        z-index: 1;
      }
    </style>
    
  </head>
  <body>
    <div class="background-image"></div>
    <div
      style="
        display: flex;
        flex-direction: column;
        align-items: center;
        margin-bottom: 18px;
      "
    >
      <img
        src="../../img/dnc.png"
        alt="Logo"
        style="
          width: 80px;
          height: 80px;
          object-fit: contain;
          margin-bottom: 0px;
          border-radius: 12px;
          box-shadow: 0 2px 8px rgba(0, 123, 255, 0.1);
        "
      />
      <h1 style="font-size: 22px; color: #007bff; font-weight: 700; margin: 0">
        QUẢN LÝ ĐỀ TÀI
      </h1>
    </div>
    <div class="login-container">
      <h2>Đăng ký</h2>
      <form action="../controller/registerhandler.php" method="post">
        <div class="form-group">
          <label for="username">Tên đăng nhập:</label>
          <input type="text" id="username" name="username" style="width: 320px;" required />
        </div>
        <div class="form-group">
                <label for="gender">Giới tính</label>
                <select id="gender" name="gender" required>
                    <option value="Nam">Nam</option>
                    <option value="Nu">Nữ</option>
                </select>
            </div>
        <div class="form-group">
          <label for="password">Mật Khẩu:</label>
          <input type="password" id="password" name="password" style="width: 320px;" required />
        </div>
        <div class="form-group">
          <label for="confirm_password">Nhập lại mật khẩu:</label>
          <input
            type="password"
            id="confirm_password"
            name="confirm_password"
            style="width: 320px;"
            required
          />
        </div>
        <button type="submit" class="login-btn">Đăng Ký</button>
      </form>
      <p style="text-align: center">
        Bạn đã có tài khoản? <a href="login.php">Đăng Nhập</a>
      </p>
    </div>
  </body>
  <script>
    
  </script>
</html>
