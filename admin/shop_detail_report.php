<?php
require_once("../config/connect.php");
require_once("../layout/header.php");
require_once("../layout/check_admin.php");

// รับ shop_id
$shop_id = $_GET['id'] ?? 0;
$shop_id = (int)$shop_id;

// ดึงข้อมูลร้าน
$shop = $controller->get_shop_with_owner($shop_id);

if(!$shop){
    echo "<div class='container my-5'><div class='alert alert-danger text-center'>ไม่พบร้านค้า</div></div>";
    require_once("../layout/footer.php");
    exit;
}

// ดึงสินค้าของร้าน
$products = $controller->get_my_products($shop_id);

// ==========================================
// ระบบแบ่งหน้า (Pagination)
// ==========================================
$items_per_page = 6;
$total_products = count($products);
$total_pages = ceil($total_products / $items_per_page);

$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if($current_page < 1) $current_page = 1;
if($current_page > $total_pages && $total_pages > 0) $current_page = $total_pages;

$offset = ($current_page - 1) * $items_per_page;
$paginated_products = array_slice($products, $offset, $items_per_page);
// ==========================================
?>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    /* ตกแต่ง Card ให้มีมิติเวลาเอาเมาส์ชี้ */
    .product-card {
        transition: transform 0.2s, box-shadow 0.2s;
        border-radius: 12px;
    }
    .product-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.1) !important;
    }
    .shop-info-card {
        border-radius: 15px;
        overflow: hidden;
    }
    #shopMap{
        height: 250px;
        width: 100%;
        border-radius: 10px;
        border: 1px solid #ddd;
    }
    /* ปรับภาพขาวดำสำหรับสินค้าหมด */
    .grayscale {
        filter: grayscale(100%);
    }
    /* ปุ่ม Pagination */
    .page-link {
        color: #ffc107;
        font-weight: 500;
    }
    .page-link:hover {
        color: #e0a800;
    }
    .page-item.active .page-link {
        background-color: #ffc107;
        border-color: #ffc107;
        color: white;
    }
</style>

