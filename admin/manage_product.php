<?php
require_once("../config/connect.php");
require_once("../layout/header.php");
require_once("../layout/check_admin.php");

// รับค่าค้นหา
$keyword = $_GET['keyword'] ?? '';
$shop_id = $_GET['shop_id'] ?? '';

// ดึงร้านทั้งหมด (ไว้ filter)
$shops = $controller->get_all_shops();

// ดึงสินค้าทั้งหมดเพื่อทำ Realtime Search บน Client
$products = $controller->search_products('', '');
?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
/* =========================================
   Sidebar Styles (เมนูด้านซ้าย แบบเดียวกับ Dashboard)
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
                    <a href="manage_shop.php" class="nav-link nav-link-custom">
                        <i class="bi bi-shop"></i> จัดการร้านค้า
                    </a>
                </li>
                <li class="nav-item">
                    <a href="manage_product.php" class="nav-link nav-link-custom active">
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
                <h3 class="fw-bold mb-0 text-dark">ระบบจัดการสินค้า (Admin)</h3>
            </div>

            <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
                <div class="card-body p-4">
                    <form onsubmit="return false;" class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label text-muted small fw-bold">คำค้นหา (ชื่อสินค้า)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                                <input type="text" id="searchInput" class="form-control border-start-0 bg-light" placeholder="พิมพ์ชื่อสินค้า..." onkeyup="filterTable()">
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label text-muted small fw-bold">กรองตามร้านค้า</label>
                            <select id="shopFilter" class="form-select bg-light" onchange="filterTable()">
                                <option value="">-- แสดงทุกร้านค้า --</option>
                                <?php foreach($shops as $s){ ?>
                                    <option value="<?php echo $s['shop_id']; ?>">
                                        <?php echo htmlspecialchars($s['shop_name']); ?>
                                    </option>
                                <?php } ?>
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
                                <th width="10%">รูปภาพ</th>
                                <th width="20%">ชื่อสินค้า</th>
                                <th width="20%">ร้านค้าเจ้าของ</th>
                                <th width="15%">ราคา (บาท)</th>
                                <th width="15%">สถานะ</th>
                                <th width="15%">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if($products && count($products) > 0){ ?>
                            <?php $i=1; foreach($products as $p){ 
                                // จัดการสถานะให้สวยงาม
                                $isAvailable = ($p["status"] == "available");
                                $badgeClass = $isAvailable ? 'bg-success' : 'bg-secondary';
                                $badgeText = $isAvailable ? '<i class="bi bi-check-circle"></i> พร้อมขาย' : '<i class="bi bi-x-circle"></i> หมด';
                            ?>
                                <tr class="product-row" data-name="<?php echo htmlspecialchars(mb_strtolower($p['product_name'], 'UTF-8')); ?>" data-shop="<?php echo $p['shop_id']; ?>">
                                    <td><span class="text-muted"><?php echo $i++; ?></span></td>

                                    <td>
                                        <?php if(!empty($p['product_image'])){ ?>
                                            <img src="../uploads/<?php echo htmlspecialchars($p['product_image']); ?>" class="shadow-sm" style="width: 50px; height: 50px; object-fit:cover; border-radius:8px;">
                                        <?php } else { ?>
                                            <div class="bg-light text-muted d-flex align-items-center justify-content-center shadow-sm mx-auto" style="width: 50px; height: 50px; border-radius:8px; font-size: 0.7rem;">
                                                ไม่มีรูป
                                            </div>
                                        <?php } ?>
                                    </td>

                                    <td class="text-start fw-bold text-dark">
                                        <?php echo htmlspecialchars($p['product_name']); ?>
                                    </td>

                                    <td>
                                        <span class="badge bg-light text-dark border px-2 py-1">
                                            <i class="bi bi-shop text-warning"></i> <?php echo htmlspecialchars($p['shop_name']); ?>
                                        </span>
                                    </td>

                                    <td class="text-success fw-bold">
                                        ฿<?php echo number_format($p['price'], 2); ?>
                                    </td>

                                    <td>
                                        <span class="badge <?php echo $badgeClass; ?> px-3 py-2 rounded-pill shadow-sm">
                                            <?php echo $badgeText; ?>
                                        </span>
                                    </td>

                                    <td>
                                        <div class="btn-group shadow-sm" role="group">
                                            <a href="edit_product.php?id=<?php echo $p['product_id']; ?>" class="btn btn-sm btn-outline-warning text-dark" title="แก้ไข">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                            <a href="delete_product.php?id=<?php echo $p['product_id']; ?>" class="btn btn-sm btn-outline-danger" title="ลบสินค้า" onclick="confirmDelete(event, this.href, this)">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php } ?>
                        <?php } else { ?>
                            <tr id="noDataRow">
                                <td colspan="7" class="text-center py-5">
                                    <i class="bi bi-box-seam display-4 text-muted opacity-25"></i>
                                    <p class="text-muted mt-3 mb-0">ไม่พบข้อมูลสินค้าในระบบ</p>
                                </td>
                            </tr>
                        <?php } ?>
                        <tr id="jsNoDataRow" style="display: none;">
                            <td colspan="7" class="text-center py-5">
                                <i class="bi bi-box-seam display-4 text-muted opacity-25"></i>
                                <p class="text-muted mt-3 mb-0">ไม่พบข้อมูลสินค้าที่ค้นหา</p>
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
    let shopFilter = document.getElementById('shopFilter').value;
    
    sessionStorage.setItem('admin_product_keyword', keyword);
    sessionStorage.setItem('admin_product_shop', shopFilter);
    
    let rows = document.querySelectorAll('.product-row');
    let visibleCount = 0;
    
    rows.forEach(row => {
        let name = row.getAttribute('data-name');
        let shopId = row.getAttribute('data-shop');
        
        let matchName = name.includes(keyword);
        let matchShop = (shopFilter === "") || (shopId === shopFilter);
        
        if (matchName && matchShop) {
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
    document.getElementById('shopFilter').value = '';
    sessionStorage.removeItem('admin_product_keyword');
    sessionStorage.removeItem('admin_product_shop');
    filterTable();
}

document.addEventListener('DOMContentLoaded', function() {
    let savedKeyword = sessionStorage.getItem('admin_product_keyword');
    let savedShop = sessionStorage.getItem('admin_product_shop');
    
    if(savedKeyword) {
        document.getElementById('searchInput').value = savedKeyword;
    }
    if(savedShop) {
        document.getElementById('shopFilter').value = savedShop;
    }
    
    if(savedKeyword || savedShop) {
        filterTable();
    }
});

// สคริปต์สำหรับ Alert การลบข้อมูล (SweetAlert2)
function confirmDelete(e, url, el) {
    e.preventDefault();
    Swal.fire({
        title: 'ยืนยันการลบสินค้า?',
        text: "ในฐานะแอดมิน คุณแน่ใจหรือไม่ว่าต้องการลบสินค้านี้ออกจากระบบ?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'ใช่, ลบเลย',
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
                        text: 'สินค้านี้ถูกลบออกจากระบบแล้ว',
                        icon: 'success',
                        timer: 1500,
                        showConfirmButton: false
                    });
                } else {
                    Swal.fire('ผิดพลาด', 'ไม่สามารถลบสินค้าได้', 'error');
                }
            }).catch(error => {
                Swal.fire('ผิดพลาด', 'ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้', 'error');
            });
        }
    })
}
</script>

<?php require_once("../layout/footer.php"); ?>
