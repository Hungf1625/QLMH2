<?php 
session_start();
require_once '../core/databasePDO.php';
require_once '../core/checkIfLogin.php';
require_once '../core/getUser.php';

//querry chỉ để truy vấn tất cả group đang có trong db không điều kiện
$group = "SELECT * from groups";
$stmt = $pdo->prepare($group);
$stmt->execute(); 
$groups = $stmt->fetchAll(PDO::FETCH_ASSOC);

$querry = "SELECT * from groupmember
           WHERE user_id = ?";
$stmt = $pdo->prepare($querry);
$stmt->execute([$_SESSION['user_id']]); 
$currentUser = $stmt->fetch(PDO::FETCH_ASSOC);
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
        <!-- dropdown menu for user avatar -->
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
                <a class="nav-link active" href="groups.php">
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
        <div class="">
            <div class="container-fluid">
                <div class="profile mt-5">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th colspan="5" style="position:relative; border: none;">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h1 class="mb-0" style="flex: 1; text-align: center;">Danh sách nhóm</h1>
                                        <button class="btn btn-primary addbutton" data-bs-toggle="modal" data-bs-target="#addGroupModal">
                                            <i class="bi bi-plus-circle"></i> Thêm nhóm
                                        </button>
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
                                <th>Tên nhóm</th>
                                <th>Tên đề tài</th>
                                <th width="150" class="text-center">Chi tiết</th>
                                <th width="180" class="text-center">Yêu cầu tham gia</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            foreach ($groups as $index => $group) {
                                echo '<tr>';
                                echo '<td class="text-center align-middle">' . ($index + 1) . '</td>';
                                echo '<td class="align-middle fw-bold">' . htmlspecialchars($group['groupname']) . '</td>';
                                echo '<td class="align-middle">' . (isset($group['topic']) && !empty($group['topic']) ? htmlspecialchars($group['topic']) : '<span class="text-muted">Chưa có đề tài</span>') . '</td>';
                                if(isset($currentUser) && !empty($currentUser)){
                                    if(($currentUser['group_id'] == $group['group_id']) && !($currentUser['status'] == 'pending')){
                                    echo '<td class="text-center align-middle">';
                                        echo '<a href="groupDetail.php?group_id=' . $group['group_id'] . '" class="btn btn-outline-primary btn-sm">Xem chi tiết</a>';
                                    echo '</td>';
                                    }else if(($currentUser['status'] == 'pending') && ($group['group_id'] == $currentUser['group_id'])){
                                    echo '<td class="text-center align-middle">';
                                        echo '<a href="../controller/leaveGroup.php?group_id='.$group['group_id'].'" class="btn btn-outline-success btn-sm" disabled>Hủy yêu cầu</a>';
                                    }
                                    else{
                                        echo'<td class="text-center align-middle">Bạn đã có nhóm hoặc đã gửi yêu cầu</td>';
                                    }
                                }
                                else{
                                    echo'<td class="text-center align-middle">Không phải là thành viên nhóm </td>';
                                }
                                if(isset($currentUser) && isset($currentUser['group_id'])){
                                    echo'<td class="text-center align-middle">Bạn đã có nhóm hoặc đã gửi yêu cầu</td>';
                                }else{
                                    echo '<td class="text-center align-middle">';
                                    echo '<a href="../controller/joinRequest.php?group_id='.$group['group_id'].'" class="btn btn-outline-success btn-sm" disabled>Gửi yêu cầu</a>';
                                    echo '</td>';
                                }
                                echo '</tr>';
                            }
                            
                            if(empty($groups)) {
                                echo '<tr>';
                                echo '<td colspan="5" class="text-center py-5">';
                                echo '<div class="text-muted">';
                                echo '<i class="bi bi-people fs-1"></i>';
                                echo '<p class="mt-3 mb-2 fs-5">Chưa có nhóm nào</p>';
                                echo '<small>Hãy tạo nhóm đầu tiên để bắt đầu</small>';
                                echo '</div>';
                                echo '</td>';
                                echo '</tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>


        <!-- Modal -->
        <div class="modal fade" id="addGroupModal" tabindex="-1" aria-labelledby="addGroupModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addGroupModalLabel">Thêm nhóm Mới</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="../model/newGroup.php" method="POST">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="groupname" class="form-label">Tên nhóm</label>
                                <input type="text" class="form-control" id="groupname" name="groupname" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                            <button type="submit" class="btn btn-primary">Tạo nhóm</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </main>


</body>

</html>