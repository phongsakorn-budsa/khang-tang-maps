<?php
require_once("config/connect.php");

$id = $_GET['id'] ?? 0;

// ข้อมูลร้าน
$shop = $controller->get_shop_with_owner($id);

// สินค้าของร้าน
$products = $controller->get_my_products($id);
usort($products, function($a, $b) {
    $statusA = strtolower(trim($a['status'] ?? ''));
    $statusB = strtolower(trim($b['status'] ?? ''));
    
    // ถ้า A คือ available แต่ B ไม่ใช่ ให้ A ขึ้นก่อน (-1)
    if ($statusA === 'available' && $statusB !== 'available') return -1;
    // ถ้า B คือ available แต่ A ไม่ใช่ ให้ B ขึ้นก่อน (1)
    if ($statusA !== 'available' && $statusB === 'available') return 1;
    return 0;
});
?>

<style>
    /* ส่วนหัวร้านค้า */
    .shop-image-header {
        height: 250px;
        object-fit: cover;
        width: 100%;
        border-radius: 15px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }

    /* กรอบการ์ดสินค้า */
    .product-card {
        border: 1px solid #f0f0f0;
        transition: transform 0.2s, box-shadow 0.2s;
        border-radius: 15px;
        overflow: hidden;
        background-color: #fff;
        box-shadow: 0 4px 12px rgba(0,0,0,0.06) !important; /* เพิ่มเงาให้ดูมีมิติ ไม่แบน */
    }
    .product-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.12) !important;
    }

    /* คอนเทนเนอร์รูปภาพ บังคับสัดส่วน 1:1 (จัตุรัส) */
    .product-img-container {
        width: 100%;
        aspect-ratio: 1 / 1; 
        position: relative;
        overflow: hidden;
        background-color: #f8f9fa;
    }
    .product-img-container img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    /* ป้ายสถานะ พร้อมขาย/สินค้าหมด */
    .badge-status {
        position: absolute;
        top: 8px;
        left: 8px; /* ย้ายมาซ้ายบนตามรูปตัวอย่าง */
        font-size: 0.75rem;
        padding: 5px 12px;
        border-radius: 8px;
        z-index: 2;
        font-weight: bold;
    }

    /* รายละเอียดสินค้า */
    .product-detail {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        font-size: 0.8rem;
        color: #6c757d;
        margin-bottom: 8px;
    }

    /* ป้ายราคา */
    .price-tag {
        font-size: 1rem; /* ปรับขนาดให้เล็กลงนิดหน่อยสำหรับมือถือ */
        color: #198754; 
        font-weight: 800;
        white-space: nowrap; /* สำคัญมาก: บังคับไม่ให้ตัวเลขตกบรรทัด */
    }

    /* เพิ่มคลาสใหม่สำหรับชื่อสินค้า */
    .product-title {
        font-size: 0.9rem; /* ปรับชื่อสินค้าให้เล็กลง เพื่อให้แสดงผลได้ยาวขึ้น */
    }

    /* เอฟเฟกต์รูปสินค้าหมด */
    .out-of-stock {
        filter: grayscale(100%);
        opacity: 0.5;
    }
</style>

