<?php 
require_once("../config/connect.php");
require_once("../layout/header.php");

$userid = $_SESSION['userid'];

// ===== ส่วนการเพิ่มสินค้า (Modal) =====
if(isset($_POST["submit_product"])){
    $shop_id = $_POST["shop_id"];
    $product_name = $_POST["product_name"];
    $product_detail = $_POST["product_detail"]; 
    $product_type = $_POST["product_type"];
    $price = $_POST["price"];
    $product_image = "";
    $status = "available";
    
    if(isset($_FILES['product_image']) && $_FILES['product_image']['error'] == 0){
        $ext = pathinfo($_FILES['product_image']['name'], PATHINFO_EXTENSION);
        $new_filename = "product_" . uniqid() . "." . $ext;
        $upload_path = "../uploads/" . $new_filename;

        if(move_uploaded_file($_FILES['product_image']['tmp_name'], $upload_path)){
            $product_image = $new_filename;
        }
    }

    $result = $controller->insert_products(
        $shop_id,
        $product_name,
        $product_detail,
        $product_type,
        $price,
        $product_image,
        $status
    );

    if($result){
        $_SESSION['flash_success'] = "เพิ่มสินค้าเรียบร้อยแล้ว";
    }else{
        $_SESSION['flash_error'] = "เกิดข้อผิดพลาดในการเพิ่มสินค้า";
    }
    header("Location: myshop.php");
    exit;
}
// ===================================

// เช็คว่า User คนนี้มีร้านค้าหรือยัง?
$my_shop = $controller->get_my_shop($userid);

$products = [];
if($my_shop){
    // ถ้ามีร้านแล้ว ให้ไปดึงสินค้าของร้านนี้มาเก็บไว้
    $products = $controller->get_my_products($my_shop['shop_id']);
}
?>

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
</style>

