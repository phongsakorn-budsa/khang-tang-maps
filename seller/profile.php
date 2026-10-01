<?php
require_once("../config/connect.php");
require_once("../layout/header.php");
require_once("../layout/check_login.php");

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

// ====== ดึงข้อมูลร้านค้า (ถ้ามี) ======
$my_shop = $controller->get_my_shop($userid);

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
    echo "<script>window.location='/wayside_edit/seller/profile.php';</script>";
    exit;
}
?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    /* ตกแต่ง Card */
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
    
    /* Animation เปลี่ยนโหมด */
    .fade-in {
        animation: fadeIn 0.3s ease-in-out;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>

<div class="container my-5">
    <div class="row align-items-stretch g-4">
        
        <div class="col-lg-6">
            <div class="card card-custom h-100">
                <div class="card-header bg-warning text-white text-center py-3">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-person-badge"></i> ข้อมูลส่วนตัว</h5>
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
                                    <?php 
                                        if($user['role'] == "seller") echo "<span class='badge bg-warning text-dark'>เจ้าของร้าน</span>";
                                        elseif($user['role'] == "admin") echo "<span class='badge bg-danger'>Admin</span>";
                                        else echo "<span class='badge bg-secondary'>ผู้ใช้งาน</span>";
                                    ?>
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

        <div class="col-lg-6">
            <div class="card card-custom h-100">
                <div class="card-header bg-white border-bottom text-center py-3">
                    <h5 class="mb-0 fw-bold text-dark"><i class="bi bi-shop text-warning"></i> ร้านค้าของคุณ</h5>
                </div>
                <div class="card-body d-flex flex-column align-items-center justify-content-center p-4">
                    
                    <?php if($my_shop) { ?>
                        <div class="position-relative mb-3">
                            <?php if(!empty($my_shop['shop_image'])){ ?>
                                <img src="../uploads/<?php echo htmlspecialchars($my_shop['shop_image']); ?>" class="rounded-3 shadow-sm" style="width: 100%; max-width: 300px; height: 180px; object-fit: cover;">
                            <?php } else { ?>
                                <div class="bg-light rounded-3 d-flex align-items-center justify-content-center text-muted shadow-sm" style="width: 300px; height: 180px;">
                                    <i class="bi bi-image" style="font-size: 3rem;"></i>
                                </div>
                            <?php } ?>
                            
                            <div class="position-absolute top-0 end-0 m-2">
                                <?php if($my_shop['is_open'] == 1){ ?>
                                    <span class="badge bg-success shadow">เปิดให้บริการ</span>
                                <?php } else { ?>
                                    <span class="badge bg-danger shadow">ปิดร้านอยู่</span>
                                <?php } ?>
                            </div>
                        </div>

                        <h4 class="fw-bold text-center mb-1"><?php echo htmlspecialchars($my_shop['shop_name']); ?></h4>
                        <p class="text-muted text-center small mb-4 px-3 text-truncate" style="max-width: 100%;">
                            <?php echo htmlspecialchars($my_shop['description']); ?>
                        </p>

                        <div class="d-grid w-100 px-md-4 gap-2 mt-auto">
                            <a href="myshop.php" class="btn btn-warning text-white fw-bold shadow-sm rounded-pill">
                                <i class="bi bi-box-arrow-in-right"></i> ไปที่ระบบจัดการร้านค้า
                            </a>
                        </div>

                    <?php } else { ?>
                        <i class="bi bi-shop-window text-muted opacity-50 mb-3" style="font-size: 5rem;"></i>
                        <h4 class="fw-bold text-muted">ยังไม่มีร้านค้า</h4>
                        <p class="text-muted text-center mb-4">สร้างร้านค้าของคุณเพื่อเริ่มลงขายสินค้าและรับออเดอร์จากลูกค้าได้ทันที</p>
                        
                        <a href="add_myshop.php" class="btn btn-warning text-white fw-bold shadow-sm rounded-pill px-5 py-2">
                            <i class="bi bi-plus-lg"></i> สร้างร้านค้าใหม่
                        </a>
                    <?php } ?>

                </div>
            </div>
        </div>

    </div>
</div>

<script>
function toggleEditMode() {
    const viewMode = document.getElementById('viewMode');
    const editMode = document.getElementById('editMode');
    
    if(viewMode.classList.contains('d-none')) {
        // เปลี่ยนกลับเป็นโหมดดูข้อมูล
        viewMode.classList.remove('d-none');
        editMode.classList.add('d-none');
    } else {
        // เปลี่ยนเป็นโหมดแก้ไข
        viewMode.classList.add('d-none');
        editMode.classList.remove('d-none');
    }
}
</script>

<?php if(isset($_SESSION['profile_status'])): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            <?php if($_SESSION['profile_status'] == 'success'): ?>
                Swal.fire({
                    icon: 'success',
                    title: 'อัปเดตข้อมูลสำเร็จ!',
                    text: 'ข้อมูลส่วนตัวของคุณถูกบันทึกเรียบร้อยแล้ว',
                    confirmButtonColor: '#198754',
                    timer: 2000,
                    timerProgressBar: true
                });
            <?php elseif($_SESSION['profile_status'] == 'empty'): ?>
                Swal.fire({
                    icon: 'warning',
                    title: 'ข้อมูลไม่ครบถ้วน',
                    text: 'กรุณากรอกข้อมูลที่มีเครื่องหมายดอกจัน (*) ให้ครบ',
                    confirmButtonColor: '#ffc107'
                });
            <?php else: ?>
                Swal.fire({
                    icon: 'error',
                    title: 'เกิดข้อผิดพลาด',
                    text: 'ไม่สามารถอัปเดตข้อมูลได้ กรุณาลองใหม่อีกครั้ง',
                    confirmButtonColor: '#dc3545'
                });
            <?php endif; ?>
        });
    </script>
    <?php unset($_SESSION['profile_status']); ?>
<?php endif; ?>

<?php require_once("../layout/footer.php"); ?>