<?php if($shop){ ?>
<div class="shop-panel-content d-flex flex-column container py-4" style="min-height: 100vh;">

    <div class="position-relative mb-4">
        <img src="uploads/<?php echo $shop['shop_image'] ?: 'no-image.png'; ?>" class="shop-image-header">
    </div>

    <div class="shop-info px-2">
        <h2 class="fw-bold mb-1"><?php echo htmlspecialchars($shop['shop_name']); ?></h2>
        <p class="text-muted mb-3"><?php echo htmlspecialchars($shop['description']); ?></p>
        
        <div class="row g-2 mb-3">
            <div class="col-auto">
                <span class="badge bg-light text-dark border p-2">
                    <i class="bi bi-person-fill text-primary"></i> <?php echo htmlspecialchars($shop['first_name'].' '.$shop['last_name']); ?>
                </span>
            </div>
            <div class="col-auto">
                <a href="tel:<?php echo htmlspecialchars($shop['phone_number']); ?>" class="badge bg-light text-dark border p-2 text-decoration-none">
                    <i class="bi bi-telephone-fill text-success"></i> <?php echo htmlspecialchars($shop['phone_number']); ?>
                </a>
            </div>
        </div>

        <?php
        $lat = (float)($shop['latitude'] ?? 0);
        $lng = (float)($shop['longitude'] ?? 0);
        ?>
        <div class="d-grid mb-4">
            <a class="btn btn-warning text-white fw-bold shadow-sm"
               href="https://www.google.com/maps/search/?api=1&query=<?php echo $lat; ?>,<?php echo $lng; ?>"
               target="_blank" rel="noopener">
                <i class="bi bi-geo-alt-fill"></i> นำทางไปยังร้านค้า
            </a>
        </div>
    </div>

    <hr class="my-3 opacity-25">

    <h5 class="fw-bold mb-3"><i class="bi bi-bag-check-fill text-danger"></i> เมนู/สินค้าของเรา</h5>

    <?php if(count($products) > 0){ ?>
    <div class="row g-3">
        <?php foreach($products as $p){ 
            $status = $p['status'] ?? 'available';
            $isAvailable = (strtolower(trim($status)) === 'available');
        ?>
        <div class="col-6 col-md-6 col-lg-6">
            <div class="card h-100 product-card d-flex flex-column">
                
                <div class="product-img-container">
                    <span class="badge badge-status <?php echo $isAvailable ? 'bg-success' : 'bg-secondary'; ?>">
                        <?php echo $isAvailable ? 'พร้อมขาย' : 'สินค้าหมด'; ?>
                    </span>

                    <?php if(!empty($p['product_image'])){ ?>
                        <img src="uploads/<?php echo htmlspecialchars($p['product_image']); ?>"
                             class="<?php echo !$isAvailable ? 'out-of-stock' : ''; ?>">
                    <?php } else { ?>
                        <div class="w-100 h-100 d-flex align-items-center justify-content-center text-muted">
                            <i class="bi bi-image" style="font-size: 2rem;"></i>
                        </div>
                    <?php } ?>
                </div>

                <div class="card-body p-2 d-flex flex-column">
                    <h6 class="card-title product-title fw-bold mb-1 text-truncate <?php echo !$isAvailable ? 'text-muted' : ''; ?>">
                        <?php echo htmlspecialchars($p['product_name']); ?>
                    </h6>
                    
                    <div class="product-detail <?php echo !$isAvailable ? 'text-muted' : ''; ?>">
                        <?php echo !empty($p['product_detail']) ? htmlspecialchars($p['product_detail']) : '&nbsp;'; ?>
                    </div>

                    <div class="mt-auto pt-2">
                        <span class="price-tag <?php echo !$isAvailable ? 'text-muted' : ''; ?>">
                            ฿<?php echo number_format((float)$p['price']); ?>
                        </span>
                    </div>
                </div>

            </div>
        </div>
        <?php } ?>
    </div>
    <?php } else { ?>
        <div class="text-center py-5">
            <i class="bi bi-box-seam text-muted" style="font-size: 3rem;"></i>
            <p class="text-muted mt-2">ยังไม่มีสินค้าในขณะนี้</p>
        </div>
    <?php } ?>

    <div class="mt-5 text-center pb-4">
        <a href="report_shop.php?id=<?php echo (int)$shop['shop_id']; ?>" class="text-danger text-decoration-none small">
            <i class="bi bi-exclamation-triangle"></i> รายงานร้านค้าไม่เหมาะสม
        </a>
    </div>

</div>
<?php } else { ?>
    <div class="container py-5 text-center">
        <p class="text-danger">ไม่พบข้อมูลร้านค้า</p>
    </div>
<?php } ?>