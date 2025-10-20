<?php 
session_start();
require_once '../core/databasePDO.php';
require_once '../core/checkIfLogin.php';
require_once '../core/getUser.php';

$group_id = $userInfo['group_id'] ?? null;
$project_id = $userInfo['project_id'] ?? null;

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
    <link href="http://yoursite.com/themify-icons.css" rel="stylesheet">

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
                <a class="nav-link active" href="task.php">
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
        <div class="row mt-5">
            <div class="col-md-7">
                <div class="task_header d-flex justify-content-between align-items-center mb-1">
                    <h2 class="ms-2">Tasks</h2>
                    <ul class="list-group list-group-horizontal">
                        <li style="list-style: none;"><button class="new_task me-2" data-bs-toggle="modal"
                                data-bs-target="#addNewTask" onclick="">New task</button></li>
                        <li style="list-style: none;">
                            <div class="dropdown">
                                <button class="new_task dropdown-toggle" type="button" id="dropdownMenuButton1"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                    Lọc theo
                                </button>
                                <ul class="dropdown-menu p-0" aria-labelledby="dropdownMenuButton1">
                                    <li class="w-100"><button class="btn btn-warning w-100 rounded-0 text-center"
                                            onclick="handleClick(this)" value="all">Tất cả</button></li>
                                    <li class="w-100"><button class="btn btn-danger w-100 rounded-0 text-center"
                                            onclick="handleClick(this)" value="pending">Chưa nộp</button></li>
                                    <li class="w-100"><button class="btn btn-primary w-100 rounded-0 text-center"
                                            onclick="handleClick(this)" value="submitted">Đã nộp</button></li>
                                    <li class="w-100"><button class="btn btn-success w-100 rounded-0 text-center"
                                            onclick="handleClick(this)" value="completed">Đã duyệt</button></li>
                                </ul>
                            </div>
                        </li>
                    </ul>
                </div>
                <div class="task_list mt-3">
                </div>
            </div>

            <div class="col-md-5">
                <div class="progress_header">
                    <h2 class="ms-2">Tiến độ làm việc</h2>
                </div>
                <div class="mt-2" style="background-color: white;
                            padding: 20px; 
                            border-radius: 8px; 
                            box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                    <div id="chart"></div>
                </div>
                <div class="projects_log bg-white mt-2 border-bottom border-2">
                    <h4>Task logs</h2>
                </div>
                <div class="">

                </div>
            </div>
        </div>

        <!-- modal add new task -->
        <div class="modal fade" id="addNewTask" tabindex="-1" aria-labelledby="addNewTask" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Thông tin đề tài</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form class="new_task_form">
                            <div class="mb-3">
                                <label for="tasktitle" class="form-label">Tiêu đề công việc</label>
                                <input type="text" class="form-control" id="tasktitle" name="tasktitle" required>
                            </div>
                            <div class="mb-3">
                                <label for="description" class="form-label">Mô tả công việc</label>
                                <textarea class="form-control" id="description" name="description" rows="4"
                                    required></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="deadline" class="form-label">Hạn hoàn thành</label>
                                <input type="date" class="form-control" id="deadline" name="deadline" required>
                            </div>
                            <button type="submit" class="btn btn-primary new_task">Tạo công việc</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- modal task detail -->
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
                        <?php
                        if($userInfo['role_in_group'] === 'leader'){
                            echo '<button id="delTaskButton" class="btn btn-danger " onclick="" style="padding-top:10px;padding: 10px;margin-top: 10px;">Xóa công việc</button>';
                            echo '<button id="submitTask" class="btn btn-success" onclick="" style="padding-top:10px;padding: 10px;margin-top: 10px;position: relative;margin-left: 2px;">Đã hoàn thành</button>';
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
        <!-- modal upload file -->
        <div class="modal fade" id="uploadFile" tabindex="-1" aria-labelledby="uploadFile" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Upload file</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="uploadForm" enctype="multipart/form-data" method="POST">
                            <input type="hidden" id="hiddenTaskId" name="task_id">
                            <div class="mb-3">
                                <label for="file" class="form-label">Chọn file</label>
                                <input type="file" class="form-control" id="file" name="file" required>
                                <div class="form-text">Chấp nhận: PDF, DOC, DOCX, JPG, PNG (Tối đa 30MB)</div>
                            </div>
                            <div class="mb-3">
                                <label for="description" class="form-label">Mô tả (tuỳ chọn)</label>
                                <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Upload File</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </main>

    <script>

    async function submitTaskF(task_id,project_id,group_id){
        try{
            const response = await fetch(`../controller/taskAction.php?task_id=${task_id}&group_id=${group_id}&project_id=${project_id}&action=submitTask`)
            const result = await response.json();
            if(result.success){
                alert(result.message);
                fetchTask();
            }else{
                alert(result.message)
            }
        }catch(err){
            console.log('Lỗi', err);
        }
    }

    function getTaskId(task_id) {
        document.getElementById('hiddenTaskId').value = task_id;
    }

    document.getElementById('uploadForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        

        const task_id = document.getElementById('hiddenTaskId').value;
        const group_id = <?php echo $userInfo['group_id'] ?? 'null'; ?>;
        const project_id = <?php echo $userInfo['project_id'] ?? 'null'; ?>;
        
        if (!task_id) {
            alert('Vui lòng chọn task trước khi upload!');
            return;
        }
        
        const formData = new FormData(this);
        
        try {
            const response = await fetch(`../controller/taskAction.php?task_id=${task_id}&project_id=${project_id}&group_id=${group_id}&action=uploadFile`, {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                alert(result.message);
                fetchTasks(); 
                this.reset();

                const modal = bootstrap.Modal.getInstance(document.getElementById('uploadFile'));
                modal.hide();
            } else {
                alert('Lỗi: ' + result.message);
            }
        } catch (err) {
            console.log('Lỗi khi upload files: ', err);
            alert('Có lỗi xảy ra khi upload file');
        }
    });

    async function handleClick(button) {
        const value = button.value;
        try {
            await fetchTasks(value);
        } catch (err) {
            console.log('Lỗi khi lọc công việc:', err);
        }
    }

    async function fetchTasks(statusFilter = null) {
        try {
            if (!<?php echo isset($userInfo['group_id']) && isset($userInfo['project_id']) ? 'true' : 'false' ?>) {
                const tasksContainer = document.querySelector('.task_list');
                tasksContainer.innerHTML = '<p class="text-muted">Bạn cần tham gia nhóm và dự án trước.</p>';
                return;
            }

            const group_id = <?php echo $userInfo['group_id'] ?? 'null' ?>;
            const project_id = <?php echo $userInfo['project_id'] ?? 'null' ?>;

            const response = await fetch(
                `../controller/taskAction.php?group_id=${group_id}&action=getTasks&project_id=${project_id}`);
            const result = await response.json();
            if (result.success) {
                renderTasks(result.tasks, statusFilter);
                taskCal();
            } else {
                console.error('Lỗi:', result.message);
            }
        } catch (err) {
            console.error('Lỗi khi lấy công việc:', err);
        }
    }

    function renderTasks(tasks, statusFilter) {
        const tasksContainer = document.querySelector('.task_list');
        tasksContainer.innerHTML = '';

        if (!tasks || tasks.length === 0) {
            tasksContainer.innerHTML = '<p class="text-muted">Không có công việc nào.</p>';
            return;
        }

        let filteredTasks = tasks;
        if (statusFilter && statusFilter !== 'all') {
            filteredTasks = tasks.filter(task => task.status === statusFilter);
        }

        if (filteredTasks.length === 0) {
            tasksContainer.innerHTML = '<p class="text-muted">Không có công việc nào ở trạng thái này.</p>';
            return;
        }

        filteredTasks.forEach(task => {
            const taskElement = document.createElement('div');
            if (task.status === 'completed') {
                taskElement.className = 'task_body mt-3';
                taskElement.innerHTML = `
                    <div class="task_content_completed pl-10px">
                        <button class="edit_icon" 
                            title="Upload file" 
                            data-bs-toggle="modal" 
                            data-bs-target="#uploadFile" 
                            data-task-id="${task.task_id}" 
                            value="${task.task_id}"
                            onclick="getTaskId(${task.task_id})">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <div class="task_information">
                            <h4 class="task_title">${task.tasktitle}</h4>
                            <p class="task_description">${task.description}</p>
                            <div class="d-flex justify-content-between">
                                <button class="task_detailbutton" data-bs-toggle="modal" data-bs-target="#taskDetail" onclick="getTask(${task.task_id}, ${task.project_id}, ${task.group_id})">Xem chi tiết</button>
                                <div class="task_status_completed">${task.status}</div>
                            </div>
                        </div>
                    </div>
                `;
            } else if (task.status === 'pending') {
                taskElement.className = 'task_body mt-3';
                taskElement.innerHTML = `
                    <div class="task_content pl-10px">
                        <button class="edit_icon" 
                            title="Upload file" 
                            data-bs-toggle="modal" 
                            data-bs-target="#uploadFile" 
                            data-task-id="${task.task_id}" 
                            value="${task.task_id}"
                            onclick="getTaskId(${task.task_id})">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <div class="task_information">
                            <h4 class="task_title">${task.tasktitle}</h4>
                            <p class="task_description">${task.description}</p>
                            <div class="d-flex justify-content-between">
                                <button class="task_detailbutton" data-bs-toggle="modal" data-bs-target="#taskDetail" onclick="getTask(${task.task_id}, ${task.project_id}, ${task.group_id})">Xem chi tiết</button>
                                <div class="task_status">${task.status}</div>
                            </div>
                        </div>
                    </div>
                `;
            } else {
                taskElement.className = 'task_body mt-3';
                taskElement.innerHTML = `
                    <div class="task_content_submitted pl-10px">
                        <button class="edit_icon" 
                            title="Upload file" 
                            data-bs-toggle="modal" 
                            data-bs-target="#uploadFile" 
                            data-task-id="${task.task_id}" 
                            value="${task.task_id}"
                            onclick="getTaskId(${task.task_id})">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <div class="task_information">
                            <h4 class="task_title">${task.tasktitle}</h4>
                            <p class="task_description">${task.description}</p>
                            <div class="d-flex justify-content-between">
                                <button class="task_detailbutton" data-bs-toggle="modal" data-bs-target="#taskDetail" onclick="getTask(${task.task_id}, ${task.project_id}, ${task.group_id})">Xem chi tiết</button>
                                <div class="task_status_submitted">${task.status}</div>
                            </div>
                        </div>
                    </div>
                `;
            }
            tasksContainer.appendChild(taskElement);
        });
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

                document.getElementById('taskTitle').innerText = result.task.tasktitle;
                document.getElementById('Description').innerText = result.task.description;
                document.getElementById('Creator').innerText = result.task.creator_name;
                document.getElementById('Created_at').innerText = created;
                document.getElementById('Deadline').innerText = deadline;
                if(result.task.role_in_group === 'leader'){
                    document.getElementById('submitTask').addEventListener('click', async (e) =>{
                        e.preventDefault();
                        e.stopPropagation();
                        await submitTaskF(result.task.task_id,result.task.project_id,result.task.group_id);
                    })
                    document.getElementById('delTaskButton').addEventListener('click', async (e) => {
                        e.preventDefault();
                        e.stopPropagation();
                        await deleteTask(result.task.task_id, result.task.project_id, result.task.group_id);
                    });
                }
            
                if(result.task.files.filepath){
                    
                    const fileLink = document.createElement('a');
                    fileLink.href = result.task.files.filepath;
                    fileLink.innerText = 'Tải tệp';
                    fileLink.target = '_blank';
                    document.getElementById('filePath').innerHTML = '';
                    document.getElementById('filePath').appendChild(fileLink);
                }else{
                    document.getElementById('filePath').innerText = 'Chưa có tệp nào được tải lên.';
                }
                document.getElementById('fileUploader').innerText = result.task.files.uploader_name || 'Chưa có người tải lên';
                document.getElementById('fileType').innerText = result.task.files.filetype || 'Chưa có loại tệp';

            } else {
                throw new Error(result.message || 'Không thể lấy thông tin task');
            }
        } catch (err) {
            console.error('Lỗi:', err);
            alert('Lỗi khi lấy chi tiết công việc: ' + err.message);
        }
    }

    const newTaskButton = document.querySelector('.new_task_form');
    if (newTaskButton) {
        newTaskButton.addEventListener('submit', async (e) => {
            e.preventDefault();
            const group_id = <?php echo $userInfo['group_id'] ?? 'null' ?>;
            const project_id = <?php echo $userInfo['project_id'] ?? 'null' ?>;

            if (!group_id || !project_id) {
                alert('Bạn cần tham gia nhóm và dự án trước khi tạo công việc');
                return;
            }

            await newTask(e, group_id, project_id);
        });
    }

    async function newTask(e, group_id, project_id) {
        e.preventDefault();
        const formData = new FormData(e.target);
        try {
            const response = await fetch(
                `../controller/taskAction.php?group_id=${group_id}&&action=newTask&&project_id=${project_id}`, {
                    method: 'POST',
                    body: formData
                });
            const result = await response.json();
            if (result.success) {
                alert(result.message);
                fetchTasks();
                e.target.reset();
            } else {
                alert(result.message);
            }
        } catch (err) {
            alert('Lỗi khi tạo công việc mới: ' + err.message);
        }
    }

    async function deleteTask(task_id, project_id, group_id) {
        try {
            const response = await fetch(
                `../controller/taskAction.php?task_id=${task_id}&&group_id=${group_id}&&action=delTask&&project_id=${project_id}`, {
                    method: 'DELETE'
                });
            const result = await response.json();
            if (result.success) {
                alert(result.message);
                fetchTasks();
                taskCal();
            } else {
                alert(result.message);
            }
        } catch (err) {
            alert('Lỗi khi xóa công việc: ' + err.message);
        }
    }
    </script>
</body>

</html>