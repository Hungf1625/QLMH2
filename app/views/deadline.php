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
        bottom: 20px;
    }

    .yellowBtn {
        position: absolute;
        bottom: 20px;
        right: 10px;
    }

    .cancelBtn {
        position: absolute;
        bottom: 20px;
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
                    <a class="nav-link active" href="deadline.php">
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
                    <div class="row">
                     <h3 class="text-center">Nộp bài</h3>
                        <div class="col-6">
                            <h6>Hạn chót</h6>
                            <p id="deadline" class="text-muted"></p>
                            <h6>Ngày còn lại</h6>
                            <p id="remainDay" class="text-muted"></p>
                            <h6>Trạng thái</h6>
                            <p id="projectStatus" class="text-muted"></p>
                        </div>
                        <div class="col-6">               
                            <h6>Trạng thái phúc khảo</h6>
                            <p id="reEvaStatus" class="text-muted"></p>
                            <h6>Điểm số</h6>
                            <p id="projectRS" class="text-muted"></p>
                        </div>
                    </div>

                    <div style="position: relative;
                                    bottom: -170px;">
                        <?php
                            if($userInfo['role_in_group'] == 'leader'){
                                echo '<button id="submitBtn" class="btn btn-success confirmBtn" onclick="">Xác nhận nộp bài</button>';
                                echo '<button id="cancelBtn" class="btn btn-danger cancelBtn" onclick="">Hủy nộp bài</button>';
                                echo '<button id="requestRevalution" class="btn btn-warning yellowBtn" data-bs-toggle="modal"
                                data-bs-target="#rqEvalution" >Yêu cầu phúc khảo</button>';
                            }
                            ?>
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
        <div class="modal fade" id="rqEvalution" tabindex="-1" aria-labelledby="rqEvalution" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Yêu cầu phúc khảo</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="new_PK">
                            <div class="mb-3">
                                <label for="title" class="form-label">Tiêu đề</label>
                                <input type="text" class="form-control" id="title" name="title" required>
                            </div>
                            <div class="mb-3">
                                <label for="description" class="form-label">Mô tả</label>
                                <textarea class="form-control" id="description" name="description" rows="4"
                                    required></textarea>
                            </div>
                            <button id="submitReEvaluation" type="submit" class="btn btn-primary">Gửi yêu cầu</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </main>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        renderProjectInfor(<?php echo $userInfo['project_id']?>, <?php echo $userInfo['group_id']?>);
        renderGroupmembers(<?php echo $userInfo['project_id']?>, <?php echo $userInfo['group_id']?>);
        renderCompletedtasks(<?php echo $userInfo['project_id']?>, <?php echo $userInfo['group_id']?>);
    })

    async function renderProjectInfor(project_id, group_id) {
        try {
            const response = await fetch(
                `../controller/deadlineAction.php?action=getProjectInfor&project_id=${project_id}&group_id=${group_id}`
            );
            const result = await response.json();

            const projectName = document.getElementById('projectName');
            const projectId = document.getElementById('projectId');
            const lecturerName = document.getElementById('lecturerName');
            const deadline = document.getElementById('deadline');
            const remainDay = document.getElementById('remainDay');
            const submitBtn = document.getElementById('submitBtn');
            const reEvaStatus = document.getElementById('reEvaStatus');
            const projectStatus = document.getElementById('projectStatus');
            const projectRS = document.getElementById('projectRS');
            console.log(projectRS);
            if (result.success) {
                
                const project = result.project;
                const currentDate = new Date();
                const deadlineDate = new Date(project.deadline);
                const formattedDeadline = deadlineDate.toLocaleDateString('vi-VN');

                const timeDiff = deadlineDate.getTime() - currentDate.getTime();
                const daysRemaining = Math.ceil(timeDiff / (1000 * 3600 * 24));

                if (projectName) projectName.innerHTML = project.projectname;
                if (projectId) projectId.innerHTML = project.project_id;
                if (lecturerName) lecturerName.innerHTML = project.lecturer_name || 'Chưa có GVHD';
                if (deadline) deadline.innerHTML = formattedDeadline;
                if (projectRS) projectRS.innerHTML = project.result;
                if (remainDay) renderDeadlineInfo(remainDay, daysRemaining);

                if (reEvaStatus) renderReEvaluationStatus(reEvaStatus, project.re_evaluation);

                renderProjectButtons(project, submitBtn, reEvaStatus, projectStatus, project_id, group_id,
                    currentDate, deadlineDate);

            } else {
                handleError(result.message, projectName, remainDay);
            }
        } catch (err) {
            handleFetchError(err);
        }
    }

    function renderReEvaluationStatus(reEvaStatusElement, reEvaluationValue) {
        if (!reEvaStatusElement) return;

        const statusMap = {
            'none': 'Chưa gửi yêu cầu',
            'pending':'<i class="bi bi-arrow-repeat"</i>Đã gửi yêu cầu',
            'in_progress': '<i class="bi bi-arrow-repeat"></i> Đang phúc khảo',
            'completed': '<i class="bi bi-check-circle"></i> Đã phúc khảo xong'
        };

        if (reEvaluationValue && reEvaluationValue !== "none" && statusMap[reEvaluationValue]) {
            reEvaStatusElement.innerHTML =
                `<span class="status-${reEvaluationValue}">${statusMap[reEvaluationValue]}</span>`;
        } else {
            reEvaStatusElement.innerHTML = '<span class="text-secondary">Chưa gửi yêu cầu</span>';
        }
    }

    function renderDeadlineInfo(remainDayElement, daysRemaining) {
        if (!remainDayElement) return;

        if (daysRemaining < 0) {
            remainDayElement.innerHTML =
                `<span class="text-danger"><i class="bi bi-alarm"></i> Đã quá hạn ${Math.abs(daysRemaining)} ngày</span>`;
        } else if (daysRemaining === 0) {
            remainDayElement.innerHTML =
                '<span class="text-warning"><i class="bi bi-alarm-fill"></i> Hôm nay là hạn chót</span>';
        } else if (daysRemaining <= 3) {
            remainDayElement.innerHTML =
                `<span class="text-warning"><i class="bi bi-hourglass-split"></i> Còn ${daysRemaining} ngày</span>`;
        } else {
            remainDayElement.innerHTML =
                `<span class="text-success"><i class="bi bi-calendar-date"></i> Còn ${daysRemaining} ngày</span>`;
        }
    }

    function renderProjectButtons(project, submitBtn, reEvaStatus, projectStatus, project_id, group_id, currentDate,
        deadlineDate) {
        const cancelBtn = document.getElementById('cancelBtn');
        const requestRevalution = document.getElementById('new_PK');

        if (submitBtn) submitBtn.onclick = null;
        if (cancelBtn) cancelBtn.onclick = null;
        if (requestRevalution) requestRevalution.onclick = null;

        switch (project.status) {
            case "submitted":
                handleSubmittedState(project, submitBtn, cancelBtn, requestRevalution, reEvaStatus, projectStatus,
                    project_id, group_id, currentDate, deadlineDate);
                break;

            case "approved":
                handleApprovedState(project, submitBtn, cancelBtn, requestRevalution, projectStatus, project_id,
                    group_id);
                break;

            case "rejected":
                handleRejectedState(project, submitBtn, cancelBtn, requestRevalution, projectStatus, project_id,
                    group_id);
                break;

            default:
                handleDefaultState(submitBtn, cancelBtn, requestRevalution, projectStatus, project_id, group_id);
        }
    }

    function handleSubmittedState(project, submitBtn, cancelBtn, requestRevalution, reEvaStatus, projectStatus,
        project_id, group_id, currentDate, deadlineDate) {
        if (submitBtn) submitBtn.style.display = 'none';
        if (projectStatus) projectStatus.innerHTML = `<i class="bi bi-check-circle"></i> Đã nộp`;

        if (requestRevalution) {
            if (project.re_evaluation && project.re_evaluation !== "none") {
                requestRevalution.style.display = "none";
            } else {
                requestRevalution.style.display = "none";
            }
        }

        if (cancelBtn) {
            if (currentDate > deadlineDate) {
                cancelBtn.style.display = 'none';
            } else {
                cancelBtn.style.display = 'block';
                cancelBtn.onclick = createCancelHandler(project_id, group_id);
            }
        }
    }

    function handleApprovedState(project, submitBtn, cancelBtn, requestRevalution, projectStatus, project_id, group_id) {
        if (projectStatus) projectStatus.innerHTML = '<i class="bi bi-check-circle"></i> Đã chấm điểm';
        if (submitBtn) submitBtn.style.display = 'none';
        if (cancelBtn) cancelBtn.style.display = 'none';

        if (requestRevalution) {
            if (!project.re_evaluation || project.re_evaluation === "none") {
                requestRevalution.style.display = "block";
                
                requestRevalution.addEventListener('submit', async (e) => {
                    await createReEvaluationHandler(e, project_id, group_id);
                });
                
            } else {
                requestRevalution.style.display = "none";
                requestRevalution.onclick = null;
            }
        }
    }

    function handleRejectedState(project, submitBtn, cancelBtn, requestRevalution, projectStatus, project_id,
    group_id) {
        if (projectStatus) projectStatus.innerHTML = '<i class="bi bi-x-octagon"></i> Đã từ chối';
        if (submitBtn) submitBtn.style.display = 'none';
        if (cancelBtn) cancelBtn.style.display = 'none';

        if (requestRevalution) {
            if (!project.re_evaluation || project.re_evaluation === "none") {
                requestRevalution.style.display = "block";
                requestRevalution.addEventListener('submit', async (e) => {
                    await createReEvaluationHandler(e, project_id, group_id);
                });
            } else {
                requestRevalution.style.display = "none";
            }
        }
    }

    function handleDefaultState(submitBtn, cancelBtn, requestRevalution, projectStatus, project_id, group_id) {
        if (projectStatus) projectStatus.innerHTML = "🟡 Chưa nộp";

        if (submitBtn) {
            submitBtn.onclick = async () => {
                await submitProject(project_id, group_id);
                window.location.reload();
            };
            submitBtn.style.display = 'block';
        }

        if (requestRevalution) requestRevalution.style.display = "none";
        if (cancelBtn) cancelBtn.style.display = 'none';
    }

    async function createReEvaluationHandler(e, project_id, group_id) {
  
        const isConfirmed = confirm("Bạn có chắc muốn gửi yêu cầu phúc khảo không?");
        
        if (!isConfirmed) {
            return;
        }

        e.preventDefault(); 
        
        try {
            const formData = new FormData(e.target);
            const response = await fetch(`../controller/deadlineAction.php?action=reEvalutionBtn&project_id=${project_id}&group_id=${group_id}`, {
                method: 'POST',
                body: formData
            });
            const result = await response.json();
            
            if (result.success) {
                alert("✅ " + result.message);
                const modal = bootstrap.Modal.getInstance(document.getElementById('rqEvalution'));
                if (modal) modal.hide();
                window.location.reload();
            } else {
                alert("❌ " + result.message);
            }
        } catch (err) {
            console.log('Lỗi', err);
            alert("❌ Có lỗi xảy ra khi gửi yêu cầu");
        }
    }

    function createCancelHandler(project_id, group_id) {
        return async () => {
            await cancelSubmitPJ(project_id, group_id);
            window.location.reload();
        };
    }

    function handleError(message, projectName, remainDay) {
        console.error('Lỗi từ server:', message);
        if (projectName) projectName.innerHTML = 'Lỗi tải dữ liệu';
        if (remainDay) remainDay.innerHTML =
            '<span class="text-danger"><i class="bi bi-x-octagon"></i> Không thể tải thông tin</span>';
    }

    function handleFetchError(err) {
        console.error('Lỗi renderProjectInfor:', err);
        const projectName = document.getElementById('projectName');
        const remainDay = document.getElementById('remainDay');
        if (projectName) projectName.innerHTML = 'Lỗi kết nối';
        if (remainDay) remainDay.innerHTML =
            '<span class="text-danger"><i class="bi bi-x-octagon"></i> Lỗi kết nối server</span>';
    }

    async function renderGroupmembers(project_id, group_id) {
        try {
            const response = await fetch(
                `../controller/deadlineAction.php?action=getGroupMembers&project_id=${project_id}&group_id=${group_id}`
                );
            const result = await response.json();

            const tableBody = document.getElementById('tableBody');

            if (!tableBody) {
                console.error('Không tìm thấy element với id tableBody');
                return;
            }

            tableBody.innerHTML = '';

            if (result.success) {
                result.members.forEach(member => {
                    const row = document.createElement('tr');

                    let roleBadge = '';
                    if (member.role_in_group === 'leader') {
                        roleBadge = '<span class="badge bg-primary">Trưởng nhóm</span>';
                    } else {
                        roleBadge = '<span class="badge bg-secondary">Thành viên</span>';
                    }

                    row.innerHTML = `
                            <td class="px-4 py-3">${member.MSSV}</td>
                            <td class="px-4 py-3">${member.fullname}</td>
                            <td class="px-4 py-3">${roleBadge}</td>
                        `;
                    tableBody.appendChild(row);
                });

                console.log(`Đã tải ${result.members.length} thành viên`);

            } else {
                console.error('Lỗi từ server:', result.message);
                tableBody.innerHTML = `
                        <tr>
                            <td colspan="3" class="text-center text-muted py-4">
                                <i class="bi bi-x-octagon"></i> ${result.message}
                            </td>
                        </tr>
                    `;
            }
        } catch (err) {
            console.error('Lỗi renderGroupmembers:', err);
            const tableBody = document.getElementById('tableBody');
            if (tableBody) {
                tableBody.innerHTML = `
                        <tr>
                            <td colspan="3" class="text-center text-danger py-4">
                                <i class="bi bi-x-octagon"></i> Lỗi kết nối: ${err.message}
                            </td>
                        </tr>
                    `;
            }
        }
    }

    async function renderCompletedtasks(project_id, group_id) {
        try {
            const response = await fetch(
                `../controller/deadlineAction.php?action=getCompletedTasks&project_id=${project_id}&group_id=${group_id}`
                );
            const result = await response.json();

            const tasksBody = document.getElementById('tasksBody');

            if (!tasksBody) {
                console.error('Không tìm thấy element với id tasksBody');
                return;
            }

            tasksBody.innerHTML = '';

            if (result.success) {
                if (result.tasks && result.tasks.length > 0) {
                    result.tasks.forEach(task => {
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
                        tasksBody.appendChild(taskElement);
                    });
                } else {
                    tasksBody.innerHTML = '<p class="text-muted">Không có công việc đã hoàn thành</p>';
                }
            } else {
                console.log(result.message);
                tasksBody.innerHTML = `<p class="text-danger">${result.message}</p>`;
            }
        } catch (err) {
            console.log('Lỗi', err);
            const tasksBody = document.getElementById('tasksBody');
            if (tasksBody) {
                tasksBody.innerHTML = '<p class="text-danger">Lỗi khi tải dữ liệu</p>';
            }
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

    async function submitProject(project_id, group_id) {
        try {
            const response = await fetch(
                `../controller/deadlineAction.php?action=submitProject&project_id=${project_id}&group_id=${group_id}`
                );
            const result = await response.json();
            if (result.success) {
                alert(result.message);
            } else {
                console.log(result.message);
            }
        } catch (err) {
            console.log('Lỗi', err);
        }
    }

    async function cancelSubmitPJ(project_id, group_id) {
        try {
            const response = await fetch(
                `../controller/deadlineAction.php?action=cancelSubmitPJ&project_id=${project_id}&group_id=${group_id}`
                );
            const result = await response.json();
            if (result.success) {
                alert(result.message);
            } else {
                console.log(result.message);
            }
        } catch (err) {
            console.log('Lỗi', err);
        }
    }

    async function requestReEvalution(project_id, group_id) {
        try {
            const response = await fetch(
                `../controller/deadlineAction.php?action=reEvalutionBtn&project_id=${project_id}&group_id=${group_id}`
                );
            const result = await response.json();
            if (result.success) {
                alert("Đã gửi yêu cầu phúc khảo");
            } else {
                alert(result.message);
            }
        } catch (err) {
            console.log("Lỗi", err);
        }
    }
    </script>

</body>

</html>