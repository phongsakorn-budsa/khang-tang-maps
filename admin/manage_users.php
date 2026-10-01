<?php
require_once("../config/connect.php");
require_once("../layout/header.php");
require_once("../layout/check_admin.php");

// รับค่าค้นหา
$keyword = $_GET['keyword'] ?? '';
$role    = $_GET['role'] ?? '';

// ดึงข้อมูลผู้ใช้ทั้งหมดเพื่อทำ Realtime Search บน Client
$users = $controller->search_users('', '');
?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
/* =========================================
   Sidebar Styles (เมนูด้านซ้าย)
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
   Main Content Styles (เนื้อหาด้านขวา)
========================================= */
.main-content {
    background-color: #f8f9fc;
    padding: 30px;
    min-height: calc(100vh - 60px);
}

/* Custom Table */
.table-custom th {
    background-color: #f8f9fa;
    color: #495057;
    font-weight: 600;
    border-bottom: 2px solid #dee2e6;
    text-align: center;
    white-space: nowrap;
}
.table-custom td {
    vertical-align: middle;
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
                    <a href="manage_users.php" class="nav-link nav-link-custom active">
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
                <h3 class="fw-bold mb-0 text-dark">ระบบจัดการผู้ใช้งาน (Admin)</h3>
            </div>

            <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
                <div class="card-body p-4">
                    <form onsubmit="return false;" class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label text-muted small fw-bold">คำค้นหา</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                                <input type="text" id="searchInput" class="form-control border-start-0 bg-light" placeholder="ค้นหาชื่อ หรือ Username..." onkeyup="filterTable()">
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label text-muted small fw-bold">กรองตามสิทธิ์</label>
                            <select id="roleFilter" class="form-select bg-light" onchange="filterTable()">
                                <option value="">-- ทุกสิทธิ์การใช้งาน --</option>
                                <option value="admin">ผู้ดูแลระบบ (Admin)</option>
                                <option value="seller">เจ้าของร้าน (Seller)</option>
                            </select>
                        </div>

                        <div class="col-md-4 d-flex gap-2">
                            <button type="button" class="btn btn-light border fw-bold w-100" onclick="resetFilter()">
                                ล้างค่า
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card shadow-sm border-0" style="border-radius: 12px; overflow: hidden;">
                <div class="card-body p-0 table-responsive">
                    <table class="table table-hover table-custom align-middle mb-0">
                        <thead>
                            <tr>
                                <th width="5%">#</th>
                                <th width="15%">Username</th>
                                <th width="20%">ชื่อ-นามสกุล</th>
                                <th width="15%">ติดต่อ (โทร/อีเมล)</th>
                                <th width="15%">สิทธิ์ใช้งาน</th>
                                <th width="15%">วันที่สมัคร</th>
                                <th width="15%">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if($users && count($users) > 0){ ?>
                            <?php $i=1; foreach($users as $u){ ?>
                                <tr class="user-row" data-name="<?php echo htmlspecialchars(mb_strtolower($u['username'].' '.$u['first_name'].' '.$u['last_name'], 'UTF-8')); ?>" data-role="<?php echo htmlspecialchars($u['role']); ?>">
                                    <td class="text-center"><span class="text-muted"><?php echo $i++; ?></span></td>
                                    
                                    <td class="fw-bold text-dark text-center">
                                        <i class="bi bi-person-circle text-muted me-1"></i> <?php echo htmlspecialchars($u['username']); ?>
                                    </td>
                                    
                                    <td class="text-center"><?php echo htmlspecialchars($u['first_name'].' '.$u['last_name']); ?></td>
                                    
                                    <td class="text-center">
                                        <?php if(!empty($u['phone_number'])) { ?>
                                            <a href="tel:<?php echo htmlspecialchars($u['phone_number']); ?>" class="text-decoration-none text-success fw-bold d-block">
                                                <i class="bi bi-telephone-fill"></i> <?php echo htmlspecialchars($u['phone_number']); ?>
                                            </a>
                                        <?php } ?>
                                        <?php if(!empty($u['email'])) { ?>
                                            <a href="mailto:<?php echo htmlspecialchars($u['email']); ?>" class="text-decoration-none text-danger small d-block">
                                                <i class="bi bi-envelope-fill"></i> <?php echo htmlspecialchars($u['email']); ?>
                                            </a>
                                        <?php } ?>
                                        <?php if(empty($u['phone_number']) && empty($u['email'])) { ?>
                                            <span class="text-muted small">-</span>
                                        <?php } ?>
                                    </td>

                                    <td class="text-center">
                                        <?php if($u['role'] == 'admin'){ ?>
                                            <span class="badge bg-danger shadow-sm px-3 py-2 rounded-pill"><i class="bi bi-star-fill"></i> Admin</span>
                                        <?php } else { ?>
                                            <span class="badge bg-primary shadow-sm px-3 py-2 rounded-pill"><i class="bi bi-shop"></i> Seller</span>
                                        <?php } ?>
                                    </td>

                                    <td class="text-center text-muted small">
                                        <i class="bi bi-calendar-check"></i> <?php echo date("d/m/Y", strtotime($u['created_at'])); ?>
                                    </td>

                                    <td class="text-center">
                                        <div class="btn-group shadow-sm" role="group">
                                            <a href="edit_user.php?id=<?php echo $u['user_id']; ?>" class="btn btn-sm btn-outline-warning text-dark" title="แก้ไขข้อมูล">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>

                                            <?php if($u['role'] != 'admin'){ ?>
                                                <a href="delete_user.php?id=<?php echo $u['user_id']; ?>" class="btn btn-sm btn-outline-danger" title="ลบผู้ใช้" onclick="confirmDeleteUser(event, this.href, this)">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            <?php } else { ?>
                                                <button class="btn btn-sm btn-outline-secondary" disabled title="ไม่สามารถลบ Admin ได้">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            <?php } ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php } ?>
                        <?php } else { ?>
                            <tr id="noDataRow">
                                <td colspan="7" class="text-center py-5">
                                    <i class="bi bi-people text-muted opacity-25" style="font-size: 3rem;"></i>
                                    <p class="text-muted mt-3 mb-0">ไม่พบข้อมูลผู้ใช้งานในระบบ</p>
                                </td>
                            </tr>
                        <?php } ?>
                        <tr id="jsNoDataRow" style="display: none;">
                            <td colspan="7" class="text-center py-5">
                                <i class="bi bi-people text-muted opacity-25" style="font-size: 3rem;"></i>
                                <p class="text-muted mt-3 mb-0">ไม่พบข้อมูลผู้ใช้งานที่ค้นหา</p>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
function filterTable() {
    let keyword = document.getElementById('searchInput').value.toLowerCase().trim();
    let roleFilter = document.getElementById('roleFilter').value;
    
    sessionStorage.setItem('admin_user_keyword', keyword);
    sessionStorage.setItem('admin_user_role', roleFilter);
    
    let rows = document.querySelectorAll('.user-row');
    let visibleCount = 0;
    
    rows.forEach(row => {
        let name = row.getAttribute('data-name');
        let role = row.getAttribute('data-role');
        
        let matchName = name.includes(keyword);
        let matchRole = (roleFilter === "") || (role === roleFilter);
        
        if (matchName && matchRole) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });

    let jsNoData = document.getElementById('jsNoDataRow');
    if(jsNoData) {
        if(visibleCount === 0 && rows.length > 0) {
            jsNoData.style.display = '';
        } else {
            jsNoData.style.display = 'none';
        }
    }
}

function resetFilter() {
    document.getElementById('searchInput').value = '';
    document.getElementById('roleFilter').value = '';
    sessionStorage.removeItem('admin_user_keyword');
    sessionStorage.removeItem('admin_user_role');
    filterTable();
}

document.addEventListener('DOMContentLoaded', function() {
    let savedKeyword = sessionStorage.getItem('admin_user_keyword');
    let savedRole = sessionStorage.getItem('admin_user_role');
    
    if(savedKeyword) {
        document.getElementById('searchInput').value = savedKeyword;
    }
    if(savedRole) {
        document.getElementById('roleFilter').value = savedRole;
    }
    
    if(savedKeyword || savedRole) {
        filterTable();
    }
});

// สคริปต์สำหรับ Alert การลบข้อมูล (SweetAlert2)
function confirmDeleteUser(e, url, el) {
    e.preventDefault();
    Swal.fire({
        title: 'ยืนยันการลบผู้ใช้?',
        text: "ข้อมูลของผู้ใช้คนนี้ (รวมถึงร้านค้าและสินค้าของเขา) จะถูกลบอย่างถาวร!",
        icon: 'error',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'ใช่, ลบผู้ใช้',
        cancelButtonText: 'ยกเลิก',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(url).then(response => {
                if(response.ok) {
                    if(el) {
                        el.closest('tr').remove();
                        filterTable();
                    }
                    Swal.fire({
                        title: 'ลบสำเร็จ!',
                        text: 'ผู้ใช้นี้ถูกลบออกจากระบบแล้ว',
                        icon: 'success',
                        timer: 1500,
                        showConfirmButton: false
                    });
                } else {
                    Swal.fire('ผิดพลาด', 'ไม่สามารถลบผู้ใช้ได้', 'error');
                }
            }).catch(error => {
                Swal.fire('ผิดพลาด', 'ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้', 'error');
            });
        }
    })
}
</script>

<?php require_once("../layout/footer.php"); ?>
