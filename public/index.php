<?php 
session_start();
require_once '../app/core/databasePDO.php';
require_once '../app/core/checkIfLogin.php';
require_once '../app/core/getUser.php';

$query = 'SELECT gm.*, g.groupname, g.groupimg
          FROM groupmember gm
          INNER JOIN groups g ON gm.group_id = g.group_id
          WHERE gm.user_id = ?';
$stmt = $pdo->prepare($query);
$stmt->execute([$_SESSION['user_id']]);
$groups = $stmt->fetch(PDO::FETCH_ASSOC);



?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý thư viện DNC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/style_test.css">
    <script src="assets/script.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</head>

<body>
    <header>
        <nav class="navbar navbar-expand-lg bg-white border-bottom fixed-top">
            <div class="container-fluid">
                <a href="index.php">
                    <img src="../img/sv_logo_dashboard.png" alt="Logo" width="200px" height="40px"
                        class="d-inline-block align-text-top brand_logo">
                </a>
                <div class="d-flex ms-2 me-2 ms-auto">
                    <?php 
                        if (isset($userInfo)){
                            if($userInfo['avatar'] == null){
                                echo '<img src="../img/dnc.png" class="user_avatar" alt="user_avatar" width="40" height="40" class="rounded-circle">';
                            }else{
                                echo '<img src="' . htmlspecialchars($userInfo['avatar']) . '" class="user_avatar" alt="user_avatar" width="40" height="40" class="rounded-circle">';
                            }
                        }
                        ?>
                </div>
                <button class="navbar-toggler navbarbutton" type="button" onclick="toggleSidebar()">
                    <span class="navbar-toggler-icon"></span>
                </button>

            </div>
        </nav>
    </header>

    <section>
        <!-- dropdown menu -->
        <div class="user-dropdown-menu"
            style="display: none; position: absolute; top: 60px; right: 20px; background: white; border: 1px solid #ddd; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); min-width: 200px; z-index: 1000;">
            <div class="dropdown-header p-3 border-bottom">
                <div class="d-flex align-items-center">
                    <?php 
                        if (isset($userInfo)) {
                            echo '<img scr="' . htmlspecialchars($userInfo['avatar']) . '" class="rounded-circle me-3" width="40" height="40" alt="User">';
                        }
                    ?>

                    <div>
                        <?php 
                        if (isset($userInfo)){
                            echo '<h6 class="mb-0" id="dropdown-user-name">' . htmlspecialchars($userInfo['username']) . '</h6>';
                            echo '<small class="text-muted" id="dropdown-user-email">' . htmlspecialchars($userInfo['email']) . '</small>';
                        }
                         ?>
                    </div>
                </div>
            </div>
            <div class="dropdown-body">
                <a href="../app/views/profileEdit.php" class="dropdown-item d-block p-3 border-bottom">
                    <i class="bi bi-person me-2"></i>Chỉnh sửa thông tin cá nhân
                </a>
                <a href="help.php" class="dropdown-item d-block p-3 border-bottom">
                    <i class="bi bi-question-circle me-2"></i>Trợ giúp
                </a>
                <a href="../app/controller/logout.php" class="dropdown-item d-block p-3 text-danger">
                    <i class="bi bi-box-arrow-right me-2"></i>Đăng xuất
                </a>
            </div>
        </div>
    </section>


    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>
    <!-- sidebar -->
    <aside class="sidebar-container" id="sidebar">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link active" href="">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="../app/views/projects.php">
                    <i class="bi bi-journal"></i> Đề tài
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="../app/views/groups.php">
                    <i class="bi bi-people"></i> Nhóm & Thành viên
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="../app/views/task.php">
                    <i class="bi bi-list-task"></i> Công việc
                </a>
            </li>
            <?php
            if(isset($userInfo['project_id']) && isset($userInfo['group_id'])) {
                echo '
                <li class="nav-item">
                    <a class="nav-link" href="../app/views/deadline.php">
                        <i class="bi bi-upload"></i> Mốc nộp bài
                    </a>
                </li>
                ';
            }
            ?>
            <li class="nav-item">
                <a class="nav-link" href="../app/views/re-evaluation.php">
                    <i class="bi bi-chat-dots"></i> Phúc khảo
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="../app/views/report.php">
                    <i class="bi bi-bar-chart"></i> Báo cáo
                </a>
            </li>
        </ul>
    </aside>

    <main class="main-content">
        <div class="container-fluid">
            <div class="row mt-5">
                <!-- Cột trái - 2 ảnh chồng lên nhau -->
                <div class="col-xxl-7 me-md-3">
                    <!-- cột trái khối thứ 1 -->
                    <div class="profile mb-3">
                        <div class="border-bottom border-2" style="position: relative;">
                            <h3>Thông tin cá nhân:</h3>
                            <a href="../app/views/profileEdit.php" class="profile_detail">Chỉnh sửa thông tin</a>
                        </div>
                        <div>
                            <div class="container-fluid d-flex profile_body_custom ">
                                <div class="d-flex justify-content-center align-items-center">
                                    <?php 
                                        if(isset($userInfo) && isset($userInfo['avatar'])) {
                                            if($userInfo['avatar'] == null){
                                                echo '<img class="profile_img mt-1" src="../img/dnc.png">';
                                            }else{
                                                echo '<img class="profile_img mt-1" src="' . htmlspecialchars($userInfo['avatar']) . '" alt="User Avatar">';
                                            }
                                        } 
                                        else {
                                            // Hiển thị ảnh mặc định nếu không có avatar
                                            echo '<img class="profile_img mt-1" src="../img/dnc.png" alt="Default Avatar">';
                                        }
                                    ?>
                                </div>
                                <div class="profile_text ms-2 profile_body">
                                    <div>
                                        <div class="d-flex justify-content-between">
                                            <div class="pf_Custom">
                                                <?php 
                                                if (isset($userInfo)){
                                                    if(isset($userInfo['fullname'])){
                                                        echo '<label class ="profile_Infor"><strong>Họ và tên:</strong> ' . htmlspecialchars($userInfo['fullname']) . '</label>';
                                                    }
                                                    else{
                                                        echo '<label class ="profile_Infor"><strong>Họ và tên:</strong> Chưa cập nhật</label>';
                                                    }        
                                                    if(isset($userInfo['email'])){
                                                        echo '<label class ="profile_Infor"><strong>Email:</strong> ' . htmlspecialchars($userInfo['email']) . '</label>'; echo '<br>';
                                                    }                                 
                                                    else{
                                                        echo '<label class ="profile_Infor"><strong>Email:</strong> Chưa cập nhật</label>'; echo '<br>';
                                                    }       
                                                    if(isset($userInfo['phonenumber'])){
                                                        echo '<label class ="profile_Infor"><strong>Số điện thoại:</strong> ' . htmlspecialchars($userInfo['phonenumber']) . '</label>'; echo '<br>';
                                                    }
                                                    else{
                                                        echo '<label class ="profile_Infor"><strong>Số điện thoại:</strong> Chưa cập nhật</label>'; echo '<br>';
                                                    } 
                                                    if(isset($userInfo['role_id'])){
                                                        switch($userInfo['role_id']){
                                                            case 'GV':
                                                                echo '<label class ="profile_Infor"><strong>Vai trò:</strong> Giảng viên</label>';
                                                                break;
                                                            case 'SV':
                                                                echo '<label class ="profile_Infor"><strong>Vai trò:</strong> Sinh viên</label>';
                                                                break;
                                                            case 'HD':
                                                                echo '<label class ="profile_Infor"><strong>Vai trò:</strong> Hội đồng</label>';
                                                                break;
                                                            case 'TK':
                                                                echo '<label class ="profile_Infor"><strong>Vai trò:</strong> Thư ký</label>';
                                                                break;
                                                        }
            
                                                    }      
                                                }
                                                ?>
                                            </div>
                                            <div class="pf_Custom">
                                                <?php 
                                                if (isset($userInfo)){
                                                    if(isset($userInfo['studentID'])){
                                                        echo '<label class ="profile_Infor"><strong>MSSV:</strong> ' . htmlspecialchars($userInfo['studentID']) . '</label>' ;echo '<br>';
                                                    }
                                                    else{
                                                        echo '<label class ="profile_Infor"<strong>MSSV:</strong> Chưa cập nhật</label>' ;echo '<br>';
                                                    }
                                                    if(isset($userInfo['gender'])){
                                                        echo '<label class ="profile_Infor"><strong>Giới tính:</strong> ' . htmlspecialchars($userInfo['gender']) . '</label>'; echo '<br>';
                                                    }
                                                    else{
                                                        echo '<label class ="profile_Infor"><strong>Giới tính:</strong> Chưa cập nhật</label>'; echo '<br>';
                                                    }
                                                    if(isset($userInfo['class'])){
                                                        echo '<label class ="profile_Infor"><strong>Lớp:</strong> ' . htmlspecialchars($userInfo['class']) . '</label>'; 
                                                    }
                                                    else{
                                                        echo '<label class ="profile_Infor"><strong>Lớp:</strong> Chưa cập nhật</label>'; 
                                                    }
                                                }
                                                ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- cột trái khối thứ 2 -->
                    <div class="profile mb-3">
                        <div class="border-bottom border-2">
                            <h3>Thông tin nhóm:</h3>
                        </div>
                        <div>
                            <div class="profile_text ms-2 profile_body">
                                <div class="row">
                                    <!-- Thêm row để tạo layout 2 cột -->
                                    <!-- Cột 1: Ảnh nhóm -->
                                    <div class="col-md-3 text-center">
                                        <?php
                                        if(!empty($groups['groupimg'])) {
                                            echo '<img src="'.$groups['groupimg'].'" alt="'.$groups['groupname'].'" class="img-fluid rounded" style="max-width: 100px;">';
                                        } else {
                                            echo '<img src="../img/dnc.png" alt="ảnh nhóm" class="img-fluid rounded" style="max-width: 100px;">';
                                        }
                                        ?>
                                    </div>

                                    <!-- Cột 2: Thông tin nhóm -->
                                    <div class="col-md-9">
                                        <div class="pf_Custom">
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <?php
                                                        if(isset($groups['groupname'])) {
                                                            echo '<label class="profile_Infor"><strong>Tên nhóm:</strong><br>'.$groups['groupname'].'</label>';
                                                        } else {
                                                            echo '<label class="profile_Infor"><strong>Tên nhóm:</strong><br>Chưa gia nhập nhóm</label>';
                                                        }
                                                    ?>              
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="profile_Infor"><strong>Vai trò:</strong><br>
                                                        <?php
                                                        if(isset($groups['role_in_group'])){
                                                            switch($groups['role_in_group']){
                                                            case 'leader':
                                                                echo 'Trưởng nhóm';
                                                                break;
                                                            case 'member':
                                                                echo 'Thành viên';
                                                                break;
                                                            case 'assistant':
                                                                echo 'Thư ký';
                                                                break;
                                                            default:
                                                                echo $groups['role_in_group'];
                                                            }
                                                        }
                                                        else{
                                                            echo '<label class="profile_Infor"><strong>Tên nhóm:</strong><br>Chưa gia nhập nhóm</label>';
                                                        }
                                                        ?>
                                                    </label>
                                                </div>
                                                <div class="col-md-4">
                                                    <?php
                                                        if(isset($groups['joined_at'])){
                                                            echo '<label class="profile_Infor"><strong>Ngày tham gia:</strong><br>' . date('d/m/Y', strtotime($groups['joined_at'])) . '</label>';
                                                        } else {
                                                            echo '<label class="profile_Infor"><strong>Ngày tham gia:</strong><br>Chưa gia nhập nhóm</label>';                                    
                                                        }
                                                    ?>
                                                </div>
                                            </div>

                                             <?php
                                            if(isset($groups['group_id'])){
                                                echo '<div class="mt-3" style="position: relative">';
                                                echo '<a href="../app/views/groupDetail.php?group_id='.$groups['group_id'].'" class="btn btn-primary btn-sm detailButton">Xem chi tiết</a>';
                                                echo '</div>';
                                            }
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xxl-3 profile">
                    <div class="information_header border-bottom border-2">
                        <h3>Thông báo</h3>
                    </div>
                    <div class="information_body">
                        <p>TEST</p>
                    </div>
                </div>
            </div>

            <!-- Cột phải - 1 ảnh lớn -->

        </div>
        </div>
    </main>


</body>

</html>