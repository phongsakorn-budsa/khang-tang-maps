<?php 
require_once("config/connect.php");
require_once("layout/header.php");

if($_SERVER["REQUEST_METHOD"]=="POST"){
    $username = $_POST["username"];
    $password = $_POST["password"];
    $new_password = md5($password.$username);
    $result = $users->getUser($username,$new_password);

    if(!$result){
        echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>';
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'error',
                    title: 'เข้าสู่ระบบล้มเหลว',
                    text: 'ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง',
                    confirmButtonColor: '#dc3545'
                });
            });
        </script>";
    }else{
        $_SESSION["username"] = $username;
        $_SESSION["userid"]   = $result["user_id"];
        $_SESSION["role"]     = $result["role"];
        
        if ($result["role"] === 'admin') {
            header("Location: admin/dashbord.php");
        } else {
            header("Location: index.php");
        }
        exit;
    }
}
?>

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
        <div class="col-md-6 col-lg-5 col-xl-4">
            <div class="card auth-card">
                <div class="card-body auth-card-body bg-white">
                    <div class="text-center mb-4">
                        <img src="/wayside_edit/image/logo.png" alt="Logo" style="height: 70px; border-radius: 12px; margin-bottom: 15px; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
                        <h3 class="auth-title">ยินดีต้อนรับ!</h3>
                        <p class="auth-subtitle">เข้าสู่ระบบเพื่อจัดการร้านค้าของคุณ</p>
                    </div>

                    <form method="POST" action="<?php echo htmlentities($_SERVER['PHP_SELF'])?>">

                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" id="username" name="username" placeholder="Username" required value="<?php echo $_POST['username'] ?? ''; ?>">
                            <label for="username"><i class="bi bi-person text-muted me-1"></i> ชื่อผู้ใช้ (Username)</label>
                        </div>

                        <div class="form-floating mb-2">
                            <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                            <label for="password"><i class="bi bi-lock text-muted me-1"></i> รหัสผ่าน (Password)</label>
                        </div>
                        
                        <div class="d-flex justify-content-end mb-4 px-1">
                            <a href="forgot_password.php" class="auth-link small">ลืมรหัสผ่าน?</a>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" name="submit" class="btn btn-auth">
                                เข้าสู่ระบบ
                            </button>
                        </div>
                        
                        <div class="text-center mt-4 pt-3 border-top">
                            <span class="text-muted small">ยังไม่มีบัญชีผู้ใช้?</span>
                            <a href="register.php" class="auth-link ms-1">สมัครสมาชิกใหม่</a>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once("layout/footer.php"); ?>
