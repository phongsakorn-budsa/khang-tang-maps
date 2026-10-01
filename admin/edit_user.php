<?php
require_once("../config/connect.php");
require_once("../layout/header.php");
require_once("../layout/check_admin.php");

// ตรวจ id
if(!isset($_GET['id'])){
    header("Location: manage_users.php");
    exit;
}

$user_id = $_GET['id'];

// ดึงข้อมูลผู้ใช้
$user = $users->get_user_by_id($user_id);
if(!$user){
    header("Location: manage_users.php");
    exit;
}

// อัปเดตข้อมูล
if(isset($_POST['submit'])){
    $first_name   = $_POST['first_name'];
    $last_name    = $_POST['last_name'];
    $phone_number = $_POST['phone_number'];
    $email        = $_POST['email'];
    $role         = $_POST['role'];

    $result = $users->update_user(
        $user_id,
        $first_name,
        $last_name,
        $phone_number,
        $email,
        $role
    );

    if($result){
        $_SESSION['update_status'] = 'success';
    }else{
        $_SESSION['update_status'] = 'error';
    }
    echo "<script>window.location='edit_user.php?id=$user_id';</script>";
    exit;
}
?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    /* ตกแต่ง Card ให้ดูนุ่มนวลและมีมิติ */
    .card-custom {
        border-radius: 15px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.06);
    }
    
    /* ไอคอนผู้ใช้ด้านบน */
    .profile-icon {
        font-size: 4rem;
        color: #ffc107;
        margin-bottom: -15px;
    }
    
    /* ปรับแต่ง Label ตัวหนังสือหนา */
    .form-label {
        font-weight: 600;
        color: #495057;
    }
    
    /* ปรับปุ่มให้มนขึ้น */
    .btn-custom {
        border-radius: 10px;
        padding: 10px;
        font-weight: bold;
    }
</style>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            
            <div class="mb-3">
                <a href="manage_users.php" class="text-decoration-none text-muted fw-bold">
                    <i class="bi bi-arrow-left"></i> กลับไปหน้าจัดการผู้ใช้งาน
                </a>
            </div>

            <div class="card card-custom border-0">
                <div class="card-header bg-white pt-4 pb-0 border-0 text-center">
                    <i class="bi bi-person-circle profile-icon"></i>
                    <h4 class="fw-bold mt-3 text-dark">แก้ไขข้อมูลผู้ใช้งาน</h4>
                </div>
                
                <div class="card-body p-4 pt-3">
                    <form method="POST">

                        <div class="mb-4">
                            <label class="form-label text-muted small">ชื่อผู้ใช้ (Username)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-person text-muted"></i></span>
                                <input type="text" class="form-control bg-light border-start-0 text-muted" 
                                       value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">ชื่อ <span class="text-danger">*</span></label>
                                <input type="text" name="first_name" class="form-control" 
                                       value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">นามสกุล <span class="text-danger">*</span></label>
                                <input type="text" name="last_name" class="form-control" 
                                       value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">เบอร์โทรศัพท์ <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white"><i class="bi bi-telephone text-muted"></i></span>
                                    <input type="text" name="phone_number" class="form-control" 
                                           value="<?php echo htmlspecialchars($user['phone_number']); ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">อีเมล <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white"><i class="bi bi-envelope text-muted"></i></span>
                                    <input type="email" name="email" class="form-control" 
                                           value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">บทบาท (Role) <span class="text-danger">*</span></label>
                            <select name="role" class="form-select bg-light" required>
                                <option value="user"   <?php if($user['role']=='user') echo 'selected'; ?>>👤 User (ผู้ใช้งานทั่วไป)</option>
                                <option value="seller" <?php if($user['role']=='seller') echo 'selected'; ?>>🏪 Seller (เจ้าของร้าน)</option>
                                <option value="admin"  <?php if($user['role']=='admin') echo 'selected'; ?>>🛡️ Admin (ผู้ดูแลระบบ)</option>
                            </select>
                            <?php if($user['role']=='admin'){ ?>
                                <small class="text-danger d-block mt-1">* ระวัง: หากถอดสิทธิ์ Admin ผู้ใช้นี้จะเข้าถึงระบบหลังบ้านไม่ได้อีก</small>
                            <?php } ?>
                        </div>

                        <hr class="mb-4 opacity-25">

                        <div class="d-flex justify-content-between gap-3">
                            <a href="manage_users.php" class="btn btn-light w-50 btn-custom border">ยกเลิก</a>
                            <button type="submit" name="submit" class="btn btn-warning text-white w-50 btn-custom shadow-sm">
                                <i class="bi bi-save"></i> บันทึกการแก้ไข
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if(isset($_SESSION['update_status'])): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            <?php if($_SESSION['update_status'] == 'success'): ?>
                Swal.fire({
                    icon: 'success',
                    title: 'อัปเดตสำเร็จ!',
                    text: 'แก้ไขข้อมูลผู้ใช้งานเรียบร้อยแล้ว',
                    confirmButtonColor: '#198754',
                    timer: 2000,
                    timerProgressBar: true
                }).then(() => {
                    window.location = 'manage_users.php';
                });
            <?php else: ?>
                Swal.fire({
                    icon: 'error',
                    title: 'เกิดข้อผิดพลาด!',
                    text: 'ไม่สามารถแก้ไขข้อมูลได้ กรุณาลองใหม่อีกครั้ง',
                    confirmButtonColor: '#dc3545'
                });
            <?php endif; ?>
        });
    </script>
    <?php unset($_SESSION['update_status']); ?>
<?php endif; ?>

<?php require_once("../layout/footer.php"); ?>