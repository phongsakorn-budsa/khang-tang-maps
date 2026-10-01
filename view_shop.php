<?php 
require_once("config/connect.php");

// 1. ตรวจสอบว่าถูกเรียกผ่าน Modal หรือไม่ (เช็คจาก URL ที่ส่ง ?modal=true มา)
$isModal = (isset($_GET['modal']) && $_GET['modal'] == 'true');

// 2. ถ้าไม่ได้เปิดใน Modal ค่อยดึงแถบ Navbar (Header) มาแสดง
if (!$isModal) {
    require_once("layout/header.php");
}

// รับค่า shop_id จาก URL
$shop_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($shop_id === 0) {
    if (!$isModal) echo "<script>window.location.href='index.php';</script>";
    exit;
}

// ดึงข้อมูลร้านค้า
$shop = $controller->get_shop_with_owner($shop_id);

if (!$shop) {
    echo "<div class='container my-5 text-center'>
            <i class='bi bi-shop-window display-1 text-muted opacity-50'></i>
            <h3 class='mt-4 fw-bold'>ไม่พบร้านค้านี้</h3>
          </div>";
    if (!$isModal) require_once("layout/footer.php");
    exit;
}

// ดึงสินค้าทั้งหมดของร้านนี้
$products = $controller->get_my_products($shop_id);
?>

<style>
    <?php if (!$isModal): ?>
    body { background-color: #f8f9fa; }
    <?php endif; ?>

    /* --- ส่วนบน (Cover ร้านค้า) --- */
    .shop-cover-wrapper {
        position: relative;
        overflow: hidden;
        margin-bottom: -40px; 
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        <?php echo !$isModal ? 'border-radius: 20px;' : ''; ?> /* ถ้าเป็น Modal ไม่ต้องโค้งเยอะ */
    }
    .shop-cover-img {
        width: 100%;
        height: 300px;
        object-fit: cover;
        filter: brightness(0.85); 
    }
    .shop-header-content {
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        padding: 40px 20px 60px 20px;
        background: linear-gradient(to top, rgba(0,0,0,0.8) 0%, rgba(0,0,0,0) 100%);
        color: white;
    }

    /* --- การ์ดรายละเอียดร้าน --- */
    .shop-info-card {
        background: white;
        border-radius: 20px;
        padding: 25px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        position: relative;
        z-index: 10;
        border: none;
    }

    /* --- การ์ดสินค้า --- */
    .product-card {
        border-radius: 15px;
        overflow: hidden;
        border: none;
        box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        transition: transform 0.2s, box-shadow 0.2s;
        background: white;
    }
    .product-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
    }
    .product-img {
        width: 100%;
        height: 180px;
        object-fit: cover;
    }
    .grayscale { filter: grayscale(100%); }
</style>

