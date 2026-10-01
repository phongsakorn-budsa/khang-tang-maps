<?php 
require_once("../config/connect.php");
require_once("../layout/header.php");

// ดึงข้อมูลภาพรวม
$stats = $controller->get_dashboard_stats();
?>

<style>
/* =========================================
   Sidebar Styles (เมนูด้านซ้าย)
========================================= */
.sidebar-wrapper {
    min-height: calc(100vh - 60px); /* ความสูงเต็มจอหักลบ Navbar ด้านบน */
    background-color: #ffffff;
    border-right: 1px solid #eaeaea;
    box-shadow: 2px 0 10px rgba(0,0,0,0.03);
}
.nav-link-custom {
    color: #555;
    font-weight: 500;
    border-radius: 10px;
    padding: 10px 15px;
    margin-bottom: 5px;
    transition: all 0.3s ease;
}
.nav-link-custom:hover {
    background-color: #fff8e1; /* สีเหลืองอ่อน */
    color: #ffc107;
    transform: translateX(5px);
}
.nav-link-custom.active {
    background-color: #ffc107;
    color: #ffffff;
    box-shadow: 0 4px 10px rgba(255, 193, 7, 0.3);
}
.nav-link-custom i {
    margin-right: 10px;
    font-size: 1.1rem;
    width: 25px;
    text-align: center;
    display: inline-block;
}

/* =========================================
   Dashboard Content Styles (เนื้อหาด้านขวา)
========================================= */
.main-content {
    background-color: #f8f9fc; /* สีพื้นหลังหลักเทาอ่อนสบายตา */
    padding: 30px;
    min-height: calc(100vh - 60px);
}
.dashboard-card {
    text-decoration: none;
    color: inherit;
    display: block;
    height: 100%;
}
.dashboard-card .card {
    height: 100%;
    transition: all .3s ease;
    cursor: pointer;
    border-radius: 16px;
    border: none;
}
.dashboard-card .card:hover {
    transform: translateY(-6px);
    box-shadow: 0 10px 20px rgba(0,0,0,.08) !important;
}

/* ตกแต่งวงกลมพื้นหลังไอคอนให้สวยขึ้น */
.icon-circle {
    width: 70px;
    height: 70px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 15px auto;
}
.stat-number {
    font-size: 36px;
    font-weight: 800;
    margin: 0;
    color: #2c3e50;
    line-height: 1;
}
.stat-title {
    margin-bottom: 10px;
    font-size: 15px;
    font-weight: 600;
    color: #6c757d;
}
</style>

