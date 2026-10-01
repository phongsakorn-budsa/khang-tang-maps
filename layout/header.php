<?php
ob_start();
require_once(__DIR__ ."/session.php");
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Khang Tang Maps</title>
    <link rel="icon" type="image/png" href="/wayside_edit/image/logo.png">
    
    <link rel="stylesheet" href="/wayside_edit/asset/css/bootstrap.min.css">
    <script src="/wayside_edit/asset/js/bootstrap.bundle.min.js"></script>
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        /* ตั้งค่าฟอนต์หลักของทั้งเว็บไซต์ */
        body {
            font-family: 'Prompt', sans-serif !important;
            background-color: #f8f9fa;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* ตกแต่ง Navbar (แถบเมนูด้านบน) */
        .navbar-custom {
            background: linear-gradient(to right, #ffca28, #fff59d); /* ไล่สีเหลืองทองไปเหลืองอ่อน */
            padding: 15px 0;
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }
        
        .navbar-brand {
            font-size: 1.2rem;
            letter-spacing: 0.5px;
            color: #212529 !important;
        }

        /* ตกแต่งปุ่มเมนู เฉพาะบน Navbar เท่านั้น */
        .navbar-custom .nav-link {
            font-weight: 600;
            padding: 8px 16px !important;
            border-radius: 8px;
            transition: all 0.3s ease;
            margin: 0 4px;
            color: #333 !important;
        }
        .navbar-custom .nav-link:hover, .navbar-custom .nav-link:focus {
            background-color: rgba(0, 0, 0, 0.05);
            color: #000 !important;
            transform: translateY(-2px);
        }

        /* ตกแต่ง Dropdown Menu (เมนูย่อยของโปรไฟล์) */
        .dropdown-menu {
            border: none;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            padding: 10px;
            margin-top: 10px;
        }
        .dropdown-item {
            border-radius: 8px;
            padding: 8px 15px;
            font-weight: 500;
            color: #495057;
            transition: all 0.2s;
            margin-bottom: 2px;
        }
        .dropdown-item:hover {
            background-color: #fff8e1;
            color: #ffc107;
        }
        .dropdown-item.text-danger:hover {
            background-color: #fee2e2;
            color: #dc3545 !important;
        }
        
        .nav-icon {
            margin-right: 4px;
        }
    </style>
</head>
<body>
    <?php $isAdminPage = (strpos($_SERVER['SCRIPT_NAME'], '/admin/') !== false); ?>

    <nav class="navbar navbar-expand-lg navbar-light shadow-sm sticky-top navbar-custom">
        <div class="container">
            
            <a href="/wayside_edit/index.php" class="navbar-brand fw-bold d-flex align-items-center gap-2">
                <img src="/wayside_edit/image/logo.png" alt="Logo" style="height:45px; border-radius: 8px;">
                <div class="d-flex flex-column lh-1">
                    <span class="text-dark fs-5 fw-bold">Khang Tang Maps</span>
                    <span class="text-muted mt-1" style="font-size: 0.75rem; font-weight: 500;">Explore local, live the history</span>
                </div>
            </a>

            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#wayside-list">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="wayside-list">
                <ul class="navbar-nav ms-auto align-items-center">

                    <?php if(!$isAdminPage): ?>
                    
                    <?php if(!isset($_SESSION["role"]) || $_SESSION["role"] != "admin"): ?>
                    <li class="nav-item">
                        <a href="/wayside_edit/index.php" class="nav-link">
                            <i class="bi bi-house-door-fill nav-icon"></i> หน้าแรก
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="/wayside_edit/all_shops.php?filter=favorites" class="nav-link">
                            <i class="bi bi-heart-fill nav-icon text-danger"></i> รายการโปรด
                        </a>
                    </li>
                    <?php endif; ?>

                    <?php if(isset($_SESSION["role"]) && $_SESSION["role"] == "seller") { ?>
                    <li class="nav-item">
                        <a href="/wayside_edit/seller/myshop.php" class="nav-link">
                            <i class="bi bi-shop nav-icon"></i> ร้านค้าของฉัน
                        </a>
                    </li>
                    <?php } ?>

                    <?php if(isset($_SESSION["role"]) && $_SESSION["role"] == "admin") { ?>
                    <li class="nav-item">
                        <a href="/wayside_edit/admin/dashbord.php" class="nav-link">
                            <i class="bi bi-shield-lock-fill nav-icon"></i> ระบบแอดมิน
                        </a>
                    </li>
                    <?php } ?>
                    <?php endif; ?> <!-- End !isAdminPage -->

                    <?php if(!isset($_SESSION["userid"])){ ?>
                    <li class="nav-item ms-lg-2 mt-2 mt-lg-0">
                        <a href="/wayside_edit/login.php" class="btn bg-white text-warning fw-bold px-4 shadow-sm rounded-pill border border-warning border-opacity-25">
                            <i class="bi bi-box-arrow-in-right nav-icon"></i> เข้าสู่ระบบร้านค้า
                        </a>
                    </li>
                    <?php } else { ?>
                    
                    <li class="nav-item dropdown ms-lg-2">
                        <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" data-bs-toggle="dropdown" style="<?php echo $isAdminPage ? 'background: #fff; padding: 5px 15px !important; border-radius: 50px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);' : ''; ?>">
                            <div class="bg-warning text-white rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px;">
                                <?php echo strtoupper(substr($_SESSION["username"] ?? 'U', 0, 1)); ?>
                            </div>
                            <span class="<?php echo $isAdminPage ? 'fw-bold' : ''; ?>"><?php echo isset($_SESSION["username"]) ? $_SESSION["username"] : "User"; ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end border-0">
                            <li>
                                <a class="dropdown-item" href="/wayside_edit/<?php echo (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') ? 'admin' : 'seller'; ?>/profile.php">
                                    <i class="bi bi-person-lines-fill text-warning me-2"></i> ข้อมูลส่วนตัว
                                </a>
                            </li>
                            <?php if(!$isAdminPage): ?>
                            <li><hr class="dropdown-divider opacity-25"></li>
                            <li>
                                <a class="dropdown-item text-danger fw-bold" href="/wayside_edit/logout.php">
                                    <i class="bi bi-box-arrow-right me-2"></i> ออกจากระบบ
                                </a>
                            </li>
                            <?php endif; ?>
                        </ul>
                    </li>

                    <?php } ?>
                </ul>
            </div>
        </div>
    </nav>