<div class="<?php echo $isModal ? '' : 'container pb-5'; ?>">

    <?php if (!$isModal): ?>
    <div class="mt-4 mb-3">
        <a href="index.php" class="btn btn-white bg-white shadow-sm fw-bold rounded-pill border-0 px-3 py-2 text-dark">
            <i class="bi bi-arrow-left"></i> กลับไปหน้าแรก
        </a>
    </div>
    <?php endif; ?>

    <div class="row justify-content-center m-0">
        <div class="col-lg-10 p-0 p-md-3">

            <div class="shop-cover-wrapper">
                <?php if(!empty($shop['shop_image'])){ ?>
                    <img src="uploads/<?php echo htmlspecialchars($shop['shop_image']); ?>" class="shop-cover-img" alt="Shop Cover">
                <?php } else { ?>
                    <div class="shop-cover-img bg-secondary d-flex align-items-center justify-content-center">
                        <i class="bi bi-shop display-1 text-white opacity-50"></i>
                    </div>
                <?php } ?>
                
                <div class="shop-header-content">
                    <div class="d-flex justify-content-between align-items-end">
                        <div>
                            <h2 class="fw-bold mb-1 text-white text-shadow"><?php echo htmlspecialchars($shop['shop_name']); ?></h2>
                            <?php if($shop['is_open'] == 1){ ?>
                                <span class="badge bg-success px-3 py-2 rounded-pill fs-6"><i class="bi bi-door-open-fill"></i> เปิดให้บริการ</span>
                            <?php } else { ?>
                                <span class="badge bg-danger px-3 py-2 rounded-pill fs-6"><i class="bi bi-door-closed-fill"></i> ปิดชั่วคราว</span>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="shop-info-card mx-3 mx-md-5 mb-5">
                <div class="row g-3 align-items-center">
                    <div class="col-md-7 border-end border-md-0 border-end-md">
                        <h6 class="text-muted fw-bold mb-2">รายละเอียดร้าน:</h6>
                        <p class="mb-0 text-dark"><?php echo nl2br(htmlspecialchars($shop['description'] ?: 'ไม่มีคำอธิบายร้านค้า')); ?></p>
                    </div>
                    <div class="col-md-5 ps-md-4">
                        <div class="mb-2">
                            <i class="bi bi-person-circle text-muted me-2"></i> 
                            <span class="fw-bold">เจ้าของร้าน:</span> <?php echo htmlspecialchars($shop['first_name'] . ' ' . $shop['last_name']); ?>
                        </div>
                        <div class="mb-2">
                            <i class="bi bi-telephone-fill text-success me-2"></i> 
                            <span class="fw-bold">โทร:</span> <?php echo htmlspecialchars($shop['phone_number']); ?>
                        </div>
                        <div class="d-flex flex-wrap gap-2 mt-2">
                            <a href="https://www.google.com/maps/search/?api=1&query=<?php echo $shop['latitude']; ?>,<?php echo $shop['longitude']; ?>" target="_blank" class="btn btn-warning text-white btn-sm rounded-pill fw-bold shadow-sm flex-grow-1 text-nowrap">
                                <i class="bi bi-geo-alt-fill"></i> นำทาง
                            </a>
                            <button type="button" class="btn btn-info text-white btn-sm rounded-pill fw-bold shadow-sm px-3 text-nowrap" data-bs-toggle="modal" data-bs-target="#mapModal" title="แสดงบนแผนที่">
                                <i class="bi bi-map-fill"></i> แผนที่
                            </button>
                            <button onclick="shareShop()" class="btn btn-outline-primary btn-sm rounded-pill fw-bold shadow-sm px-3 text-nowrap" title="แชร์ร้านค้านี้">
                                <i class="bi bi-share-fill"></i> แชร์
                            </button>
                            <a href="report_shop.php?id=<?php echo $shop_id; ?>" class="btn btn-outline-danger btn-sm rounded-pill fw-bold shadow-sm px-3 d-flex align-items-center justify-content-center" title="รายงานร้านค้านี้">
                                <i class="bi bi-flag-fill"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="px-3 px-md-0">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="fw-bold mb-0"><i class="bi bi-bag-heart-fill text-warning"></i> เมนู/สินค้าของร้าน</h5>
                    <span class="badge bg-light text-dark border px-3 py-2 rounded-pill shadow-sm">ทั้งหมด <?php echo count($products); ?> รายการ</span>
                </div>

                <?php if(count($products) > 0) { ?>
                    <div class="row row-cols-2 row-cols-md-3 g-3 g-md-4 mb-4">
                        <?php foreach($products as $prod) { 
                            $status = strtolower(trim($prod['status'] ?? 'available'));
                            $isAvailable = ($status === 'available');
                            $badgeText  = $isAvailable ? 'พร้อมขาย' : 'สินค้าหมด';
                            $badgeClass = $isAvailable ? 'bg-success' : 'bg-secondary';
                        ?>
                            <div class="col">
                                <div class="card h-100 product-card <?php echo !$isAvailable ? 'opacity-75' : ''; ?>">
                                    
                                    <div class="position-relative">
                                        <?php if(!empty($prod['product_image'])){ ?>
                                            <img src="uploads/<?php echo htmlspecialchars($prod['product_image']); ?>" class="product-img <?php echo !$isAvailable ? 'grayscale' : ''; ?>" alt="Product">
                                        <?php } else { ?>
                                            <div class="product-img bg-light d-flex justify-content-center align-items-center text-muted">
                                                <i class="bi bi-image" style="font-size: 3rem;"></i>
                                            </div>
                                        <?php } ?>
                                        
                                        <div class="position-absolute top-0 start-0 m-2">
                                            <span class="badge <?php echo $badgeClass; ?> shadow-sm px-2 py-1">
                                                <?php echo $badgeText; ?>
                                            </span>
                                        </div>
                                    </div>

                                    <div class="card-body d-flex flex-column p-3">
                                        <h6 class="fw-bold mb-1 text-truncate text-dark" title="<?php echo htmlspecialchars($prod['product_name']); ?>">
                                            <?php echo htmlspecialchars($prod['product_name']); ?>
                                        </h6>
                                        
                                        <p class="small text-muted mb-2" style="display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; flex-grow: 1;">
                                            <?php echo htmlspecialchars($prod['product_detail'] ?: '-'); ?>
                                        </p>
                                        
                                        <div class="mt-auto pt-2 border-top">
                                            <span class="fs-5 fw-bold <?php echo $isAvailable ? 'text-success' : 'text-muted'; ?>">
                                                ฿<?php echo number_format((float)$prod['price']); ?>
                                            </span>
                                        </div>
                                    </div>
                                    
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                <?php } else { ?>
                    <div class="text-center py-5 bg-white rounded-4 shadow-sm mb-4">
                        <i class="bi bi-basket display-1 text-muted opacity-25"></i>
                        <h5 class="mt-3 fw-bold text-muted">ร้านค้านี้ยังไม่ได้เพิ่มสินค้า</h5>
                    </div>
                <?php } ?>
            </div>

        </div>
    </div>
</div>

<!-- Modal แผนที่ -->
<div class="modal fade" id="mapModal" tabindex="-1" aria-labelledby="mapModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content border-0 shadow" style="border-radius: 20px; overflow: hidden;">
      <div class="modal-header border-0 bg-light">
        <h5 class="modal-title fw-bold" id="mapModalLabel"><i class="bi bi-map text-primary me-2"></i>ตำแหน่งร้าน: <?php echo htmlspecialchars($shop['shop_name']); ?></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-0">
        <div id="shopMap" style="height: 400px; width: 100%;"></div>
      </div>
    </div>
  </div>
</div>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<style>
/* Custom Shop Marker */
.custom-shop-icon {
    background: transparent;
    border: none;
}
.shop-marker-wrapper {
    display: flex;
    flex-direction: column;
    align-items: center;
    transform: translate(-50%, -50%);
    width: 150px;
}
.shop-marker-img-container {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    border: 3px solid #ffc107;
    background-color: #fff;
    box-shadow: 0 4px 8px rgba(0,0,0,0.3);
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 2;
}
.shop-marker-img-container img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.shop-marker-name {
    background: white;
    padding: 3px 12px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: bold;
    color: #333;
    box-shadow: 0 2px 6px rgba(0,0,0,0.2);
    text-align: center;
    white-space: nowrap;
    margin-top: -10px;
    z-index: 10;
    max-width: 140px;
    overflow: hidden;
    text-overflow: ellipsis;
}
</style>

<script>
function shareShop() {
    const shopName = "<?php echo addslashes($shop['shop_name']); ?>";
    const shopUrl = window.location.href;
    
    if (navigator.share) {
        navigator.share({
            title: shopName,
            text: 'แวะมาดูร้าน ' + shopName + ' บน Khang Tang Maps สิ!',
            url: shopUrl
        }).catch((error) => console.log('Error sharing', error));
    } else {
        // Fallback
        navigator.clipboard.writeText(shopUrl).then(() => {
            alert('คัดลอกลิงก์ร้านค้าเรียบร้อยแล้ว! สามารถนำไปวางเพื่อแชร์ได้เลย');
        }).catch(err => {
            console.error('Could not copy text: ', err);
            alert('ไม่สามารถคัดลอกลิงก์ได้ กรุณาคัดลอก URL ด้านบนแทน');
        });
    }
}

// Leaflet Map Initialization
let mapInitialized = false;
let shopMap;

document.getElementById('mapModal').addEventListener('shown.bs.modal', function () {
    if (!mapInitialized) {
        shopMap = L.map('shopMap').setView([<?php echo $shop['latitude']; ?>, <?php echo $shop['longitude']; ?>], 16);
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(shopMap);
        
        const shopImageUrl = "<?php echo !empty($shop['shop_image']) ? 'uploads/'.addslashes($shop['shop_image']) : ''; ?>";
        const shopName = "<?php echo addslashes(htmlspecialchars($shop['shop_name'])); ?>";
        
        const markerHtml = `
            <div class="shop-marker-wrapper">
                <div class="shop-marker-img-container">
                    ${shopImageUrl ? `<img src="${shopImageUrl}" alt="Shop">` : `<i class="bi bi-shop fs-3 text-muted"></i>`}
                </div>
                <div class="shop-marker-name">${shopName}</div>
            </div>
        `;
        
        const customIcon = L.divIcon({
            className: 'custom-shop-icon',
            html: markerHtml,
            iconSize: [0, 0], 
            iconAnchor: [0, 0] 
        });
        
        L.marker([<?php echo $shop['latitude']; ?>, <?php echo $shop['longitude']; ?>], {icon: customIcon})
            .addTo(shopMap);
            
        mapInitialized = true;
    } else {
        shopMap.invalidateSize();
    }
});
</script>