<div class="container my-4">

    <div class="mb-3">
        <a href="report.php" class="text-decoration-none text-muted fw-bold">
            <i class="bi bi-arrow-left"></i> กลับไปหน้าจัดการร้านค้า
        </a>
    </div>

    <div class="row g-4">
        
        <div class="col-md-4">
            <div class="card shadow-sm border-0 shop-info-card sticky-top" style="top: 20px; z-index: 1;">
                <div class="card-header bg-dark text-white text-center py-3 border-0">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-shop-window text-warning"></i> ข้อมูลร้านค้า (มุมมอง Admin)</h5>
                </div>
                
                <div class="position-relative">
                    <?php if(!empty($shop['shop_image'])){ ?>
                        <img src="../uploads/<?php echo htmlspecialchars($shop['shop_image']); ?>" class="w-100" style="height: 220px; object-fit: cover;">
                    <?php } else { ?>
                        <div class="bg-light text-muted d-flex justify-content-center align-items-center" style="height: 220px;">
                            <span><i class="bi bi-image fs-1"></i></span>
                        </div>
                    <?php } ?>
                    
                    <div class="position-absolute top-0 end-0 m-2">
                        <?php if($shop['is_open'] == 1){ ?>
                            <span class="badge bg-success shadow px-3 py-2">เปิดให้บริการ</span>
                        <?php } else { ?>
                            <span class="badge bg-danger shadow px-3 py-2">ปิดชั่วคราว</span>
                        <?php } ?>
                    </div>
                </div>

                <div class="card-body p-4">
                    <h4 class="card-title text-center fw-bold mb-3"><?php echo htmlspecialchars($shop['shop_name']); ?></h4>
                    
                    <div class="mb-3 text-center small">
                        <i class="bi bi-person-circle text-muted"></i> เจ้าของร้าน: 
                        <span class="fw-bold"><?php echo htmlspecialchars($shop['first_name'].' '.$shop['last_name']); ?></span><br>
                        <i class="bi bi-telephone-fill text-success"></i> เบอร์ติดต่อ: 
                        <span class="fw-bold"><?php echo htmlspecialchars($shop['phone_number']); ?></span>
                    </div>

                    <div class="mb-3">
                        <label class="text-muted small fw-bold">รายละเอียดร้าน:</label>
                        <p class="card-text mb-0"><?php echo htmlspecialchars($shop['description']); ?></p>
                    </div>

                    <div class="bg-light p-3 rounded-3 mb-4 small">
                        <div class="d-flex justify-content-between mb-2">
                            <span><i class="bi bi-geo-alt-fill text-danger"></i> ละติจูด:</span>
                            <span class="text-muted"><?php echo htmlspecialchars($shop['latitude']); ?></span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span><i class="bi bi-geo-alt-fill text-danger"></i> ลองจิจูด:</span>
                            <span class="text-muted"><?php echo htmlspecialchars($shop['longitude']); ?></span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div id="shopMap"></div>
                    </div>

                    <div class="d-grid gap-2">
                        <a href="edit_shop.php?id=<?php echo $shop['shop_id']; ?>" class="btn btn-outline-warning text-dark fw-bold">
                            <i class="bi bi-pencil-square"></i> แก้ไขข้อมูลร้านนี้
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-3 border-0">
                    <h5 class="mb-0 fw-bold text-dark">
                        <i class="bi bi-box-seam text-warning"></i> สินค้าในร้าน (<?php echo $total_products; ?>)
                    </h5>
                    <a href="add_product.php?shop_id=<?php echo $shop['shop_id']; ?>" class="btn btn-dark text-white btn-sm shadow-sm fw-bold px-3">
                        <i class="bi bi-plus-lg text-warning"></i> แอดมินเพิ่มสินค้า
                    </a>
                </div>

                <div class="card-body bg-light p-4" style="border-bottom-left-radius: 15px; border-bottom-right-radius: 15px;">
                    <div class="row g-3">

                        <?php if(count($paginated_products) > 0) { ?>
                            <?php foreach($paginated_products as $prod) { 
                                $status = strtolower(trim($prod['status'] ?? 'available'));
                                $isAvailable = ($status === 'available');

                                $badgeText  = $isAvailable ? 'พร้อมขาย' : 'สินค้าหมด';
                                $badgeClass = $isAvailable ? 'bg-success' : 'bg-secondary';
                            ?>

                                <div class="col-lg-4 col-md-6">
                                    <div class="card h-100 shadow-sm border-0 product-card d-flex flex-column <?php echo !$isAvailable ? 'opacity-75' : ''; ?>">

                                        <div class="position-relative">
                                            <div class="ratio ratio-1x1 bg-light rounded-top overflow-hidden">
                                                <?php if(!empty($prod['product_image'])){ ?>
                                                    <img src="../uploads/<?php echo htmlspecialchars($prod['product_image']); ?>" class="w-100 h-100 object-fit-cover <?php echo !$isAvailable ? 'grayscale' : ''; ?>">
                                                <?php } else { ?>
                                                    <div class="d-flex justify-content-center align-items-center h-100 text-muted">
                                                        <i class="bi bi-image" style="font-size: 2rem;"></i>
                                                    </div>
                                                <?php } ?>
                                            </div>

                                            <div class="position-absolute top-0 start-0 m-2">
                                                <span class="badge <?php echo $badgeClass; ?> shadow-sm">
                                                    <?php echo $badgeText; ?>
                                                </span>
                                            </div>
                                        </div>

                                        <div class="card-body p-3 d-flex flex-column">
                                            <h6 class="card-title fw-bold text-truncate mb-1 <?php echo !$isAvailable ? 'text-muted' : ''; ?>" title="<?php echo htmlspecialchars($prod['product_name']); ?>">
                                                <?php echo htmlspecialchars($prod['product_name']); ?>
                                            </h6>
                                            
                                            <div class="mb-2" style="flex-grow: 1;">
                                                <?php if(!empty($prod['product_detail'])){ ?>
                                                    <p class="card-text small text-muted mb-0" style="display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden;">
                                                        <?php echo htmlspecialchars($prod['product_detail']); ?>
                                                    </p>
                                                <?php } else { ?>
                                                    <p class="card-text small text-muted fst-italic mb-0">- ไม่ระบุรายละเอียด -</p>
                                                <?php } ?>
                                            </div>

                                            <div class="mt-auto mb-2">
                                                <span class="fs-5 fw-bold <?php echo $isAvailable ? 'text-success' : 'text-muted'; ?>">
                                                    ฿<?php echo number_format((float)$prod['price']); ?>
                                                </span>
                                            </div>
                                        </div>

                                        <div class="card-footer bg-white border-top-0 p-2 pt-0">
                                            <div class="btn-group w-100" role="group">
                                                <a href="edit_product.php?id=<?php echo (int)$prod['product_id']; ?>" class="btn btn-sm btn-light border text-dark">
                                                    <i class="bi bi-pencil"></i> แก้ไข
                                                </a>
                                                <a href="delete_product.php?id=<?php echo (int)$prod['product_id']; ?>" class="btn btn-sm btn-outline-danger" onclick="confirmDelete(event, this.href)">
                                                    <i class="bi bi-trash"></i> ลบ
                                                </a>
                                            </div>
                                        </div>

                                    </div>
                                </div>

                            <?php } ?>
                        <?php } else { ?>
                            <div class="col-12 text-center py-5">
                                <i class="bi bi-box-seam display-4 text-muted opacity-50"></i>
                                <h5 class="mt-3 text-muted">ร้านนี้ยังไม่มีสินค้า</h5>
                            </div>
                        <?php } ?>

                    </div>

                    <?php if($total_pages > 1): ?>
                    <nav aria-label="Page navigation" class="mt-4 pt-3 border-top">
                        <ul class="pagination justify-content-center mb-0">
                            <li class="page-item <?php echo ($current_page <= 1) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?id=<?php echo $shop_id; ?>&page=<?php echo $current_page - 1; ?>">ก่อนหน้า</a>
                            </li>
                            <?php for($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?php echo ($current_page == $i) ? 'active' : ''; ?>">
                                    <a class="page-link" href="?id=<?php echo $shop_id; ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>
                            <li class="page-item <?php echo ($current_page >= $total_pages) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?id=<?php echo $shop_id; ?>&page=<?php echo $current_page + 1; ?>">ถัดไป</a>
                            </li>
                        </ul>
                    </nav>
                    <?php endif; ?>

                </div>
            </div>
        </div>

    </div>
</div>

<script>
// วาดแผนที่ร้าน
const lat = parseFloat("<?php echo $shop['latitude']; ?>");
const lng = parseFloat("<?php echo $shop['longitude']; ?>");

const map = L.map('shopMap').setView([lat, lng], 15);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap'
}).addTo(map);

L.marker([lat, lng]).addTo(map).bindPopup("<?php echo addslashes($shop['shop_name']); ?>").openPopup();
map.whenReady(() => map.invalidateSize());

// ฟังก์ชันลบสินค้า
function confirmDelete(e, url) {
    e.preventDefault();
    Swal.fire({
        title: 'คุณแน่ใจหรือไม่?',
        text: "ข้อมูลสินค้านี้จะถูกลบและไม่สามารถกู้คืนได้!",
        icon: 'error',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#adb5bd',
        confirmButtonText: '<i class="bi bi-trash"></i> ใช่, ลบเลย!',
        cancelButtonText: 'ยกเลิก',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = url;
        }
    })
}
</script>

<?php require_once("../layout/footer.php"); ?>