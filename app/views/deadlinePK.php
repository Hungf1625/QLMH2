<?php 
session_start();
require_once '../core/databasePDO.php';
require_once '../core/checkIfLogin.php';
require_once '../core/getUser.php';

$group_id = $_GET['group_id'] ?? '';
$project_id = $_GET['project_id'] ?? '';
$action = $_GET['action'] ?? '';
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
    .parent {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        grid-template-rows: repeat(5, 1fr);
        gap: 8px;
    }

    .div1 {
        grid-column: span 3 / span 3;
        background-color: white;
        padding-top: 5px;
        padding-bottom: 5px;
        padding-left: 10px;
        border-radius: 10px;
    }

    .div3 {
        grid-column: span 2 / span 2;
        grid-row: span 2 / span 2;
        grid-column-start: 4;
        background-color: white;
        padding-top: 5px;
        padding-bottom: 5px;
        padding-left: 10px;
        border-radius: 10px;
        position: relative;
    }

    .div4 {
        grid-column: span 3 / span 3;
        grid-row: span 4 / span 4;
        grid-row-start: 2;

    }

    .div4 h4 {
        background-color: white;
        padding-top: 5px;
        padding-bottom: 5px;
        border-radius: 10px;
    }

    .div5 {
        grid-column: span 2 / span 2;
        grid-row: span 3 / span 3;
        grid-column-start: 4;
        grid-row-start: 3;
        background-color: white;
        padding-top: 5px;
        padding-bottom: 5px;
        padding-left: 10px;
        border-radius: 10px;
    }

    .confirmBtn {
        position: absolute;
        bottom: 50px;
    }
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
            <div class="parent mt-5">
                <div id="information" class="div1">
                    <h4>Tên đề tài</h4>
                    <h6 id="projectName" class="text-muted ps-2"></h6>
                    <h4>Mã đề tài</h4>
                    <h6 id="projectId" class="text-muted ps-2"></h6>
                    <h5>Giảng viên phụ trách</h5>
                    <h6 id="lecturerName" class="text-muted ps-2"></h6>
                </div>
                <div class="div3">
                        <div>
                            <?php
                                if($action != "nopbai"){
                                    echo '<h3 class="text-center">Thông tin phúc khảo</h3>';
                                }else{
                                    echo '<h3 class="text-center">Thông tin</h3>';
                                }    
                            ?>
                            <div class="row">
                                <div class="col-6">
                                    <h6>Tiêu đề</h6>
                                    <p id="RE_title" class="text-muted"></p>
                                    <h6>Hạn chót</h6>
                                    <p id="deadline" class="text-muted"></p>
                                </div>
                                <div class="col-6">
                                    <h6>Ngày nộp</h6>
                                    <p id="submitDay" class="text-muted"></p>
                                    <h6>Điểm số</h6>
                                    <p id="projectResult" class="text-muted"></p>
                                </div>
                            </div>
                            <h5>Mô tả</h5>
                            <p id="RE_description" class="text-muted"></p>
                            <div style="position: relative;
                                            bottom: -170px;">
                                <?php
                                    if($userInfo['role_id'] == 'HD' && $action != "nopbai"){
                                        echo '<button class="btn btn-success confirmBtn" data-bs-toggle="modal" data-bs-target="#acpPK" >Chấp nhận phúc khảo</button>"';
                                    }else if($action === "nopbai"){
                                        echo '<button class="btn btn-success confirmBtn" data-bs-toggle="modal" data-bs-target="#projectRS" >Chấm điểm đề tài</button>"';
                                    }
                                    ?>
                        </div>
                    </div>
                </div>

                <div class="div4">
                    <h4 class="text-center">Những công việc đã hoàn thành</h4>
                    <div id="tasksBody" style="height: 300px; overflow-y: auto;">

                    </div>
                </div>
                <div class="div5" style="height: 300px;">
                    <h4 class="text-center">Thành viên trong nhóm</h4>
                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                        <table class="table table-hover table-striped">
                            <thead class="table-light">
                                <tr>
                                    <th class="px-3 py-2" style="width: 25%;">MSSV</th>
                                    <th class="px-3 py-2" style="width: 50%;">Họ và tên</th>
                                    <th class="px-3 py-2" style="width: 25%;">Chức vụ</th>
                                </tr>
                            </thead>
                            <tbody id="tableBody"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="taskDetail" tabindex="-1" aria-labelledby="taskDetail" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Thông tin công việc</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-6">
                                <h5 class="modal-title" id="taskDetail">ID công việc</h5>
                                <div id="taskId" class="text-muted"></div>
                                <h5 class="modal-title" id="taskDetail">Tiêu đề công việc</h5>
                                <div id="taskTitle" class="text-muted"></div>
                                <h5 class="modal-title" id="taskDetail">Mô tả</h5>
                                <div id="Description" class="text-muted"></div>
                                <h5 class="modal-title" id="taskDetail">Người tạo</h5>
                                <div id="Creator" class="text-muted"></div>
                                <h5 class="modal-title" id="taskDetail">Ngày tạo</h5>
                                <div id="Created_at" class="text-muted"></div>
                                <h5 class="modal-title" id="taskDetail">Hạn chót</h5>
                                <div id="Deadline" class="text-muted"></div>
                            </div>
                            <div class="col-6">
                                <h5 class="modal-title" id="taskDetail">Tệp đã tải lên</h5>
                                <div id="filePath" class="text-muted"></div>
                                <h5 class="modal-title" id="taskDetail">Tên người tải tệp</h5>
                                <div id="fileUploader" class="text-muted"></div>
                                <h5 class="modal-title" id="taskDetail">Loại tệp</h5>
                                <div id="fileType" class="text-muted"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!--modal-->
        <div class="modal fade" id="acpPK" tabindex="-1" aria-labelledby="acpPK" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Chấp nhận phúc khảo</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="new_PK">
                            <div class="mb-3">
                                <label for="result" class="form-label">Điểm số</label>
                                <input type="number" class="form-control" id="result" name="result" required>
                            </div>
                            <button id="submitPK" type="submit" class="btn btn-primary">Xác nhận</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="projectRS" tabindex="-1" aria-labelledby="projectRS" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Chấm điểm</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="new_RS">
                            <div class="mb-3">
                                <label for="result" class="form-label">Điểm số</label>
                                <input type="number" class="form-control" id="result" name="result" required>
                            </div>
                            <button id="submitRS" type="submit" class="btn btn-primary">Xác nhận</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </main>

    <script>
    getInformation(<?php echo $project_id ?>, <?php echo $group_id ?>);
    getCompletedTasks(<?php echo $project_id ?>, <?php echo $group_id ?>);
    getGroupMembers(<?php echo $group_id ?>);

    document.getElementById('new_PK').addEventListener('submit', async (e) => {
        const project_id = <?php echo $project_id ?>;
        const group_id = <?php echo $group_id ?>;
        try {
            const formData = new FormData(e.target);
            const response = await fetch(
                `../controller/reevaluationAction.php?action=insertResult&project_id=${project_id}&group_id=${group_id}`, {
                    method: 'POST',
                    body: formData
                });
            const result = await response.json();
            if (result.success) {
                Alert("Đã cập nhật điểm thành công");
                window.location.reload();
            } else {
                console.log("Lỗi:", result.message);
            }
        } catch (err) {
            console.log("Lỗi:", err);
        }
    });

    document.getElementById('new_RS').addEventListener('submit', async (e) =>{
        const project_id = <?php echo $project_id ?>;
        const group_id = <?php echo $group_id ?>;
        try {
            const formData = new FormData(e.target);
            const response = await fetch(
                `../controller/nopbaiAction.php?action=insertResultPJ&project_id=${project_id}&group_id=${group_id}`, {
                    method: 'POST',
                    body: formData
                });
            const result = await response.json();
            if (result.success) {
                Alert("Đã cập nhật điểm thành công");
                window.location.href = 'nopbai.php';
            } else {
                console.log("Lỗi:", result.message);
            }
        } catch (err) {
            console.log("Lỗi:", err);
        }
    });

    async function getInformation(project_id, group_id) {
        try {
            const response = await fetch(
                `../controller/reevaluationAction.php?action=getProjectInfor&project_id=${project_id}&group_id=${group_id}`
                );
            const result = await response.json();
            if (result.success) {
                renderInfor(result.project);
            } else {
                console.log(result.message);
            }
        } catch (err) {
            console.log("Lỗi", err);
        }
    }

    async function getGroupMembers(group_id) {
        try {
            const response = await fetch(
                `../controller/reevaluationAction.php?action=getGroupMembers&group_id=${group_id}`);
            const result = await response.json();
            if (result.success) {
                renderGroupMembers(result.members);
            } else {
                alert(result.message);
            }
        } catch (err) {
            console.log("Lỗi", err);
        }
    }

    async function getCompletedTasks(project_id, group_id) {
        try {
            const response = await fetch(
                `../controller/reevaluationAction.php?action=getCompletedTasks&project_id=${project_id}&group_id=${group_id}`
                );
            const result = await response.json();
            console.log(result);
            if (result.success) {
                renderTasks(result.tasks);
            } else {
                console.log(result.message);
            }
        } catch (err) {
            console.log("Lỗi", err);
        }
    }

    function renderGroupMembers(members) {
        try {
            const tableContainer = document.getElementById('tableBody');

            if (!tableContainer) {
                console.error('Không tìm thấy element với id tableBody');
                return;
            }

            tableContainer.innerHTML = '';

            if (!members || members.length === 0) {
                tableContainer.innerHTML = `
                        <tr>
                            <td colspan="3" class="text-center text-muted py-4">
                                <i class="bi bi-people"></i> Không có thành viên nào
                            </td>
                        </tr>
                    `;
                return;
            }

            members.forEach(member => {
                const row = document.createElement('tr');

                // Tạo badge cho role
                let roleBadge = '';
                if (member.role_in_group === 'leader') {
                    roleBadge = '<span class="badge bg-primary">Trưởng nhóm</span>';
                } else {
                    roleBadge = '<span class="badge bg-secondary">Thành viên</span>';
                }

                row.innerHTML = `
                        <td class="px-4 py-3">${member.MSSV || 'N/A'}</td>
                        <td class="px-4 py-3">${member.fullname || 'Không có tên'}</td>
                        <td class="px-4 py-3">${roleBadge}</td>
                    `;
                tableContainer.appendChild(row);
            });

        } catch (err) {
            console.error("Lỗi khi render danh sách thành viên:", err);

            const tableContainer = document.getElementById('tableBody');
            if (tableContainer) {
                tableContainer.innerHTML = `
                        <tr>
                            <td colspan="3" class="text-center text-danger py-4">
                                <i class="bi bi-exclamation-triangle"></i> Lỗi khi tải danh sách thành viên
                            </td>
                        </tr>
                    `;
            }
        }
    }

    function renderInfor(project) {
        const projectName = document.getElementById('projectName');
        const projectId = document.getElementById('projectId');
        const lecturerName = document.getElementById('lecturerName');
        const deadline = document.getElementById('deadline');
        const submitDay = document.getElementById('submitDay');
        const projectResult = document.getElementById('projectResult');
        const RE_title = document.getElementById('RE_title');
        const RE_description = document.getElementById('RE_description');

        if (projectName) projectName.innerHTML = project.projectname || 'Không có tên';
        if (projectId) projectId.innerHTML = project.project_id || 'N/A';
        if (lecturerName) lecturerName.innerHTML = project.lecturer_name || 'Chưa có GVHD';

        if (deadline) {
            if (project.deadline) {
                const deadlineDate = new Date(project.deadline);
                deadline.innerHTML = deadlineDate.toLocaleDateString('vi-VN');
            } else {
                deadline.innerHTML = 'Chưa có deadline';
            }
        }

        if (submitDay) {
            if (project.submitted_at) {
                const submittedDate = new Date(project.submitted_at);
                submitDay.innerHTML = submittedDate.toLocaleDateString('vi-VN');
            } else {
                submitDay.innerHTML = 'Chưa nộp bài';
            }
        }

        if (projectResult) {
            if (project.result === null || project.result === '') {
                projectResult.innerHTML = '<span class="text-muted">Chưa có điểm</span>';
            } else {
                projectResult.innerHTML = `<span class="text-success fw-bold">${project.result}</span>`;
            }
        }

        if (RE_title) {
            RE_title.innerHTML = project.RE_title || project.revaluation_title ||
                '<span class="text-muted">Không có yêu cầu phúc khảo</span>';
        }

        if (RE_description) {
            RE_description.innerHTML = project.RE_description || project.revaluation_description ||
                '<span class="text-muted">Không có mô tả</span>';
        }
    }

    function renderTasks(Tasks) {
        try {
            const tasksContainer = document.getElementById('tasksBody');
            tasksContainer.innerHTML = '';
            if (Tasks && Tasks.length > 0) {
                Tasks.forEach(task => {
                    const taskElement = document.createElement('div');
                    taskElement.className = 'task_body mt-3';
                    taskElement.innerHTML = `
                                <div class="task_content_completed pl-10px">
                                    <div class="task_information">
                                        <h4 class="task_title">${task.tasktitle}</h4>
                                        <p class="task_description">${task.description}</p>
                                        <div class="d-flex justify-content-between">
                                            <div class="task_status_completed">${task.status}</div>
                                            <button class="task_detailbutton" data-bs-toggle="modal" data-bs-target="#taskDetail" onclick="getTask(${task.task_id}, ${task.project_id}, ${task.group_id})">Xem chi tiết</button>
                                        </div>
                                    </div>
                                </div>
                            `;
                    tasksContainer.appendChild(taskElement);
                });
            } else {
                tasksContainer.innerHTML = '<p class="text-muted">Không có công việc đã hoàn thành</p>';
            }
        } catch (err) {
            console.log("Lỗi", err);
        }
    }

    async function getTask(task_id, project_id, group_id) {
        try {
            if (!task_id || !project_id || !group_id) {
                throw new Error('Thiếu thông tin task');
            }
            const response = await fetch(
                `../controller/taskAction.php?task_id=${task_id}&project_id=${project_id}&group_id=${group_id}&action=getTaskDetail`
            );
            const result = await response.json();
            if (result.success && result.task) {

                const created = new Date(result.task.created_at).toLocaleDateString('vi-VN');
                const deadline = new Date(result.task.deadline).toLocaleDateString('vi-VN');

                document.getElementById('taskId').innerText = result.task.task_id;
                document.getElementById('taskTitle').innerText = result.task.tasktitle;
                document.getElementById('Description').innerText = result.task.description;
                document.getElementById('Creator').innerText = result.task.creator_name;
                document.getElementById('Created_at').innerText = created;
                document.getElementById('Deadline').innerText = deadline;


                const submitBtn = document.getElementById('submitTask');
                const deleteTaskBtn = document.getElementById('delTaskButton');

                if (submitBtn && deleteTaskBtn) {
                    if (result.task.role_in_group === 'leader') {

                        if (result.task.status === 'submitted') {
                            submitBtn.style.display = 'block';
                            submitBtn.dataset.taskId = result.task.task_id;
                            submitBtn.dataset.projectId = result.task.project_id;
                            submitBtn.dataset.groupId = result.task.group_id;
                        } else {
                            submitBtn.style.display = 'none';
                            delete submitBtn.dataset.taskId;
                            delete submitBtn.dataset.projectId;
                            delete submitBtn.dataset.groupId;
                        }


                        deleteTaskBtn.style.display = 'block';
                        deleteTaskBtn.dataset.taskId = result.task.task_id;
                        deleteTaskBtn.dataset.projectId = result.task.project_id;
                        deleteTaskBtn.dataset.groupId = result.task.group_id;

                    } else {
                        submitBtn.style.display = 'none';
                        deleteTaskBtn.style.display = 'none';

                        delete submitBtn.dataset.taskId;
                        delete submitBtn.dataset.projectId;
                        delete submitBtn.dataset.groupId;
                        delete deleteTaskBtn.dataset.taskId;
                        delete deleteTaskBtn.dataset.projectId;
                        delete deleteTaskBtn.dataset.groupId;
                    }
                } else {
                    console.warn('Một hoặc cả hai nút không tồn tại trong DOM');
                }

                if (result.task.files.filepath) {
                    const fileLink = document.createElement('a');
                    fileLink.href = result.task.files.filepath;
                    fileLink.innerText = 'Tải tệp';
                    fileLink.target = '_blank';
                    document.getElementById('filePath').innerHTML = '';
                    document.getElementById('filePath').appendChild(fileLink);
                } else {
                    document.getElementById('filePath').innerText = 'Chưa có tệp nào được tải lên.';
                }
                document.getElementById('fileUploader').innerText = result.task.files.uploader_name ||
                    'Chưa có người tải lên';
                document.getElementById('fileType').innerText = result.task.files.filetype || 'Chưa có loại tệp';

            } else {
                throw new Error(result.message || 'Không thể lấy thông tin task');
            }
        } catch (err) {
            console.error('Lỗi:', err);
            alert('Lỗi khi lấy chi tiết công việc: ' + err.message);
        }
    }
    </script>

</body>

</html>