<div class="container-fluid p-0">
    <div class="row g-0">
        
        <div class="col-md-3 col-lg-2 sidebar-wrapper d-none d-md-flex flex-column p-3">
            <div class="text-center mb-4 mt-2">
                <h5 class="fw-bold text-dark mb-0">
                    <i class="bi bi-shield-lock-fill text-warning"></i> Admin Panel
                </h5>
            </div>
            
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a href="dashboard.php" class="nav-link nav-link-custom active">
                        <i class="bi bi-grid-1x2-fill"></i> ภาพรวมระบบ
                    </a>
                </li>
                
                <li class="nav-item mt-3">
                    <small class="text-muted fw-bold px-3 text-uppercase" style="font-size: 0.75rem;">การจัดการ (Manage)</small>
                </li>
                
                <li class="nav-item mt-1">
                    <a href="manage_users.php" class="nav-link nav-link-custom">
                        <i class="bi bi-people-fill"></i> จัดการผู้ใช้
                    </a>
                </li>
                <li class="nav-item">
                    <a href="manage_shop.php" class="nav-link nav-link-custom">
                        <i class="bi bi-shop"></i> จัดการร้านค้า
                    </a>
                </li>
                <li class="nav-item">
                    <a href="manage_product.php" class="nav-link nav-link-custom">
                        <i class="bi bi-box-seam-fill"></i> จัดการสินค้า
                    </a>
                </li>

                <li class="nav-item mt-3">
                    <small class="text-muted fw-bold px-3 text-uppercase" style="font-size: 0.75rem;">ตรวจสอบ (Monitor)</small>
                </li>
                <li class="nav-item mt-1">
                    <a href="map.php" class="nav-link nav-link-custom">
                        <i class="bi bi-map-fill"></i> แผนที่ร้านค้า
                    </a>
                </li>

                <li class="nav-item mt-1">
                    <a href="report.php" class="nav-link nav-link-custom">
                        <i class="bi bi-flag-fill"></i> รายงานปัญหา
                    </a>
                </li>
                <li class="nav-item">
                    <a href="chat.php" class="nav-link nav-link-custom">
                        <i class="bi bi-chat-dots-fill"></i> แชทกับร้านค้า
                    </a>
                </li>
            </ul>
            
            <div class="mt-auto pt-4 pb-2">
                <a href="/wayside_edit/logout.php" class="btn btn-danger text-white w-100 fw-bold shadow-sm" style="border-radius: 10px;">
                    <i class="bi bi-box-arrow-right"></i> ออกจากระบบ
                </a>
            </div>
        </div>

        <div class="col-md-9 col-lg-10 main-content">
            
            <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                <h3 class="fw-bold mb-0 text-dark">Dashboard Management</h3>
                <div class="text-muted bg-white px-3 py-1 rounded-pill shadow-sm small">
                    <i class="bi bi-clock-history"></i> อัปเดตล่าสุด: <?php echo date("d/m/Y"); ?>
                </div>
            </div>

            <div class="row g-4">

                <div class="col-md-6 col-xl-3">
                    <a href="manage_users.php" class="dashboard-card">
                        <div class="card shadow-sm text-center">
                            <div class="card-body p-4">
                                <div class="icon-circle bg-primary bg-opacity-10">
                                    <i class="bi bi-people-fill fs-2 text-primary"></i>
                                </div>
                                <div class="stat-title">ผู้ใช้ทั้งหมด</div>
                                <div class="stat-number"><?php echo number_format($stats['users']); ?></div>
                            </div>
                        </div>
                    </a>
                </div>

                <div class="col-md-6 col-xl-3">
                    <a href="manage_shop.php" class="dashboard-card">
                        <div class="card shadow-sm text-center">
                            <div class="card-body p-4">
                                <div class="icon-circle bg-success bg-opacity-10">
                                    <i class="bi bi-shop fs-2 text-success"></i>
                                </div>
                                <div class="stat-title">ร้านค้าทั้งหมด</div>
                                <div class="stat-number"><?php echo number_format($stats['shops']); ?></div>
                            </div>
                        </div>
                    </a>
                </div>

                <div class="col-md-6 col-xl-3">
                    <a href="manage_product.php" class="dashboard-card">
                        <div class="card shadow-sm text-center">
                            <div class="card-body p-4">
                                <div class="icon-circle bg-warning bg-opacity-10">
                                    <i class="bi bi-box-seam-fill fs-2 text-warning"></i>
                                </div>
                                <div class="stat-title">สินค้าในระบบ</div>
                                <div class="stat-number"><?php echo number_format($stats['products']); ?></div>
                            </div>
                        </div>
                    </a>
                </div>

                <div class="col-md-6 col-xl-3">
                    <a href="report.php" class="dashboard-card">
                        <div class="card shadow-sm text-center">
                            <div class="card-body p-4">
                                <div class="icon-circle bg-danger bg-opacity-10">
                                    <i class="bi bi-send-exclamation-fill fs-2 text-danger"></i>
                                </div>
                                <div class="stat-title">การรายงานปัญหา</div>
                                <div class="stat-number"><?php echo number_format($stats['reports']); ?></div>
                            </div>
                        </div>
                    </a>
                </div>

            </div>
            
            </div>
    </div>
</div>

<?php require_once("../layout/footer.php"); ?>
