<?php 
session_start();
require_once '../core/databasePDO.php';
require_once '../core/checkIfLogin.php';
require_once '../core/getUser.php';
require_once '../core/getGroupLeader.php';
require_once '../core/getGroupMember.php';


$base_url = 'http://' . $_SERVER['HTTP_HOST'] . '/qlmh';
$group_id = (int)$_GET['group_id'];
if(isset($_GET['group_id']) && !empty($_GET['group_id'])){
    
    $sql = "SELECT * FROM groups WHERE group_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$group_id]);
    $group = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$group) {
        die("Không tìm thấy nhóm!");
    }
    } else {
    die("Thiếu thông tin nhóm!");
}


// Sửa query để lấy leader của nhóm cụ thể
$query = 'SELECT u.fullname, gm.* 
          FROM groupmember gm
          INNER JOIN users u ON gm.user_id = u.id
          WHERE gm.role_in_group = "leader" 
          AND gm.group_id = ?';
$stmt = $pdo->prepare($query);
$stmt->execute([$group_id]); // Sử dụng group_id thay vì userInfo['id']
$leader = $stmt->fetch(PDO::FETCH_ASSOC);

$query1 = 'SELECT u.*, gm.role_in_group, gm.joined_at, gm.status, gm.user_id
          FROM groupmember gm
          INNER JOIN users u ON gm.user_id = u.id
          WHERE gm.group_id = ? AND gm.status != "pending"';
$stmt = $pdo->prepare($query1);
$stmt->execute([$_GET['group_id']]);
$groupmembers = $stmt->fetchAll(PDO::FETCH_ASSOC);
$total_members = count($groupmembers);

//Yêu cầu tham gia nhóm :D

$user_id = $userInfo['id']; 

// Kiểm tra xem đã là thành viên hoặc đã gửi yêu cầu chưa
$query = 'SELECT gm.joined_at,gm.user_id,u.fullname
          FROM groupmember gm
          INNER JOIN users u ON gm.user_id = u.id
          WHERE gm.group_id = ? AND gm.status = "pending"';
$stmt = $pdo->prepare($query);
$stmt->execute([$group_id]);
$request = $stmt->fetchAll(PDO::FETCH_ASSOC);

print_r($member);

?>


<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý thư viện DNC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= $base_url ?>/public/assets/style_test.css">
    <link rel="stylesheet" href="<?= $base_url ?>/public/assets/style.css">

    <script src="<?= $base_url ?>/public/assets/script_test.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
    .group_detail_container {
        position: absolute;
        left: -100px;
    }

    .group_detail_box {
        background-color: white;
        border-radius: 10px;
    }

    .group_detail_box p {
        font-size: larger;
    }

    .group_detail_box_2nd {
        background-color: white;
        border-radius: 10px;
        margin-left: 4px;
    }

    .group_detail_box_2nd_mobile {
        background-color: white;
        border-radius: 10px;
        margin-left: 0px !important;
        margin-top: 4px !important;
    }

    .table_custom {
        position: relative;
        right: 15px;
    }

    .table_custom_mobile {
        position: relative;
        right: 0px !important;
    }
    </style>
</head>

