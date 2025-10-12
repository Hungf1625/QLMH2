<?php 
session_start();
require_once '../core/databasePDO.php';
require_once '../core/checkIfLogin.php';
require_once '../core/getUser.php';

?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý thư viện DNC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../../public/assets/style.css">
    <script src="../../public/assets/script_test.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

</head>

<body>
    <header>
        <nav class="navbar navbar-expand-lg bg-white border-bottom fixed-top">
            <div class="container-fluid">
                <a href="../../public/index.php">
                    <img src="../../img/sv_logo_dashboard.png" alt="Logo" width="200px" height="40px"
                        class="d-inline-block align-text-top brand_logo">
                </a>
                    <div class="d-flex ms-2 ms-auto">
                        <?php 
                        if (isset($userInfo)){
                            echo '<img src="' . htmlspecialchars($userInfo['avatar']) . '" class="user_avatar" alt="user_avatar" width="40" height="40" class="rounded-circle">';
                        }
                        ?>
                    </div>
                <button class="navbar-toggler navbarbutton" type="button" onclick="toggleSidebar()">
                    <span class="navbar-toggler-icon"></span>
                </button>
                
            </div>
        </nav>
    </header>

    <section> <!-- dropdown menu -->
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
                <a href="profileEdit.php" class="dropdown-item d-block p-3 border-bottom">
                    <i class="bi bi-person me-2"></i>Thông tin cá nhân
                </a>
                <a href="help.html" class="dropdown-item d-block p-3 border-bottom">
                    <i class="bi bi-question-circle me-2"></i>Trợ giúp
                </a>
                <a href="../controller/logout.php" class="dropdown-item d-block p-3 text-danger">
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
                <a class="nav-link" href="../../public/index.php">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="projects.php">
                    <i class="bi bi-journal"></i> Đề tài
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="groups.php">
                    <i class="bi bi-people"></i> Nhóm & Thành viên
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link " href="task.php">
                    <i class="bi bi-list-task"></i> Công việc
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="deadline.php">
                    <i class="bi bi-upload"></i> Mốc nộp bài
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="re-evaluation.php">
                    <i class="bi bi-chat-dots"></i> Phúc khảo
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="report.php">
                    <i class="bi bi-bar-chart"></i> Báo cáo
                </a>
            </li>
        </ul>
    </aside>

    <main class="main-content">
        <div class="">
            
        </div>
    </main>


</body>

</html>