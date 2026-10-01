<?php
require_once("../config/connect.php");
require_once("../layout/header.php");
require_once("../layout/check_admin.php");

// Fetch all reports for real-time JS filtering
$reports = $controller->get_reports_v2('', '', '');
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
.detail-col {
    max-width: 200px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
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
                    <a href="report.php" class="nav-link nav-link-custom active">
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
                <h3 class="fw-bold mb-0 text-dark">รายงานปัญหาการใช้งาน</h3>
            </div>

            <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
                <div class="card-body p-4">
                    <form onsubmit="return false;" class="row g-3 align-items-end">
                        
                        <div class="col-md-4">
                            <label class="form-label text-muted small fw-bold">คำค้นหา</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                                <input type="text" id="searchInput" oninput="filterTable()" class="form-control border-start-0 bg-light" placeholder="ค้นหาร้าน / ประเภท / โน้ต...">
                            </div>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label text-muted small fw-bold">กรองสถานะร้าน</label>
                            <select id="sellerStatusFilter" onchange="filterTable()" class="form-select bg-light">
                                <option value="">-- ทั้งหมด --</option>
                                <option value="new">🔴 ใหม่</option>
                                <option value="in_progress">🟡 กำลังแก้</option>
                                <option value="fixed">🟢 แก้แล้ว</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label text-muted small fw-bold">กรองสถานะแอดมิน</label>
                            <select id="adminStatusFilter" onchange="filterTable()" class="form-select bg-light">
                                <option value="">-- ทั้งหมด --</option>
                                <option value="pending">⏳ รอดำเนินการ</option>
                                <option value="resolved">✅ ปิดเคสแล้ว</option>
                            </select>
                        </div>

                        <div class="col-md-2 d-flex gap-2">
                            <button type="button" class="btn btn-warning text-white fw-bold w-100 shadow-sm" onclick="filterTable()" title="ค้นหา">
                                <i class="bi bi-search"></i>
                            </button>
                            <button type="button" class="btn btn-light border fw-bold w-100" onclick="resetFilters()" title="ล้างค่า">
                                <i class="bi bi-eraser"></i>
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
                                <th width="15%">ร้านที่ถูกรายงาน</th>
                                <th width="15%">ประเภทปัญหา</th>
                                <th width="15%">รายละเอียด</th>
                                <th width="15%">โน้ตจากร้านค้า</th>
                                <th width="10%">สถานะร้าน</th>
                                <th width="10%">สถานะเคส (Admin)</th>
                                <th width="15%">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if(!empty($reports)){ $i=1; foreach($reports as $r){ 
                            $searchText = strtolower($r['shop_name'].' '.$r['report_type'].' '.$r['detail'].' '.($r['seller_note']??''));
                        ?>
                            <tr class="report-row" data-search="<?php echo htmlspecialchars($searchText); ?>" data-seller="<?php echo htmlspecialchars($r['seller_status']); ?>" data-admin="<?php echo htmlspecialchars($r['admin_status']); ?>">
                                <td class="text-center text-muted"><?php echo $i++; ?></td>
                                
                                <td class="fw-bold text-dark">
                                    <i class="bi bi-shop text-warning"></i> <?php echo htmlspecialchars($r['shop_name']); ?>
                                </td>
                                
                                <td><span class="badge bg-secondary"><?php echo htmlspecialchars($r['report_type']); ?></span></td>
                                
                                <td class="detail-col" title="<?php echo htmlspecialchars($r['detail']); ?>">
                                    <?php echo htmlspecialchars($r['detail']); ?>
                                </td>
                                
                                <td class="detail-col text-muted" title="<?php echo htmlspecialchars($r['seller_note'] ?? '-'); ?>">
                                    <?php echo htmlspecialchars($r['seller_note'] ?? '-'); ?>
                                </td>

                                <td class="text-center">
                                    <?php
                                        $st = $r['seller_status'];
                                        if($st=='new') echo '<span class="badge bg-danger shadow-sm">ใหม่</span>';
                                        elseif($st=='in_progress') echo '<span class="badge bg-warning text-dark shadow-sm">กำลังแก้</span>';
                                        else echo '<span class="badge bg-success shadow-sm">แก้แล้ว</span>';
                                    ?>
                                </td>

                                <td class="text-center">
                                    <?php if($r['admin_status']=='pending'){ ?>
                                        <span class="badge bg-danger shadow-sm px-2 py-1"><i class="bi bi-hourglass-split"></i> รอดำเนินการ</span>
                                    <?php } else { ?>
                                        <span class="badge bg-success shadow-sm px-2 py-1"><i class="bi bi-check-circle"></i> ปิดเคสแล้ว</span>
                                    <?php } ?>
                                </td>

                                <td class="text-center">
                                    <div class="btn-group shadow-sm" role="group">
                                        <a href="shop_detail_report.php?id=<?php echo (int)$r['shop_id']; ?>" class="btn btn-sm btn-outline-info text-dark" title="ไปที่หน้าร้าน">
                                            <i class="bi bi-eye"></i>
                                        </a>

                                        <?php if($r['admin_status']=='pending'){ ?>
                                            <a href="toggle_report_status.php?id=<?php echo (int)$r['report_id']; ?>&status=resolved" 
                                               class="btn btn-sm btn-outline-success" title="ปิดเคส"
                                               onclick="confirmAction(event, this.href, 'ยืนยันการปิดเคส?', 'เคสนี้จะถูกบันทึกว่าแก้ไขเรียบร้อยแล้ว', 'ใช่, ปิดเคส', '#198754')">
                                                <i class="bi bi-check2-square"></i>
                                            </a>
                                        <?php } else { ?>
                                            <a href="toggle_report_status.php?id=<?php echo (int)$r['report_id']; ?>&status=pending" 
                                               class="btn btn-sm btn-outline-warning text-dark" title="ย้อนสถานะกลับ"
                                               onclick="confirmAction(event, this.href, 'ย้อนกลับสถานะ?', 'เปลี่ยนเคสนี้กลับไปเป็น รอดำเนินการ?', 'ยืนยัน', '#ffc107')">
                                                <i class="bi bi-arrow-counterclockwise"></i>
                                            </a>
                                        <?php } ?>
                                        <a href="delete_report.php?id=<?php echo (int)$r['report_id']; ?>" 
                                           class="btn btn-sm btn-outline-danger" title="ลบรายงาน"
                                           onclick="confirmAction(event, this.href, 'ยืนยันการลบ?', 'รายงานนี้จะถูกลบออกจากระบบอย่างถาวร!', 'ใช่, ลบเลย', '#dc3545')">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php } } else { ?>
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <i class="bi bi-inbox text-muted opacity-25" style="font-size: 3rem;"></i>
                                    <p class="text-muted mt-3 mb-0">ยังไม่มีรายงานปัญหาการใช้งาน</p>
                                </td>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
// ฟังก์ชันสำหรับ SweetAlert2
function confirmAction(e, url, title, text, confirmBtnText, confirmColor) {
    e.preventDefault(); 
    Swal.fire({
        title: title,
        text: text,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: confirmColor,
        cancelButtonColor: '#6c757d',
        confirmButtonText: confirmBtnText,
        cancelButtonText: 'ยกเลิก',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = url;
        }
    })
}

function filterTable() {
    const searchInput = document.getElementById('searchInput').value.toLowerCase();
    const sellerFilter = document.getElementById('sellerStatusFilter').value;
    const adminFilter = document.getElementById('adminStatusFilter').value;
    
    sessionStorage.setItem('admin_report_keyword', searchInput);
    sessionStorage.setItem('admin_report_seller', sellerFilter);
    sessionStorage.setItem('admin_report_admin', adminFilter);
    
    const rows = document.querySelectorAll('.report-row');

    rows.forEach(row => {
        const text = row.getAttribute('data-search');
        const sellerStat = row.getAttribute('data-seller');
        const adminStat = row.getAttribute('data-admin');

        const matchSearch = text.includes(searchInput);
        const matchSeller = (sellerFilter === '' || sellerStat === sellerFilter);
        const matchAdmin = (adminFilter === '' || adminStat === adminFilter);

        if(matchSearch && matchSeller && matchAdmin) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

function resetFilters() {
    document.getElementById('searchInput').value = '';
    document.getElementById('sellerStatusFilter').value = '';
    document.getElementById('adminStatusFilter').value = '';
    sessionStorage.removeItem('admin_report_keyword');
    sessionStorage.removeItem('admin_report_seller');
    sessionStorage.removeItem('admin_report_admin');
    filterTable();
}

document.addEventListener('DOMContentLoaded', function() {
    let savedKeyword = sessionStorage.getItem('admin_report_keyword');
    let savedSeller = sessionStorage.getItem('admin_report_seller');
    let savedAdmin = sessionStorage.getItem('admin_report_admin');
    
    if(savedKeyword) {
        document.getElementById('searchInput').value = savedKeyword;
    }
    if(savedSeller) {
        document.getElementById('sellerStatusFilter').value = savedSeller;
    }
    if(savedAdmin) {
        document.getElementById('adminStatusFilter').value = savedAdmin;
    }
    
    if(savedKeyword || savedSeller || savedAdmin) {
        filterTable();
    }
});
</script>

<?php require_once("../layout/footer.php"); ?>