<div class="container my-4">

    <?php if(isset($_SESSION['flash_success'])){ ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'success',
                    title: 'สำเร็จ!',
                    text: '<?php echo $_SESSION['flash_success']; ?>',
                    timer: 2500,
                    showConfirmButton: false
                });
            });
        </script>
    <?php unset($_SESSION['flash_success']); } ?>

    <?php if(isset($_SESSION['flash_error'])){ ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'error',
                    title: 'ผิดพลาด',
                    text: '<?php echo $_SESSION['flash_error']; ?>',
                    confirmButtonColor: '#d33'
                });
            });
        </script>
    <?php unset($_SESSION['flash_error']); } ?>


    <?php if(!$my_shop) { ?>
        <div class="row justify-content-center">
            <div class="col-md-8 text-center py-5">
                <div class="card shadow border-0" style="border-radius: 20px;">
                    <div class="card-body p-5">
                        <i class="bi bi-shop text-warning" style="font-size: 5rem;"></i>
                        <h3 class="fw-bold mt-3">คุณยังไม่ได้สร้างร้านค้า</h3>
                        <p class="text-muted">เริ่มธุรกิจและเพิ่มยอดขายของคุณกับเราได้เลยวันนี้</p>
                        <a href="add_myshop.php" class="btn btn-warning text-white fw-bold btn-lg mt-3 shadow-sm px-5">
                            <i class="bi bi-plus-lg"></i> สร้างร้านค้าใหม่
                        </a>
                    </div>
                </div>
            </div>
        </div>

    <?php } else { ?>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card shadow-sm border-0 shop-info-card sticky-top" style="top: 20px; z-index: 1;">
                    <div class="card-header bg-warning text-white text-center py-3 border-0">
                        <h5 class="mb-0 fw-bold"><i class="bi bi-shop-window"></i> ข้อมูลร้านค้าของฉัน</h5>
                    </div>
                    
                    <div class="position-relative">
                        <?php if(!empty($my_shop['shop_image'])){ ?>
                            <img src="../uploads/<?php echo htmlspecialchars($my_shop['shop_image']); ?>" class="w-100" style="height: 220px; object-fit: cover;">
                        <?php } else { ?>
                            <div class="bg-light text-muted d-flex justify-content-center align-items-center" style="height: 220px;">
                                <span><i class="bi bi-image fs-1"></i></span>
                            </div>
                        <?php } ?>
                        
                        <div class="position-absolute top-0 end-0 m-2">
                            <?php if($my_shop['is_open'] == 1){ ?>
                                <span class="badge bg-success shadow px-3 py-2">เปิดให้บริการ</span>
                            <?php } else { ?>
                                <span class="badge bg-danger shadow px-3 py-2">ปิดชั่วคราว</span>
                            <?php } ?>
                        </div>
                    </div>

                    <div class="card-body p-4">
                        <h4 class="card-title text-center fw-bold mb-3"><?php echo htmlspecialchars($my_shop['shop_name']); ?></h4>
                        
                        <div class="mb-3">
                            <label class="text-muted small fw-bold">รายละเอียดร้าน:</label>
                            <p class="card-text mb-0"><?php echo htmlspecialchars($my_shop['description']); ?></p>
                        </div>

                        <div class="bg-light p-3 rounded-3 mb-4 small">
                            <div class="d-flex justify-content-between mb-2">
                                <span><i class="bi bi-geo-alt-fill text-danger"></i> ละติจูด:</span>
                                <span class="text-muted"><?php echo htmlspecialchars($my_shop['latitude']); ?></span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span><i class="bi bi-geo-alt-fill text-danger"></i> ลองจิจูด:</span>
                                <span class="text-muted"><?php echo htmlspecialchars($my_shop['longitude']); ?></span>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <a href="edit_myshop.php?id=<?php echo $my_shop['shop_id']; ?>" class="btn btn-outline-warning text-dark fw-bold">
                                <i class="bi bi-pencil-square"></i> แก้ไขข้อมูลร้าน
                            </a>

                            <?php if($my_shop['is_open'] == 1){ ?>
                                <a href="toggle_shop_status.php?status=0" class="btn btn-danger fw-bold" onclick="confirmAction(event, this.href, 'ปิดร้านค้า', 'คุณต้องการ ปิดร้าน ชั่วคราวใช่หรือไม่?', 'ใช่, ปิดร้าน', '#dc3545')">
                                    <i class="bi bi-door-closed"></i> ปิดร้านค้า
                                </a>
                            <?php } else { ?>
                                <a href="toggle_shop_status.php?status=1" class="btn btn-success fw-bold" onclick="confirmAction(event, this.href, 'เปิดร้านค้า', 'คุณต้องการ เปิดร้าน เพื่อเริ่มรับลูกค้าใช่หรือไม่?', 'ใช่, เปิดร้าน', '#198754')">
                                    <i class="bi bi-door-open"></i> เปิดร้านค้า
                                </a>
                            <?php } ?>

                            <a href="chat.php" class="btn btn-outline-primary fw-bold mt-2">
                                <i class="bi bi-chat-dots-fill"></i> แชทติดต่อแอดมิน
                            </a>

                            <a href="reports.php" class="btn btn-outline-danger mt-2">
                                <i class="bi bi-flag"></i> ดูรายงานที่ลูกค้าแจ้ง
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card shadow-sm border-0" style="border-radius: 15px;">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3 border-0">
                        <h5 class="mb-0 fw-bold text-dark">
                            <i class="bi bi-box-seam text-warning"></i> สินค้าในร้าน (<?php echo count($products); ?>)
                        </h5>
                        <button type="button" class="btn btn-warning text-white btn-sm shadow-sm fw-bold px-3" data-bs-toggle="modal" data-bs-target="#addProductModal">
                            <i class="bi bi-plus-lg"></i> เพิ่มสินค้า
                        </button>
                    </div>

                    <div class="card-body bg-light p-4" style="border-bottom-left-radius: 15px; border-bottom-right-radius: 15px;">
                        <div class="row g-3">

                            <?php if(count($products) > 0) { ?>
                                <?php foreach($products as $prod) { 
                                    // จัดการสถานะ
                                    $status = strtolower(trim($prod['status'] ?? 'available'));
                                    $isAvailable = ($status === 'available');
                                    $toggleTo = $isAvailable ? 'unavailable' : 'available';

                                    $badgeText  = $isAvailable ? 'พร้อมขาย' : 'สินค้าหมด';
                                    $badgeClass = $isAvailable ? 'bg-success' : 'bg-secondary';

                                    $actionText  = $isAvailable ? 'ตั้งเป็นสินค้าหมด' : 'ตั้งเป็นพร้อมขาย';
                                    $actionClass = $isAvailable ? 'btn-outline-secondary' : 'btn-outline-success';
                                    $confirmColor = $isAvailable ? '#6c757d' : '#198754';
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
                                                <div class="d-grid gap-2">
                                                    <a href="toggle_product_status.php?id=<?php echo (int)$prod['product_id']; ?>&status=<?php echo $toggleTo; ?>" 
                                                       class="btn btn-sm <?php echo $actionClass; ?>" 
                                                       onclick="confirmAction(event, this.href, 'ยืนยันการเปลี่ยนสถานะ', 'เปลี่ยนเป็น: <?php echo $actionText; ?> หรือไม่?', 'ยืนยัน', '<?php echo $confirmColor; ?>')">
                                                       <i class="bi bi-arrow-repeat"></i> <?php echo $actionText; ?>
                                                    </a>
                                                    
                                                    <div class="btn-group w-100" role="group">
                                                        <a href="edit_product.php?id=<?php echo (int)$prod['product_id']; ?>" class="btn btn-sm btn-light border text-dark">
                                                            <i class="bi bi-pencil"></i> แก้ไข
                                                        </a>
                                                        <a href="delete_product.php?id=<?php echo (int)$prod['product_id']; ?>" class="btn btn-sm btn-outline-danger" 
                                                           onclick="confirmDelete(event, this.href)">
                                                            <i class="bi bi-trash"></i> ลบ
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                    </div>

                                <?php } ?>
                            <?php } else { ?>
                                <div class="col-12 text-center py-5">
                                    <i class="bi bi-box-seam display-4 text-muted opacity-50"></i>
                                    <h5 class="mt-3 text-muted">ยังไม่มีสินค้าในร้าน</h5>
                                    <p class="text-muted small">กดปุ่ม "เพิ่มสินค้า" ด้านบนเพื่อเริ่มลงขายได้เลย</p>
                                </div>
                            <?php } ?>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php } ?>