<body>
    <header>
        <nav class="navbar navbar-expand-lg bg-white border-bottom fixed-top">
            <div class="container-fluid">
                <a href="<?= $base_url ?>/public/index.php">
                    <img src="<?= $base_url ?>/img/sv_logo_dashboard.png" alt="Logo" width="200px" height="40px"
                        class="d-inline-block align-text-top brand_logo">
                </a>
                <div class="d-flex ms-2 me-2 ms-auto">
                    <?php 
                if (isset($userInfo)){
                    $avatar = !empty($userInfo['avatar']) ? $userInfo['avatar'] : $base_url . '/img/default-avatar.png';
                    echo '<img src="' . htmlspecialchars($avatar) . '" class="user_avatar rounded-circle" alt="user_avatar" width="40" height="40">';
                }
                ?>
                </div>
                <button class="navbar-toggler navbarbutton" type="button" onclick="toggleSidebar()">
                    <span class="navbar-toggler-icon"></span>
                </button>
            </div>
        </nav>
    </header>

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
                <a class="nav-link" href="../views/projects.php">
                    <i class="bi bi-journal"></i> Đề tài
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="../views/groups.php">
                    <i class="bi bi-people"></i> Nhóm & Thành viên
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="../views/task.php">
                    <i class="bi bi-list-task"></i> Công việc
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="../views/deadline.php">
                    <i class="bi bi-upload"></i> Mốc nộp bài
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="../views/re-evaluation.php">
                    <i class="bi bi-chat-dots"></i> Phúc khảo
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="../views/report.php">
                    <i class="bi bi-bar-chart"></i> Báo cáo
                </a>
            </li>
        </ul>
    </aside>

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
                <a href="<?= $base_url ?>/app/views/profileEdit.php" class="dropdown-item d-block p-3 border-bottom">
                    <i class="bi bi-person me-2"></i>Chỉnh sửa thông tin cá nhân
                </a>
                <a href="help.php" class="dropdown-item d-block p-3 border-bottom">
                    <i class="bi bi-question-circle me-2"></i>Trợ giúp
                </a>
                <a href="<?= $base_url ?>/app/controller/logout.php" class="dropdown-item d-block p-3 text-danger">
                    <i class="bi bi-box-arrow-right me-2"></i>Đăng xuất
                </a>
            </div>
        </div>
    </section>

    <main class="main-content" style="position: relative;">
        <div class="container-fluid mt-5">
            <h2><i class="bi bi-person-vcard"></i> Thông tin nhóm</h2>
            <div class="row mt-2">
                <?php 
                    if(isset($member) && $member['role_in_group'] == 'leader'){
                        echo '<div class="col-md-4 group_detail_box" style="position:relative">';
                        echo '<div class="text-center mb-3">';
                        echo '<img src="'.htmlspecialchars($group['avatar'] ?? 'default-group.jpg').'" alt="ảnh nhóm" class="group-avatar img-fluid rounded" style="max-width: 200px; height: auto;">';
                        echo '</div>'; // Đóng div text-center
                    } else {
                        echo '<div class="col-md-8 group_detail_box" style="position:relative">';
                        echo '<div class="text-center mb-3">';
                        echo '<img src="'.htmlspecialchars($group['avatar'] ?? 'default-group.jpg').'" alt="ảnh nhóm" class="group-avatar img-fluid rounded" style="max-width: 200px; height: auto;">';
                        echo '</div>'; // Đóng div text-center
                    }
                    ?>

                <?php 
                    if(isset($group_id) && !empty($group_id)){
                        echo '<h6><strong>Tên nhóm:</strong></h6>';
                        echo '<p>' . htmlspecialchars($group['groupname'] ?? 'Chưa có tên') . '</p>';

                        echo '<h6><strong>Số thành viên:</strong></h6>';
                        echo '<p>' . ($total_members ?? '0') . ' thành viên</p>';
                        echo '<h6><strong>Nhóm trưởng:</strong></h6>';
                        if(isset($leader)){
                            echo '<p>'. $leader['fullname'] .'</p>';
                        }
                        // Thêm các thông tin khác nếu cần
                        if(isset($group['description'])){
                            echo '<h6>Mô tả:</h6>';
                            echo '<p>' . htmlspecialchars($group['description']) . '</p>';
                        }

                        if(isset($group['created_at'])){
                            echo '<h6><strong>Ngày tạo nhóm:</strong></h6>';
                            echo '<p>' . date('d/m/Y', strtotime($group['created_at'])) . '</p>';
                        }
                    }
                    
                    if(isset($leader['role_in_group'])){
                        if($leader['role_in_group'] == 'leader' && $leader['user_id'] == $userInfo['id']){
                            echo '<a href="../controller/deleteGroup.php?group_id=' . $_GET['group_id'] .'" style="position: absolute;
                                    top: 290px;
                                    right: 10px;
                                    text-decoration: none;
                                    border-bottom: 2px solid;
                                    " onclick="return confirm(\'Bạn có chắc muốn xóa nhóm này? Hành động này không thể hoàn tác.\')" class="btn btn-danger">
                                    Xóa nhóm</a>';
                        } else {
                            echo '<a href="../controller/leaveGroup.php?group_id=' . $_GET['group_id'] .'&user_id='.$userInfo['id'].'" style="position: absolute;
                                    top: 250px;
                                    right: 10px;
                                    text-decoration: none;
                                    border-bottom: 2px solid;
                                    " onclick="return confirm(\'Bạn có chắc muốn thoát nhóm này? Hành động này không thể hoàn tác.\')" class="btn btn-danger">
                                    Thoát nhóm</a>';
                        }
                    }
                    ?>
            </div> <!-- Đóng div col-md-4 hoặc col-md-8 -->

            <?php 
                    // Ẩn yêu cầu tham gia nếu không phải là leader
                    if(isset($member) && $member['role_in_group'] == 'leader'){
                        echo '<div class="col-md-4 group_detail_box_2nd">';
                        echo '<h4><i class="bi bi-people-fill"></i> Yêu cầu tham gia nhóm</h4>';
                        if(isset($request) && !empty($request)){
                            echo '<table class="table table-hover">';
                            echo '<thead class="table-primary">';
                            echo '<tr>';
                            echo '<th class="text-center">Họ và tên</th>';
                            echo '<th class="text-center">Yêu cầu vào ngày</th>';
                            echo '<th class="text-center">Chấp nhận</th>';
                            echo '</tr>';
                            echo '</thead>';
                            echo '<tbody>';
                            
                            foreach($request as $req){
                                echo '<tr>';
                                echo '<td class="text-center align-middle">'.htmlspecialchars($req['fullname']).'</td>';
                                echo '<td class="text-center align-middle">'.date('d/m/Y', strtotime($req['joined_at'])).'</td>';
                                echo '<td class="text-center align-middle">';
                                echo '<a class="btn btn-success btn-sm me-1" href="../controller/accRequest.php?group_id='.$group_id.'&user_id='.$req['user_id'].'" onclick="return confirm(\'Chấp nhận yêu cầu này?\')">✓</a>';
                                echo '<a class="btn btn-danger btn-sm me-1" href="../controller/rejectRequest.php?group_id='.$group_id.'&user_id='.$req['user_id'].'" onclick="return confirm(\'Từ chối yêu cầu này?\')">✗</a>';
                                echo '</td>';
                                echo '</tr>';
                            }
                            
                            echo '</tbody>';
                            echo '</table>';
                        } else {
                            echo '<p class="text-muted text-center">Không có yêu cầu tham gia nào</p>';
                        }
                        echo '</div>'; // Đóng div col-md-4 group_detail_box_2nd
                    }
                    
                    if(isset($member) && $member['role_in_group'] == 'leader'){
                        echo '<div class="col-md-2 group_detail_box_2nd">';
                        echo '<h4 style="text-align:center;">Đề tài</h4>';
                        echo '<p class="text-muted text-center">Hiện đang không đăng ký đề tài nào</p>';
                        echo '<a href="groups.php" class="btn btn-primary">Click vào đây để đi đăng ký đề tài</a>';
                        echo '</div>'; 
                    } else {
                        echo '<div class="col-md-3 group_detail_box_2nd">';
                        echo '<h4 style="text-align:center;">Đề tài</h4>';
                        echo '<p class="text-muted text-center">Hiện đang không đăng ký đề tài nào</p>';
                        echo '<a href="groups.php" class="btn btn-primary">Click vào đây để đi đăng ký đề tài</a>';
                        echo '</div>'; 
                    }
                    ?>
        </div> <!-- Đóng div row -->

        <h2>Thành viên</h2>
        <div class="card table_custom">
            <div class="card-body">
                <table class="table table-striped table-bordered">
                    <thead class="thead-dark">
                        <tr>
                            <th scope="col" style="font-size: large; color: #dc3545;">MSSV</th>
                            <th scope="col" style="font-size: large; color: #dc3545;">Họ và tên</th>
                            <th scope="col" style="font-size: large; color: #dc3545;">Chức vụ</th>
                            <th scope="col" style="font-size: large; color: #dc3545;">Tham gia nhóm ngày</th>
                            <?php if(isset($member) && $member['role_in_group'] == 'leader'){echo '<th scope="col" style="font-size: large; color: #dc3545;">Thao tác</th>';}?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                            foreach($groupmembers as $groupmember){
                                echo '<tr>';
                                    echo '<td>'.$groupmember['studentID'].'</td>';
                                    echo '<td>'.$groupmember['fullname'].'</td>';
                                    switch ($groupmember['role_in_group']) {
                                        case 'leader':
                                            echo '<td><span class="label label-info">Trưởng nhóm</span></td>';
                                            break;
                                        case 'member':
                                            echo '<td><span class="label label-info">Thành viên</span></td>';
                                            break;
                                        case 'assistant':
                                            echo '<td><span class="label label-info">Thư ký</span></td>';
                                            break;
                                    }
                                    echo '<td>'.date('d/m/Y H:i:s', strtotime($groupmember['joined_at'])).'</td>';
                                    if(isset($member) && $member['role_in_group'] == 'leader'){
                                        if($groupmember['role_in_group'] != 'leader'){
                                            echo '<td>';
                                            echo '<button type="button" class="btn btn-outline-danger btn-sm" 
                                                onclick="if(confirm(\'Bạn có chắc muốn xóa thành viên này? Hành động này không thể hoàn tác.\')) { 
                                                    window.location.href=\'../controller/removemember.php?group_id='.$_GET['group_id'].'&user_id='.$groupmember['user_id'].'\'
                                                }">
                                                <i class="fas fa-trash-alt"></i> Xóa
                                            </button>';
                                            echo '</td>';
                                        } else {
                                            echo '<td><span class="text-muted">:D</span></td>';
                                        }
                                    }
                                echo '</tr>';
                            }   
                            ?>
                    </tbody>
                </table>
            </div>
        </div>
        </div>
    </main>
    <script>
    function approveRequest(userId) {
        if (confirm("Bạn có chắc muốn chấp nhận yêu cầu tham gia này?")) {
            // Gửi request approve
            console.log("Approve user: " + userId);
        }
    }

    function rejectRequest(userId) {
        if (confirm("Bạn có chắc muốn từ chối yêu cầu tham gia này?")) {
            // Gửi request reject
            console.log("Reject user: " + userId);
        }
    }
    </script>
</body>

</html>