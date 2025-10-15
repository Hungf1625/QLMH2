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
    <link rel="stylesheet" href="../../public/assets/style_test.css">
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
                <a class="nav-link active" href="projects.php">
                    <i class="bi bi-journal"></i> Đề tài
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="groups.php">
                    <i class="bi bi-people"></i> Nhóm & Thành viên
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="task.php">
                    <i class="bi bi-list-task"></i> Công việc
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="deadline.php">
                    <i class="bi bi-upload"></i> Mốc nộp bài
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="re-evaluation.php">
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
        <div class="container-fluid">
            <div class="profile mt-5">
                <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th colspan="7" style="position:relative; border: none;">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h1 class="mb-0" style="flex: 1; text-align: center;">Danh sách đề tài</h1>
                                        <?php 
                                            if(isset($userInfo)){
                                                $role = strtoupper(trim($userInfo['role_id']));

                                                if($role == 'GV'){
                                                    echo '<button class="btn btn-primary mb-3 addbutton" data-bs-toggle="modal" data-bs-target="#addProjectModal"><i class="bi bi-plus-circle"></i></button>';
                                                }
                                            }
                                        ?>
                                    </div>
                                    <div class="input-group w-50 mx-auto">
                                        <input type="search" class="form-control rounded"
                                            placeholder="Nhập mã nhóm hoặc tên nhóm" aria-label="Search"
                                            aria-describedby="search-addon" />
                                        <button type="button" class="btn btn-outline-primary">Tìm kiếm</button>
                                    </div>
                                </th>
                            </tr>
                            <tr class="table-primary">
                                <th width="80" class="text-center">STT</th>
                                <th>Tên đề tài</th>
                                <th>Mô tả</th>
                                <th>Tên nhóm đăng ký</th>
                                <th>Hạn chót</th>
                                <th width="150" class="text-center">Chi tiết</th>
                                <th width="180" class="text-center">Đăng ký</th>
                            </tr>
                        </thead>
                        <tbody id="projectContent">
                            
                        </tbody>
                    </table>
            </div>
        </div>

        <!-- Button trigger modal -->

        <!-- Modal -->
        <div class="modal fade" id="addProjectModal" tabindex="-1" aria-labelledby="addProjectModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addProjectModalLabel">Thêm Đề Tài Mới</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="newPJ">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="projectname" class="form-label">Tên Đề Tài</label>
                                <input type="text" class="form-control" id="projectname" name="projectname" required>
                            </div>

                            <!-- Thêm các trường khác nếu cần -->
                            <div class="mb-3">
                                <label for="description" class="form-label">Mô tả đề tài</label>
                                <textarea class="form-control" id="description" name="description"
                                    rows="3"></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="deadline" class="form-label">Hạn chót</label>
                                <input type="date" class="form-control" id="deadline" name="deadline">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                            <button type="submit" class="btn btn-primary">Tạo đề tài</button>
                        </div>
                    </form>

                </div>
            </div>
        </div>

    </main>

    <script>
        document.getElementById('newPJ').addEventListener('submit', async (e) => {
            e.preventDefault();    
            const formData = new FormData(e.target);
            try {
                const response = await fetch('../model/newProject.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();
                
                if (result.success) {
                    alert(result.message);
                } else {
                    alert('Lỗi: ' + result.message);
                }

            } catch (err) {
                console.log('Error:', err);
                alert("Lỗi kết nối");
            }
    });

    tableBodyContent();
    setInterval(tableBodyContent, 2000) 
    async function tableBodyContent(){
        try{
            const response = await fetch('../core/getProjects.php');

            const result = await response.json();
            console.log(result);

            if(result.success){
                renderTable(result.projects);
            }else{
                alert(result.message)
            }

        }catch(err){
            console.log('error: ', err);
            alert("lỗi");
        }
    }

    function renderTable(projects) {
    const tableBody = document.getElementById('projectContent');
    tableBody.innerHTML = '';

    if (!projects || projects.length === 0) {
        tableBody.innerHTML = `
            <tr>
                <td colspan="7" class="text-center">Không có dự án nào</td>
            </tr>
        `;
        return;
    }
    
    projects.forEach(project => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${project.project_id}</td>
            <td>${project.projectname}</td>
            <td>${project.description}</td>
            <td>${project.description || ''}</td>
            <td>${project.deadline || ''}</td>
            <td><button class="btn btn-primary">Chi tiết</button></td>
            <td><button class="btn btn-primary">Đăng ký</button></td>
        `;
        tableBody.appendChild(row);
    });
    }
    </script>

</body>

</html>