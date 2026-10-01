<?php
require_once("../config/connect.php");
require_once("../layout/header.php");
require_once("../layout/check_admin.php");

$userid = (int)($_SESSION['userid'] ?? 0);
if($userid <= 0){
    header("Location: /wayside_edit/login.php");
    exit;
}

// ====== ดึงข้อมูลผู้ใช้ ======
$user = $users->get_user_by_id($userid);
if(!$user){
    echo "<div class='container my-5'><div class='alert alert-danger text-center shadow-sm'>ไม่พบข้อมูลผู้ใช้</div></div>";
    require_once("../layout/footer.php");
    exit;
}

// ====== อัปเดตข้อมูลส่วนตัว ======
if(isset($_POST['update_profile'])){
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name  = trim($_POST['last_name'] ?? '');
    $phone      = trim($_POST['phone_number'] ?? '');
    $email      = trim($_POST['email'] ?? '');

    if($first_name === '' || $last_name === '' || $phone === '' || $email === ''){
        $_SESSION['profile_status'] = 'empty';
    } else {
        $ok = $controller->update_user_profile($userid, $first_name, $last_name, $phone, $email);
        if($ok){
            $_SESSION['profile_status'] = 'success';
        }else{
            $_SESSION['profile_status'] = 'error';
        }
    }
    // รีเฟรชหน้าเพื่อเคลียร์ POST และแสดง Alert
    echo "<script>window.location='/wayside_edit/admin/profile.php';</script>";
    exit;
}
?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
/* =========================================
   Sidebar Styles
========================================= */
.sidebar-wrapper {
    min-height: calc(100vh - 60px);
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
    background-color: #fff8e1;
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
   Main Content Styles
========================================= */
.main-content {
    background-color: #f8f9fc;
    padding: 30px;
    min-height: calc(100vh - 60px);
}

/* Profile Card Styles */
.card-custom {
    border-radius: 15px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    border: none;
}
.profile-icon-container {
    width: 100px;
    height: 100px;
    background-color: #fff3cd;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
    color: #ffc107;
    font-size: 3.5rem;
    box-shadow: 0 4px 10px rgba(255, 193, 7, 0.2);
}
.info-label {
    font-size: 0.85rem;
    color: #6c757d;
    margin-bottom: 2px;
    font-weight: 600;
}
.info-value {
    font-size: 1.1rem;
    color: #333;
    font-weight: 500;
    margin-bottom: 15px;
}
.fade-in {
    animation: fadeIn 0.3s ease-in-out;
}
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
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
                    <a href="dashbord.php" class="nav-link nav-link-custom">
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
                <h3 class="fw-bold mb-0 text-dark">ข้อมูลส่วนตัว (Admin Profile)</h3>
            </div>
            
            <div class="row align-items-stretch g-4 justify-content-center">
                <div class="col-lg-8">
                    <div class="card card-custom h-100">
                        <div class="card-header bg-warning text-white text-center py-3">
                            <h5 class="mb-0 fw-bold"><i class="bi bi-person-badge"></i> ข้อมูลแอดมิน</h5>
                        </div>
                        
                        <div class="card-body p-4 pt-5 relative">
                            <div class="profile-icon-container mb-4">
                                <i class="bi bi-person-fill"></i>
                            </div>

                            <div id="viewMode" class="fade-in">
                                <h4 class="text-center fw-bold mb-4"><?php echo htmlspecialchars($user['first_name'].' '.$user['last_name']); ?></h4>
                                
                                <div class="row px-md-3">
                                    <div class="col-6">
                                        <div class="info-label">ชื่อผู้ใช้งาน (Username)</div>
                                        <div class="info-value"><i class="bi bi-at text-warning"></i> <?php echo htmlspecialchars($user['username']); ?></div>
                                    </div>
                                    <div class="col-6">
                                        <div class="info-label">สิทธิ์การใช้งาน</div>
                                        <div class="info-value">
                                            <span class='badge bg-danger'>Admin</span>
                                        </div>
                                    </div>
                                    <div class="col-6 mt-2">
                                        <div class="info-label">เบอร์โทรศัพท์ติดต่อ</div>
                                        <div class="info-value"><i class="bi bi-telephone-fill text-success"></i> <?php echo htmlspecialchars($user['phone_number'] ?: '- ไม่ได้ระบุ -'); ?></div>
                                    </div>
                                    <div class="col-6 mt-2">
                                        <div class="info-label">อีเมล</div>
                                        <div class="info-value"><i class="bi bi-envelope-fill text-danger"></i> <?php echo htmlspecialchars($user['email'] ?: '- ไม่ได้ระบุ -'); ?></div>
                                    </div>
                                </div>

                                <div class="text-center mt-4 pt-3 border-top">
                                    <button class="btn btn-outline-warning text-dark fw-bold px-4 rounded-pill" onclick="toggleEditMode()">
                                        <i class="bi bi-pencil-square"></i> แก้ไขข้อมูลส่วนตัว
                                    </button>
                                </div>
                            </div>

                            <div id="editMode" class="d-none fade-in">
                                <form method="POST">
                                    <div class="mb-3">
                                        <label class="form-label text-muted small fw-bold">ชื่อผู้ใช้ (เปลี่ยนไม่ได้)</label>
                                        <input class="form-control bg-light text-muted" value="<?php echo htmlspecialchars($user['username']); ?>" readonly>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-bold">ชื่อ <span class="text-danger">*</span></label>
                                            <input name="first_name" class="form-control border-warning" value="<?php echo htmlspecialchars($user['first_name'] ?? ''); ?>" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-bold">นามสกุล <span class="text-danger">*</span></label>
                                            <input name="last_name" class="form-control border-warning" value="<?php echo htmlspecialchars($user['last_name'] ?? ''); ?>" required>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-bold">เบอร์โทรศัพท์ <span class="text-danger">*</span></label>
                                            <input name="phone_number" class="form-control border-warning" value="<?php echo htmlspecialchars($user['phone_number'] ?? ''); ?>" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-bold">อีเมล <span class="text-danger">*</span></label>
                                            <input type="email" name="email" class="form-control border-warning" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-center gap-2 mt-4 pt-3 border-top">
                                        <button type="button" class="btn btn-light border px-4 rounded-pill fw-bold" onclick="toggleEditMode()">ยกเลิก</button>
                                        <button type="submit" name="update_profile" class="btn btn-warning text-white px-4 rounded-pill fw-bold shadow-sm">
                                            <i class="bi bi-save"></i> บันทึกข้อมูล
                                        </button>
                                    </div>
                                </form>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
function toggleEditMode(){
    const view = document.getElementById('viewMode');
    const edit = document.getElementById('editMode');
    if(view.classList.contains('d-none')){
        view.classList.remove('d-none');
        edit.classList.add('d-none');
    }else{
        view.classList.add('d-none');
        edit.classList.remove('d-none');
    }
}

<?php if(isset($_SESSION['profile_status'])): ?>
    <?php if($_SESSION['profile_status'] == 'success'): ?>
        Swal.fire({
            icon: 'success',
            title: 'สำเร็จ',
            text: 'อัปเดตข้อมูลส่วนตัวเรียบร้อยแล้ว',
            confirmButtonColor: '#28a745',
            confirmButtonText: 'ตกลง'
        });
    <?php elseif($_SESSION['profile_status'] == 'empty'): ?>
        Swal.fire({
            icon: 'warning',
            title: 'ข้อมูลไม่ครบถ้วน',
            text: 'กรุณากรอกข้อมูลที่มีเครื่องหมาย * ให้ครบทุกช่อง',
            confirmButtonColor: '#ffc107',
            confirmButtonText: 'ตกลง'
        });
    <?php else: ?>
        Swal.fire({
            icon: 'error',
            title: 'เกิดข้อผิดพลาด',
            text: 'ไม่สามารถอัปเดตข้อมูลได้ โปรดลองอีกครั้ง',
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'ตกลง'
        });
    <?php endif; ?>
    <?php unset($_SESSION['profile_status']); ?>
<?php endif; ?>
</script>

<?php require_once("../layout/footer.php"); ?>
