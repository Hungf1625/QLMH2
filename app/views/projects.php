<?php 
session_start();
require_once '../core/databasePDO.php';
require_once '../core/checkIfLogin.php';
require_once '../core/getUser.php';

    $query = "SELECT * FROM projectdetail";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $projects = $stmt->fetchALL(PDO::FETCH_ASSOC);

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
    <style>
    .tableButton1 {
        margin-left: 31px;
    }

    .tableButton2 {
        margin-left: 42px;
    }

    /* .detailButton{
        padding: 10px;
        margin-top: 10px;
    } */
    </style>
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
                            if($userInfo['avatar'] == null){
                                echo '<img src="../../img/dnc.png" class="user_avatar" alt="user_avatar" width="40" height="40" class="rounded-circle">';
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
                <a href="profileEdit.php" class="dropdown-item d-block p-3 border-bottom">
                    <i class="bi bi-person me-2"></i>Chỉnh sửa thông tin cá nhân
                </a>
                <!-- <a href="help.html" class="dropdown-item d-block p-3 border-bottom">
                    <i class="bi bi-question-circle me-2"></i>Trợ giúp
                </a> -->
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
                    <a class="nav-link" href="nopbai.php">
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
                                    '<th colspan="6" style="position:relative; border: none;">'
                                ?>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h1 class="mb-0" style="flex: 1; text-align: center; padding-top: 10px;">Danh sách đề
                                    tài</h1>
                                <?php 
                                            if(isset($userInfo)){
                                                $role = strtoupper(trim($userInfo['role_id']));

                                                if($role == 'GV'){
                                                    echo '<button class="btn btn-primary mb-3 addbuttonPJ" data-bs-toggle="modal" data-bs-target="#addProjectModal"><i class="bi bi-plus-circle"></i></button>';
                                                }
                                            }
                                        ?>
                            </div>
                            <div class="input-group w-50 mx-auto" style="padding-bottom: 5px;">
                                <input type="search" 
                                    class="form-control rounded" 
                                    id="searchInput"
                                    placeholder="Nhập tên đề tài hoặc tên giảng viên" 
                                    aria-label="Search"/>
                                <button type="button" 
                                        class="btn btn-outline-primary" 
                                        onclick="searchProjects()">
                                    <i class="bi bi-search"></i> Tìm kiếm
                                </button>
                            </div>
                            </th>
                        </tr>
                        <tr class="table-primary">
                            <th class="text-center align-middle" width="80" class="text-center">ID</th>
                            <th class="text-center align-middle">Tên đề tài</th>
                            <th class="text-center align-middle">Giảng viên quản lý</th>
                            <th>Hạn chót</th>
                            <th class="text-center align-middle" width="150" class="text-center">Chi tiết</th>
                            <th class="text-center align-middle" width="180" class="text-center">Đăng ký</th>
                        </tr>
                    </thead>
                    <tbody id="projectContent"></tbody>
                </table>
            </div>
        </div>

        <!-- Modal -->
        <div class="modal fade" id="addProjectModal" tabindex="-1" aria-labelledby="addProjectModalLabel"
            aria-hidden="true">
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

                            <div class="mb-3">
                                <label for="description" class="form-label">Mô tả đề tài</label>
                                <textarea class="form-control" id="description" name="description" rows="3"></textarea>
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

        <div class="modal fade" id="addProjectDetail" tabindex="-1" aria-labelledby="addProjectDetail"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Thông tin đề tài</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <h5 class="modal-title" id="addProjectDetail">ID đề tài</h5>
                        <div id="ID" class="text-muted"></div>
                        <h5 class="modal-title" id="addProjectDetail">Tên đề tài</h5>
                        <div id="Name" class="text-muted"></div>
                        <h5 class="modal-title" id="addProjectDetail">Mô tả</h5>
                        <div id="Description" class="text-muted"></div>
                        <h5 class="modal-title" id="addProjectDetail">Giảng viên quản lý</h5>
                        <div id="Lecturer" class="text-muted"></div>
                        <h5 class="modal-title" id="addProjectDetail">Nhóm đăng ký</h5>
                        <div id="Group" class="text-muted"></div>
                        <?php
                        if($userInfo['role_id'] == 'GV'){
                            echo '<button id="delButton" class="btn btn-danger " onclick="" style="padding-top:10px;padding: 10px;margin-top: 10px;">Xóa đề tài</button>';
                            echo '<button id="delGroupButton" class="btn btn-danger" style="position: relative; right: -200px;padding: 10px;margin-top: 10px;" onclick="">Xóa nhóm khỏi đề tài</button>';
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>

    </main>

    <script>

    let allProjects = []; 

    async function tableBodyContent() {
        try {
            const response = await fetch('../core/getProjects.php');
            const result = await response.json();

            if (result.success) {
                allProjects = result.projects;
                renderTable(result.projects);
            } else {
                alert(result.message)
            }
        } catch (err) {
            console.log('error: ', err);
            alert("lỗi");
        }
    }

    function searchProjects() {
        const searchTerm = document.getElementById('searchInput').value.toLowerCase();
        
        if (!searchTerm) {
            renderTable(allProjects);
            return;
        }

        const filteredProjects = allProjects.filter(project => {
            return (
                project.projectname?.toLowerCase().includes(searchTerm) ||
                project.fullname?.toLowerCase().includes(searchTerm)
            );
        });

        renderTable(filteredProjects);
    }

    document.getElementById('searchInput').addEventListener('keyup', (e) => {
    if (e.key === 'Enter') {
        searchProjects();
    }
    });

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
    

    function renderTable(projects) {
        const tableBody = document.getElementById('projectContent');
        tableBody.innerHTML = '';

        if (!projects || projects.length === 0) {
            tableBody.innerHTML = `
            <tr>
                <td colspan="6" class="text-center">
                    <i class="bi bi-search"></i> Không tìm thấy đề tài nào
                </td>
            </tr>`;
            return;
        }

        projects.forEach(project => {
            const row = document.createElement('tr');
            row.innerHTML = `
            <td class="text-center align-middle" >${project.project_id}</td>
            <td class="text-center align-middle" >${project.projectname}</td>
            <td class="text-center align-middle" >${project.fullname || ''}</td>
            <td>${project.deadline || ''}</td>
            <td><button  class="btn btn-outline-primary btn-sm tableButton1" data-bs-toggle="modal" data-bs-target="#addProjectDetail" onclick="getProjects(${project.project_id})">Chi tiết</button></td>
            <td><button class="btn btn-outline-primary btn-sm tableButton2" onclick="confirmRegister(${project.project_id}, '${project.projectname.replace(/'/g, "\\'")}')">Đăng ký</button></td>`;
            tableBody.appendChild(row);
        });
    }

    async function getProjects(id) {
        try {
            const response = await fetch('../model/projectDetail.php?project_id=' + id);
            const result = await response.json();
            if (result.success) {
                document.getElementById('ID').innerHTML = result.projects.project_id;
                document.getElementById('Name').innerHTML = result.projects.projectname;
                document.getElementById('Description').innerHTML = result.projects.description;
                document.getElementById('Lecturer').innerHTML = result.projects.fullname;
                if (!(result.projects.group_id == 0)) {
                    document.getElementById('Group').innerHTML = result.projects.groupname;
                } else {
                    document.getElementById('Group').innerHTML = 'Chưa có nhóm đăng ký';
                }
                const delButton = document.getElementById('delButton');
                const delGroupButton = document.getElementById('delGroupButton');
                if(delButton && delGroupButton){
                    delButton.addEventListener("click",async () =>{
                        confirmDeletePJ(result.projects.project_id,result.projects.projectname);
                    })
                    delGroupButton.addEventListener("click", async () =>{
                        confirmDeleteG(result.projects.project_id,result.projects.projectname);
                    })
                }
            } else {
                alert('Lỗi: ' + result.message);
            }
        } catch (err) {
            alert(err);
        }
    }

    function confirmDeletePJ(project_id,projectName) {
        const isConfirmed = confirm(`Bạn có chắc chắn muốn xóa đề tài:\n"${projectName}"\n\nSau khi xóa không thể hủy!`);
        const action = 'deleteProject';
        if (isConfirmed) {
            deleteProject(project_id,action);
        }
    }
    function confirmDeleteG(project_id,projectName) {
        const isConfirmed = confirm(`Bạn có chắc chắn muốn xóa nhóm khỏi đề tài:\n"${projectName}"\n\nSau khi xóa không thể hủy!`);
        const action = 'deleteGroup';
        if (isConfirmed) {
            deleteProject(project_id,action);
        }
    }

    async function deleteProject(project_id,action){
        try{
            const response = await fetch(`../controller/deleteProject.php?project_id=${project_id}&action=${action}`);
            const result = await response.json();
            if(result.success){
                alert(result.message);
            }else{
                alert(result.message);
            }
        }catch(err){
            alert('' + err);
        }
    }

    function confirmRegister(projectId, projectName) {
        const isConfirmed = confirm(`Bạn có chắc chắn muốn đăng ký đề tài:\n"${projectName}"\n\nSau khi đăng ký không thể hủy!`);
        
        if (isConfirmed) {
            registerProject(projectId);
        }
    }
    async function registerProject(id) {
        try {
            const response = await fetch('../controller/registerProject.php?project_id=' + id);
            const result = await response.json();
            if (result.success) {
                alert(result.message);
            } else {
                alert(result.message);
            }
        } catch (err) {
            alert("Lỗi" + err);
        }
    }
    </script>
</body>

</html>