</div>

<script>
// ฟังก์ชันสำหรับ Alert ทั่วไป (เช่น การเปลี่ยนสถานะ)
function confirmAction(e, url, title, text, confirmBtnText, confirmColor) {
    e.preventDefault(); // หยุดการลิ้งก์ไปหน้าอื่นทันที
    Swal.fire({
        title: title,
        text: text,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: confirmColor || '#ffc107',
        cancelButtonColor: '#adb5bd',
        confirmButtonText: confirmBtnText,
        cancelButtonText: 'ยกเลิก',
        reverseButtons: true // สลับให้ปุ่มยกเลิกมาอยู่ซ้ายมือ (ดีต่อ UX)
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = url; // ถ้ายืนยันค่อยไปที่ลิงก์นั้น
        }
    })
}

// ฟังก์ชันเฉพาะสำหรับการแจ้งเตือนตอน "ลบ" (ดุดันกว่าเดิม)
function confirmDelete(e, url) {
    e.preventDefault();
    Swal.fire({
        title: 'คุณแน่ใจหรือไม่?',
        text: "ข้อมูลสินค้านี้จะถูกลบและไม่สามารถกู้คืนได้!",
        icon: 'error',
        showCancelButton: true,
        confirmButtonColor: '#dc3545', // สีแดง
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

<!-- Modal เพิ่มสินค้า -->
<div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius: 15px;">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-bold" id="addProductModalLabel"><i class="bi bi-box-seam text-warning"></i> เพิ่มสินค้า</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4 pt-3">
        <form action="" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="shop_id" value="<?php echo htmlspecialchars($my_shop['shop_id']); ?>">

            <div class="mb-4 text-center">
                <img id="preview" src="" class="preview-img mb-3 shadow-sm mx-auto" alt="Image Preview" style="display: none; width: 150px; height: 150px; object-fit: cover; border-radius: 10px;">
                <input type="file" name="product_image" id="product_image" class="form-control" accept="image/*" onchange="previewImage(this)" required>
                <small class="text-muted mt-2 d-block">รองรับไฟล์ .jpg, .png</small>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">ชื่อสินค้า <span class="text-danger">*</span></label>
                <input type="text" name="product_name" class="form-control" placeholder="เช่น ทุเรียนหมอนทอง, ข้าวกล่อง" required>
            </div>
            
            <div class="mb-3">
                <label class="form-label fw-bold">รายละเอียดสินค้า <span class="text-danger">*</span></label>
                <textarea name="product_detail" class="form-control" rows="2" placeholder="เช่น น้ำหนัก, ขนาด, รสชาติ"></textarea>
            </div>
            
            <div class="mb-3">
                <label class="form-label fw-bold">ประเภทสินค้า <span class="text-danger">*</span></label>
                <select name="product_type" class="form-select" required>
                    <option value="" disabled selected>-- เลือกประเภทสินค้า --</option>
                    <option value="อาหาร">อาหาร</option>
                    <option value="ผลไม้">ผลไม้</option>
                    <option value="เครื่องดื่ม">เครื่องดื่ม</option>
                    <option value="ขนม/ของว่าง">ขนม/ของว่าง</option>
                    <option value="ของใช้">ของใช้</option>
                    <option value="อื่นๆ">อื่นๆ</option>
                </select>
            </div>

            <div class="mb-4">
                <label class="form-label fw-bold">ราคา (บาท) <span class="text-danger">*</span></label>
                <input type="number" step="0.01" name="price" class="form-control" placeholder="0.00" required>
            </div>

            <div class="d-grid">
                <button type="submit" name="submit_product" class="btn btn-warning text-white fw-bold py-2" style="border-radius: 8px;">
                    ยืนยันการเพิ่มสินค้า
                </button>
            </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
function previewImage(input){
    const preview = document.getElementById('preview');
    if(input.files && input.files[0]){
        const reader = new FileReader();
        reader.onload = e => {
            preview.src = e.target.result;
            preview.style.display = 'block'; 
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

<?php require_once("../layout/footer.php"); ?>
