<?php
require_once("../config/connect.php");
require_once("../layout/header.php");
require_once("../layout/check_admin.php");

// รับค่าค้นหา
$keyword = $_GET['keyword'] ?? '';
$status  = $_GET['status'] ?? '';

// ดึงข้อมูลร้าน (ดึงทั้งหมดมาไว้เพื่อทำ Realtime Search บน Client)
$shops = $controller->search_shops('', '');
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
    text-align: center;
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
                    <a href="manage_shop.php" class="nav-link nav-link-custom active">
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
                <h3 class="fw-bold mb-0 text-dark">ระบบจัดการร้านค้า (Admin)</h3>
            </div>

            <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
                <div class="card-body p-4">
                    <form onsubmit="return false;" class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label text-muted small fw-bold">คำค้นหา</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                                <input type="text" id="searchInput" class="form-control border-start-0 bg-light" placeholder="ชื่อร้าน..." onkeyup="filterTable()">
                            </div>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label text-muted small fw-bold">ประเภทร้าน</label>
                            <select id="typeFilter" class="form-select bg-light" onchange="filterTable()">
                                <option value="">-- ทุกประเภท --</option>
                                <option value="อาหาร">อาหาร</option>
                                <option value="เครื่องดื่ม">เครื่องดื่ม</option>
                                <option value="ของทานเล่น">ของทานเล่น</option>
                                <option value="ผลไม้">ผลไม้</option>
                                <option value="ของฝาก">ของฝาก</option>
                                <option value="ของใช้">ของใช้</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label text-muted small fw-bold">กรองตามสถานะ</label>
                            <select id="statusFilter" class="form-select bg-light" onchange="filterTable()">
                                <option value="">-- ทุกสถานะ --</option>
                                <option value="1">✅ เปิดให้บริการ</option>
                                <option value="0">❌ ปิดชั่วคราว</option>
                            </select>
                        </div>

                        <div class="col-md-3 d-flex gap-2">
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
                                <th width="10%">รูปร้าน</th>
                                <th width="15%">ชื่อร้านค้า</th>
                                <th width="15%">ประเภทร้าน</th>
                                <th width="15%">เจ้าของร้าน</th>
                                <th width="10%">สถานะ</th>
                                <th width="15%">พิกัด (Lat, Lng)</th>
                                <th width="15%">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if($shops && count($shops) > 0){ ?>
                            <?php $i=1; foreach($shops as $shop){ 
                                $isOpen = ($shop['is_open'] == 1);
                                $badgeClass = $isOpen ? 'bg-success' : 'bg-danger';
                                $badgeText = $isOpen ? 'เปิดบริการ' : 'ปิดร้าน';
                            ?>
                                <tr class="shop-row" data-name="<?php echo htmlspecialchars(mb_strtolower($shop['shop_name'], 'UTF-8')); ?>" data-status="<?php echo $shop['is_open']; ?>" data-type="<?php echo htmlspecialchars($shop['shop_type'] ?? ''); ?>">
                                    <td><span class="text-muted"><?php echo $i++; ?></span></td>

                                    <td>
                                        <?php if(!empty($shop['shop_image'])){ ?>
                                            <img src="../uploads/<?php echo htmlspecialchars($shop['shop_image']); ?>" class="shadow-sm" style="width: 50px; height: 50px; object-fit:cover; border-radius:50%;">
                                        <?php } else { ?>
                                            <div class="bg-light text-muted d-flex align-items-center justify-content-center shadow-sm mx-auto" style="width: 50px; height: 50px; border-radius:50%; font-size: 0.7rem;">
                                                ไม่มีรูป
                                            </div>
                                        <?php } ?>
                                    </td>

                                    <td class="text-start fw-bold text-dark">
                                        <?php echo htmlspecialchars($shop['shop_name']); ?>
                                    </td>

                                    <td>
                                        <span class="badge bg-secondary px-2 py-1"><?php echo htmlspecialchars($shop['shop_type'] ?: 'ไม่ระบุ'); ?></span>
                                    </td>

                                    <td>
                                        <div class="d-flex align-items-center justify-content-center gap-2">
                                            <i class="bi bi-person-circle text-muted"></i>
                                            <?php echo htmlspecialchars($shop['first_name'].' '.$shop['last_name']); ?>
                                        </div>
                                    </td>

                                    <td>
                                        <span class="badge <?php echo $badgeClass; ?> px-3 py-2 rounded-pill shadow-sm">
                                            <?php echo $badgeText; ?>
                                        </span>
                                    </td>

                                    <td>
                                        <span class="text-muted small" title="ละติจูด, ลองจิจูด">
                                            <i class="bi bi-geo-alt-fill text-danger"></i> 
                                            <?php echo htmlspecialchars($shop['latitude']).',<br>'.htmlspecialchars($shop['longitude']); ?>
                                        </span>
                                    </td>

                                    <td>
                                        <div class="btn-group shadow-sm" role="group">
                                            <a href="edit_shop.php?id=<?php echo (int)$shop['shop_id']; ?>" class="btn btn-sm btn-outline-warning text-dark" title="แก้ไขร้านค้า">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                            
                                            <a href="delete_shop.php?id=<?php echo $shop['shop_id']; ?>" class="btn btn-sm btn-outline-danger" title="ลบร้านค้า" onclick="confirmDeleteShop(event, this.href, this)">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php } ?>
                        <?php } else { ?>
                            <tr id="noDataRow">
                                <td colspan="7" class="text-center py-5">
                                    <i class="bi bi-shop text-muted opacity-25" style="font-size: 3rem;"></i>
                                    <p class="text-muted mt-3 mb-0">ไม่พบข้อมูลร้านค้าในระบบ</p>
                                </td>
                            </tr>
                        <?php } ?>
                        <tr id="jsNoDataRow" style="display: none;">
                            <td colspan="7" class="text-center py-5">
                                <i class="bi bi-shop text-muted opacity-25" style="font-size: 3rem;"></i>
                                <p class="text-muted mt-3 mb-0">ไม่พบข้อมูลร้านค้าที่ค้นหา</p>
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
    let statusFilter = document.getElementById('statusFilter').value;
    let typeFilter = document.getElementById('typeFilter').value;
    
    sessionStorage.setItem('admin_shop_keyword', keyword);
    sessionStorage.setItem('admin_shop_status', statusFilter);
    sessionStorage.setItem('admin_shop_type', typeFilter);
    
    let rows = document.querySelectorAll('.shop-row');
    let visibleCount = 0;
    
    rows.forEach(row => {
        let name = row.getAttribute('data-name');
        let status = row.getAttribute('data-status');
        let type = row.getAttribute('data-type');
        
        let matchName = name.includes(keyword);
        let matchStatus = (statusFilter === "") || (status === statusFilter);
        let matchType = (typeFilter === "") || (type === typeFilter);
        
        if (matchName && matchStatus && matchType) {
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
    document.getElementById('statusFilter').value = '';
    document.getElementById('typeFilter').value = '';
    sessionStorage.removeItem('admin_shop_keyword');
    sessionStorage.removeItem('admin_shop_status');
    sessionStorage.removeItem('admin_shop_type');
    filterTable();
}

document.addEventListener('DOMContentLoaded', function() {
    let savedKeyword = sessionStorage.getItem('admin_shop_keyword');
    let savedStatus = sessionStorage.getItem('admin_shop_status');
    let savedType = sessionStorage.getItem('admin_shop_type');
    
    if(savedKeyword) {
        document.getElementById('searchInput').value = savedKeyword;
    }
    if(savedStatus) {
        document.getElementById('statusFilter').value = savedStatus;
    }
    if(savedType) {
        document.getElementById('typeFilter').value = savedType;
    }
    
    if(savedKeyword || savedStatus || savedType) {
        filterTable();
    }
});

// สคริปต์สำหรับ Alert การลบข้อมูล (SweetAlert2)
function confirmDeleteShop(e, url, el) {
    e.preventDefault();
    Swal.fire({
        title: 'ยืนยันการลบร้านค้า?',
        text: "ข้อมูลร้านค้าและสินค้าทั้งหมดในร้านนี้จะถูกลบออกจากระบบ!",
        icon: 'error',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'ใช่, ลบร้านค้านี้',
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
                        text: 'ร้านค้านี้ถูกลบออกจากระบบแล้ว',
                        icon: 'success',
                        timer: 1500,
                        showConfirmButton: false
                    });
                } else {
                    Swal.fire('ผิดพลาด', 'ไม่สามารถลบร้านค้าได้', 'error');
                }
            }).catch(error => {
                Swal.fire('ผิดพลาด', 'ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้', 'error');
            });
        }
    })
}
</script>

<?php require_once("../layout/footer.php"); ?>
