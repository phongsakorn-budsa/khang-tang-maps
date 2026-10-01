<?php
require_once("config/connect.php");
require_once("layout/header.php");
require_once("send_otp.php"); // ไฟล์สำหรับส่ง OTP ด้วย PHPMailer

$step = $_SESSION['reset_step'] ?? 1;
$error_msg = "";
$success_msg = "";

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    
    // Step 1: ขอรับ OTP
    if(isset($_POST['request_otp'])){
        $email = trim($_POST['email'] ?? '');
        
        $user = $users->get_user_by_email($email);
        
        if($user){
            // สุ่มรหัส OTP 6 หลัก
            $otp = sprintf("%06d", mt_rand(1, 999999));
            
            // เก็บลง Session พร้อมเวลาหมดอายุ (5 นาที)
            $_SESSION['reset_email'] = $email;
            $_SESSION['reset_otp'] = $otp;
            $_SESSION['reset_expire'] = time() + (5 * 60);
            
            // ส่ง OTP ทางอีเมล
            $sent = sendOTP($email, $otp);
            
            if($sent){
                $_SESSION['reset_step'] = 2; // ไปที่ขั้นตอนยืนยัน OTP
                header("Location: forgot_password.php");
                exit;
            }else{
                $error_msg = "เกิดข้อผิดพลาดในการส่งอีเมล กรุณาลองใหม่อีกครั้ง (โปรดตรวจสอบการตั้งค่า SMTP ใน send_otp.php)";
            }
        }else{
            $error_msg = "ไม่พบอีเมลนี้ในระบบ";
        }
    }
    
    // Step 2: ยืนยัน OTP
    if(isset($_POST['verify_otp'])){
        $input_otp = trim($_POST['otp'] ?? '');
        $saved_otp = $_SESSION['reset_otp'] ?? '';
        $expire_time = $_SESSION['reset_expire'] ?? 0;
        
        if(time() > $expire_time){
            $error_msg = "รหัส OTP หมดอายุแล้ว กรุณาขอรหัสใหม่";
            $_SESSION['reset_step'] = 1;
        }elseif($input_otp === $saved_otp){
            $_SESSION['reset_step'] = 3; // ไปที่ขั้นตอนตั้งรหัสผ่านใหม่
            header("Location: forgot_password.php");
            exit;
        }else{
            $error_msg = "รหัส OTP ไม่ถูกต้อง";
        }
    }
    
    // Step 3: ตั้งรหัสผ่านใหม่
    if(isset($_POST['reset_password'])){
        $password = trim($_POST['password'] ?? '');
        $confirm = trim($_POST['confirm_password'] ?? '');
        $email = $_SESSION['reset_email'] ?? '';
        
        if(strlen($password) <= 6 || !preg_match("/[a-zA-Z]/", $password)){
            $error_msg = "Password ต้องยาวมากกว่า 6 ตัว และมีตัวอักษรภาษาอังกฤษอย่างน้อย 1 ตัว";
        }elseif($password !== $confirm){
            $error_msg = "รหัสผ่านไม่ตรงกัน";
        }else{
            $user = $users->get_user_by_email($email);
            if($user){
                $new_hashed = md5($password . $user['username']);
                $users->update_password($user['user_id'], $new_hashed);
                
                // ล้างค่า Session
                unset($_SESSION['reset_step'], $_SESSION['reset_email'], $_SESSION['reset_otp'], $_SESSION['reset_expire']);
                
                $success_msg = true;
            }
        }
    }
}
?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
.reset-card{ border-radius: 12px; }
.reset-card .card-header{ border-radius: 12px 12px 0 0; }
</style>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-5">

            <div class="card shadow reset-card">
                <div class="card-header bg-warning text-white text-center py-3">
                    <h4 class="mb-0 fw-bold"><i class="bi bi-key-fill"></i> ลืมรหัสผ่าน</h4>
                </div>
                
                <div class="card-body px-4 py-4">
                    
                    <?php if($step == 1): ?>
                        <!-- ขั้นตอน 1: กรอก Email -->
                        <form method="POST">
                            <p class="text-muted small text-center mb-4">
                                กรุณากรอกอีเมลของคุณที่ลงทะเบียนไว้ ระบบจะส่งรหัส OTP 6 หลัก ไปให้ทางอีเมล
                            </p>
                            <div class="mb-4">
                                <label class="form-label fw-bold">อีเมล (Email)</label>
                                <input type="email" class="form-control" name="email" placeholder="example@gmail.com" required>
                            </div>
                            <div class="d-grid gap-2">
                                <button type="submit" name="request_otp" class="btn btn-warning text-white fw-bold shadow-sm">ส่งรหัส OTP</button>
                                <a href="login.php" class="btn btn-light border">กลับหน้าเข้าสู่ระบบ</a>
                            </div>
                        </form>
                        
                    <?php elseif($step == 2): ?>
                        <!-- ขั้นตอน 2: ยืนยัน OTP -->
                        <form method="POST">
                            <div class="alert alert-success text-center small">
                                รหัส OTP ได้ถูกส่งไปที่ <strong><?php echo htmlspecialchars($_SESSION['reset_email']); ?></strong> แล้ว<br>
                                (รหัสมีอายุ 5 นาที)
                            </div>
                            <div class="mb-4 text-center">
                                <label class="form-label fw-bold">กรอกรหัส OTP 6 หลัก</label>
                                <input type="text" class="form-control text-center fs-3 fw-bold tracking-widest" name="otp" maxlength="6" autocomplete="off" required>
                            </div>
                            <div class="d-grid gap-2">
                                <button type="submit" name="verify_otp" class="btn btn-warning text-white fw-bold shadow-sm">ยืนยัน OTP</button>
                                <a href="forgot_password.php?cancel=1" class="btn btn-light border text-danger">ยกเลิก</a>
                                <?php 
                                    if(isset($_GET['cancel'])){
                                        unset($_SESSION['reset_step']);
                                        echo "<script>window.location='forgot_password.php';</script>";
                                    }
                                ?>
                            </div>
                        </form>
                        
                    <?php elseif($step == 3): ?>
                        <!-- ขั้นตอน 3: ตั้งรหัสผ่านใหม่ -->
                        <form method="POST">
                            <p class="text-muted small text-center mb-4">ยืนยันตัวตนสำเร็จ! กรุณาตั้งรหัสผ่านใหม่ของคุณ</p>
                            <div class="mb-3">
                                <label class="form-label fw-bold">รหัสผ่านใหม่ (New Password)</label>
                                <input type="password" class="form-control" name="password" required>
                            </div>
                            <div class="mb-4">
                                <label class="form-label fw-bold">ยืนยันรหัสผ่านใหม่ (Confirm Password)</label>
                                <input type="password" class="form-control" name="confirm_password" required>
                            </div>
                            <div class="d-grid gap-2">
                                <button type="submit" name="reset_password" class="btn btn-success text-white fw-bold shadow-sm">บันทึกรหัสผ่านใหม่</button>
                            </div>
                        </form>
                    <?php endif; ?>
                    
                </div>
            </div>

        </div>
    </div>
</div>

<?php if($error_msg !== ""): ?>
<script>
    Swal.fire({
        icon: 'error',
        title: 'ผิดพลาด',
        text: '<?php echo $error_msg; ?>',
        confirmButtonColor: '#dc3545'
    });
</script>
<?php endif; ?>

<?php if($success_msg): ?>
<script>
    Swal.fire({
        icon: 'success',
        title: 'เปลี่ยนรหัสผ่านสำเร็จ!',
        text: 'รหัสผ่านของคุณถูกเปลี่ยนเรียบร้อยแล้ว ระบบจะพากลับไปหน้าเข้าสู่ระบบ',
        timer: 3000,
        showConfirmButton: false,
        timerProgressBar: true
    }).then(() => {
        window.location.href = 'login.php';
    });
</script>
<?php endif; ?>

<?php require_once("layout/footer.php"); ?>
