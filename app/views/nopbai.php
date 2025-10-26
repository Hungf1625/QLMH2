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

</head>

<body>
    <header>
        <nav class="navbar navbar-expand-lg bg-white border-bottom fixed-top">
            <div class="container-fluid">
                <a href="../../public/index.php">
                    <img src="../../img/sv_logo_dashboard.png" alt="Logo" width="200px" height="40px"
                        class="d-inline-block align-text-top brand_logo">
                </a>
                    <div class="d-flex ms-2 me-2 ms-auto"> 
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
            <?php
            if(isset($userInfo['project_id']) && isset($userInfo['group_id'])) {
                echo '
                <li class="nav-item">
                    <a class="nav-link" href="deadline.php">
                        <i class="bi bi-upload"></i> Mốc nộp bài
                    </a>
                </li>
                ';
            }
            ?>
            <?php
            if(!($userInfo['role_id'] == "SV")){
                echo '
                    <li class="nav-item">
                        <a class="nav-link" href="re-evaluation.php">
                            <i class="bi bi-chat-dots"></i> Phúc khảo
                        </a>
                    </li>   
                ';
            }
            ?>
            <?php
            if($userInfo['role_id'] == "HD"){
                echo '
                <li class="nav-item">
                    <a class="nav-link active" href="nopbai.php">
                        <i class="bi bi-bar-chart"></i> Chấm điểm
                    </a>
                </li>
                ';
            }
            ?>
        </ul>
    </aside>

    <main class="main-content">
        <div class="container-fluid">
            <div class="profile mt-5">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <?php
                                    '<th colspan="5" style="position:relative; border: none;">'
                                ?>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h1 class="mb-0" style="flex: 1; text-align: center; padding-top: 10px;">Danh sách đề
                                    tài đã được nộp</h1>
                            </div>
                            <div class="input-group w-50 mx-auto" style="padding-bottom: 5px;">
                                <input type="search" class="form-control rounded"
                                    placeholder="Nhập mã nhóm hoặc tên nhóm" aria-label="Search"
                                    aria-describedby="search-addon" />
                                <button type="button" class="btn btn-outline-primary">Tìm kiếm</button>
                            </div>
                            </th>
                        </tr>
                        <tr class="table-primary">
                            <th class="text-center align-middle" width="80" class="text-center">ID đề tài</th>
                            <th class="text-center align-middle">Tên đề tài</th>
                            <th class="text-center align-middle">Nhóm đăng ký</th>
                            <th class="text-center align-middle">Giảng viên quản lý</th>
                            <th class="text-center align-middle">Trạng thái</th>
                            <th class="text-center align-middle" width="150" class="text-center">Chi tiết</th>
                        </tr>
                    </thead>
                    <tbody id="projectContainer"></tbody>
                </table>
            </div>
        </div>
    </main>
    
    <script>
        getSubmittedPJ();
        async function getSubmittedPJ(){
            try{
                const response = await fetch(`../controller/nopbaiAction.php?action=getSubmittedPJ`);
                const result = await response.json();
                console.log(result.project);
                if(result.success){
                    renderSubmittedPJ(result.project);
                }else{
                    console.log(result.message);
                }
            }catch(err){
                console.log("Lỗi",err);
            }
        }

        function renderSubmittedPJ(projects){
            try{
                const projectContainer = document.getElementById('projectContainer');
                projectContainer.innerHTML = '';
                projects.forEach(project => {
                    const row = document.createElement('tr');
                    const status = getStatus(project.status);
                    row.innerHTML = `
                    <td class="text-muted text-center">${project.project_id}</td>
                    <td class="text-muted text-center">${project.projectname}</td>
                    <td class="text-muted text-center">${project.groupname}</td>
                    <td class="text-muted text-center">${project.fullname}</td>
                    <td class="text-muted text-center">${status}</td>
                    <td class="text-muted text-center">
                        <a href="deadlinePK.php?project_id=${project.project_id}&group_id=${project.group_id}&action=nopbai" class="detailBtn btn btn-primary btn-sm">Xem chi tiết</a>
                    </td>
                `;
                projectContainer.appendChild(row);
                });
            }catch(err){
                console.log("lỗi",err);
            }
        }

        function getStatus(status){
            switch(status){
                case 'submitted':
                    return 'Đang chờ chấm điểm';
                    break;
                case 'completed':
                    return 'Đã chấm điểm';
                    break;
            }
        }
    </script>

</body>

</html>