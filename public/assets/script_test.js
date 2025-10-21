function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            sidebar.classList.toggle('show');
            overlay.classList.toggle('show');
    }

        // Active link highlighting
    const navLinks = document.querySelectorAll('.nav-link');
        navLinks.forEach(link => {
            link.addEventListener('click', function() {
                navLinks.forEach(l => l.classList.remove('active'));
                this.classList.add('active');
            });
    });

        // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('sidebar');
            const navbarToggler = document.querySelector('.navbar-toggler');
            const isClickInsideSidebar = sidebar.contains(event.target);
            const isClickOnToggler = navbarToggler.contains(event.target);

            if (!isClickInsideSidebar && !isClickOnToggler && sidebar.classList.contains('show')) {
                toggleSidebar();
            }
    });

        // Dropdown menu
    document.addEventListener("DOMContentLoaded", function() {
    const userAvatar = document.querySelector('.user_avatar');
    const dropdown = document.querySelector('.user-dropdown-menu');
    
    userAvatar.addEventListener('click', function(event) {
        event.stopPropagation();
        dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
    });
    
    document.addEventListener('click', function() {
        dropdown.style.display = 'none';
    });
    
    dropdown.addEventListener('click', function(event) {
        event.stopPropagation();
    });
    });

    
// check web resolution and apply mobile styles
function addResolutionClass() {
    const width = window.innerWidth;
    
    const profile = document.querySelector('.profile');
    const profileImg = document.querySelector('.profile_img');
    const profileBodyCustom = document.querySelector('.profile_body_custom');
    const profileInfor = document.querySelectorAll('.profile_Infor');
    const profileDetail = document.querySelector('.profile_detail');
    //groupDetail.php
    const groupDetailB = document.querySelector('.group_detail_box_2nd');
    const groupDetailTB = document.querySelector('.table_custom')

    if (width <= 768) { // mobile
        profile?.classList.add('profile_mobile');
        profileImg?.classList.add('profile_img_mobile');
        profileBodyCustom?.classList.add('profile_body_custom_mobile');
        groupDetailB?.classList.add('group_detail_box_2nd_mobile');
        groupDetailTB?.classList.add('table_custom_mobile');
        
        profileInfor.forEach(element => {
            element.classList.add('profile_Infor_mobile');
        });
        profileDetail?.classList.add('profile_detail_mobile');

    } else {
        profile?.classList.remove('profile_mobile');
        profileImg?.classList.remove('profile_img_mobile');
        profileBodyCustom?.classList.remove('profile_body_custom_mobile');
        profileInfor.forEach(element => {
            element?.classList.remove('profile_Infor_mobile')
        });
        profileDetail?.classList.remove('profile_detail_mobile');
        groupDetailB?.classList.remove('group_detail_box_2nd_mobile');
        groupDetailTB?.classList.remove('table_custom_mobile');
    }
}

// Thêm event listeners
document.addEventListener('DOMContentLoaded', addResolutionClass);
window.addEventListener('load', addResolutionClass);
window.addEventListener('resize', addResolutionClass);


function taskCal(){
    const uncompletedTasks = document.querySelectorAll('.task_content').length;
    const completedTasks = document.querySelectorAll('.task_content_completed').length;
    const submittedTasks = document.querySelectorAll('.task_content_submitted').length;
    
    // Chart JS - Sử dụng dữ liệu thực tế từ task calculation
    let options = {
        series: [completedTasks, uncompletedTasks, submittedTasks],
        chart: {
            type: 'donut',
        },
        labels: ['Đã hoàn thành', 'Chưa hoàn thành', 'Đã nộp'],
        colors: ['#00E396', 'red', '#008FFB'],
        responsive: [{
            breakpoint: 480,
            options: {
                chart: {
                    width: 200
                },
                legend: {
                    position: 'bottom'
                }
            }
        }]
    };

    // Xóa chart cũ nếu tồn tại
    const chartElement = document.querySelector("#chart");
    if (window.taskChart) {
        window.taskChart.destroy();
    }
    
    // Tạo chart mới
    window.taskChart = new ApexCharts(chartElement, options);
    window.taskChart.render();
}
