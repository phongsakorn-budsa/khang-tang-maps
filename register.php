<?php 
require_once("config/connect.php");
require_once("layout/header.php");

// สร้างตัวแปรไว้รอรับค่าเก่า (ถ้ามีการส่งฟอร์มแล้วผิดพลาด)
$old_username = "";
$old_email = "";
$old_first_name = "";
$old_last_name = "";
$old_phone = "";
$error_msg = "";
$success_msg = false;

if(isset($_POST["submit"])){
    // เก็บค่าที่กรอกมาใส่ตัวแปรไว้ก่อน
    $old_username   = trim($_POST["username"] ?? '');
    $old_email      = trim($_POST["email"] ?? '');
    $old_first_name = trim($_POST["first_name"] ?? '');
    $old_last_name  = trim($_POST["last_name"] ?? '');
    $old_phone      = trim($_POST["phone_number"] ?? '');
    
    $password = trim($_POST["password"] ?? '');
    $confirm  = trim($_POST["confirm_password"] ?? '');
    $role = "seller";
    
    date_default_timezone_set('Asia/Bangkok');
    $created_at = date("Y-m-d H:i:s");

    // ตรวจสอบเงื่อนไข
    if($old_username === '' || $old_email === '' || $password === '' || $confirm === ''){
        $error_msg = "กรุณากรอกข้อมูลให้ครบทุกช่อง";
    }
    elseif(strlen($old_username) < 6){
        $error_msg = "Username ต้องมีอย่างน้อย 6 ตัวอักษร";
    }
    elseif(strlen($password) <= 6 || !preg_match("/[a-zA-Z]/", $password)){
        $error_msg = "Password ต้องยาวมากกว่า 6 ตัว และมีตัวอักษรภาษาอังกฤษอย่างน้อย 1 ตัว";
    }
    elseif($password !== $confirm){
        $error_msg = "Password และ Confirm Password ต้องตรงกัน";
    }

    // ถ้าไม่มี Error ให้ทำการบันทึก
    if($error_msg === ""){
        $check_status = $users->insert_users(
            $old_username, $password, $role,
            $old_first_name, $old_last_name,
            $old_phone, $old_email, $created_at
        );

        if ($check_status) {
            $success_msg = true; // เซ็ตค่าเป็น true เพื่อให้สคริปต์แจ้งเตือนทำงาน
        } else {
            $error_msg = "Username นี้มีผู้ใช้งานแล้ว กรุณาใช้ชื่ออื่น";
        }
    }
}
?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
.auth-card {
    border: none;
    border-radius: 20px;
    box-shadow: 0 15px 35px rgba(0,0,0,0.08);
    overflow: hidden;
}
.auth-card-body {
    padding: 3rem 2.5rem;
}
.auth-title {
    font-weight: 700;
    color: #2b2b2b;
    margin-bottom: 0.5rem;
}
.auth-subtitle {
    color: #6c757d;
    font-size: 0.95rem;
    margin-bottom: 2rem;
}
.form-floating .form-control {
    border-radius: 12px;
    border: 1.5px solid #e0e0e0;
}
.form-floating .form-control:focus {
    border-color: #ffca28;
    box-shadow: 0 0 0 4px rgba(255, 202, 40, 0.15);
}
.form-floating > label {
    color: #6c757d;
}
.btn-auth {
    background: linear-gradient(135deg, #fde047 0%, #facc15 100%);
    color: #1f2937;
    border: none;
    border-radius: 12px;
    padding: 14px;
    font-weight: 700;
    font-size: 1.1rem;
    transition: all 0.3s ease;
}
.btn-auth:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(250, 204, 21, 0.4);
    color: #000;
}
.auth-link {
    color: #ffb300;
    font-weight: 600;
    text-decoration: none;
    transition: color 0.2s;
}
.auth-link:hover {
    color: #f57f17;
}
</style>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-7">

            <div class="card auth-card">
                <div class="card-body auth-card-body bg-white">
                    <div class="text-center mb-4">
                        <img src="/wayside_edit/image/logo.png" alt="Logo" style="height: 60px; border-radius: 12px; margin-bottom: 15px; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
                        <h3 class="auth-title">สร้างบัญชีร้านค้าใหม่</h3>
                        <p class="auth-subtitle">ร่วมเป็นส่วนหนึ่งของ Khang Tang Maps</p>
                    </div>

                    <form action="register.php" method="POST">
                        
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" id="reg_username" name="username" placeholder="Username" value="<?php echo htmlspecialchars($old_username); ?>" required minlength="6">
                            <label for="reg_username"><i class="bi bi-person text-muted me-1"></i> ชื่อผู้ใช้ (อย่างน้อย 6 ตัวอักษร)</label>
                        </div>

                        <div class="form-floating mb-3">
                            <input type="email" class="form-control" id="reg_email" name="email" placeholder="Email" value="<?php echo htmlspecialchars($old_email); ?>" required>
                            <label for="reg_email"><i class="bi bi-envelope text-muted me-1"></i> อีเมล (Email)</label>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="password" class="form-control" id="reg_password" name="password" placeholder="Password" required>
                                    <label for="reg_password"><i class="bi bi-lock text-muted me-1"></i> รหัสผ่าน (6 ตัวขึ้นไป)</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="password" class="form-control" id="reg_confirm" name="confirm_password" placeholder="Confirm Password" required>
                                    <label for="reg_confirm"><i class="bi bi-shield-lock text-muted me-1"></i> ยืนยันรหัสผ่าน</label>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="reg_fname" name="first_name" placeholder="First Name" value="<?php echo htmlspecialchars($old_first_name); ?>" required>
                                    <label for="reg_fname"><i class="bi bi-card-text text-muted me-1"></i> ชื่อจริง</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="reg_lname" name="last_name" placeholder="Last Name" value="<?php echo htmlspecialchars($old_last_name); ?>" required>
                                    <label for="reg_lname"><i class="bi bi-card-text text-muted me-1"></i> นามสกุล</label>
                                </div>
                            </div>
                        </div>

                        <div class="form-floating mb-4">
                            <input type="tel" class="form-control" id="reg_phone" name="phone_number" placeholder="Phone" pattern="[0-9]{10}" maxlength="10" value="<?php echo htmlspecialchars($old_phone); ?>" required>
                            <label for="reg_phone"><i class="bi bi-telephone text-muted me-1"></i> เบอร์โทรศัพท์ (10 หลัก)</label>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" name="submit" class="btn btn-auth">
                                สมัครสมาชิก
                            </button>
                        </div>
                        
                        <div class="text-center mt-4 pt-3 border-top">
                            <span class="text-muted small">มีบัญชีผู้ใช้งานอยู่แล้ว?</span>
                            <a href="login.php" class="auth-link ms-1">เข้าสู่ระบบที่นี่</a>
                        </div>

                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

<?php if($success_msg): ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            icon: 'success',
            title: 'สมัครสมาชิกสำเร็จ!',
            text: 'ระบบกำลังพาท่านไปหน้าเข้าสู่ระบบ...',
            timer: 2000,
            showConfirmButton: false,
            timerProgressBar: true
        }).then(() => {
            window.location.href = 'login.php';
        });
    });
</script>
<?php endif; ?>

<?php if($error_msg !== ""): ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            icon: 'error',
            title: 'ข้อมูลไม่ถูกต้อง',
            text: '<?php echo $error_msg; ?>',
            confirmButtonColor: '#dc3545'
        });
    });
</script>
<?php endif; ?>

<?php require_once("layout/footer.php"); ?>