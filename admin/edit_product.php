<?php 
require_once("../config/connect.php");
require_once("../layout/header.php");
require_once("../layout/check_admin.php"); // ต้องมีเผื่อกันคนที่ไม่ใช่แอดมินเข้ามา

if(isset($_POST["submit"])){
    $product_id = $_POST["product_id"];
    $product_name = $_POST["product_name"];
    $product_detail = $_POST["product_detail"];
    $product_type =$_POST["product_type"];
    $shop_id = $_POST["shop_id"];
    $price = $_POST["price"];
    $status = $_POST["status"];
    $old_image = $_POST["old_image"];

    $product_image = $old_image;

    if(isset($_FILES['product_image']) && $_FILES['product_image']['error'] == 0){
        $ext = pathinfo($_FILES['product_image']['name'], PATHINFO_EXTENSION);
        $new_filename = "product_" . uniqid() . "." . $ext;
        $upload_path = "../uploads/" . $new_filename;

        if(move_uploaded_file($_FILES['product_image']['tmp_name'], $upload_path)){
            $product_image = $new_filename;
            
            if($old_image != "" && file_exists("../uploads/".$old_image)){
                unlink("../uploads/".$old_image);
            }
        }
    }

    $result = $controller->update_product($product_id, $product_name, $product_detail, $product_type, $price, $product_image, $status);

    if($result){
        $_SESSION['update_status'] = 'success';
    } else {
        $_SESSION['update_status'] = 'error';
    }
    
    // โหลดหน้าซ้ำเพื่อแสดง Alert และเคลียร์ POST
    echo "<script>window.location='edit_product.php?id=$product_id';</script>";
    exit;
}

if(!isset($_GET["id"])){
    echo "<script>window.location='manage_product.php';</script>";
    exit;
}else{
    $id = $_GET["id"];
    $result = $controller->get_product($id);
    if(!$result) {
        echo "<script>window.location='manage_product.php';</script>";
        exit;
    }
}
?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    .card-custom {
        border-radius: 15px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.06);
    }
    .preview-img {
        width: 180px;
        height: 180px;
        border-radius: 15px;
        object-fit: cover;
        background: #f8f9fa;
        border: 2px dashed #dee2e6;
        display: block;
        margin: 0 auto;
    }
    .form-label {
        font-weight: 600;
        color: #495057;
    }
    .btn-custom {
        border-radius: 10px;
        padding: 10px;
        font-weight: bold;
    }
</style>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            
            <div class="mb-3">
                <a href="manage_product.php" class="text-decoration-none text-muted fw-bold">
                    <i class="bi bi-arrow-left"></i> กลับไปหน้าจัดการสินค้า
                </a>
            </div>

            <div class="card card-custom border-0">
                <div class="card-header bg-white pt-4 pb-2 border-0 text-center">
                    <h4 class="fw-bold mb-0 text-dark"><i class="bi bi-pencil-square text-warning"></i> แก้ไขข้อมูลสินค้า (Admin)</h4>
                </div>
                
                <div class="card-body p-4 pt-3">
                    <form action="" method="POST" enctype="multipart/form-data">
                        
                        <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($result["product_id"]); ?>">
                        <input type="hidden" name="shop_id" value="<?php echo htmlspecialchars($result["shop_id"]); ?>">
                        <input type="hidden" name="old_image" value="<?php echo htmlspecialchars($result["product_image"]); ?>">

                        <div class="mb-4 text-center">
                            <img id="preview" src="../uploads/<?php echo $result['product_image'] ?: 'no-image.png'; ?>" class="preview-img mb-3" alt="Product Preview">
                            <input type="file" name="product_image" id="product_image" class="form-control" accept="image/*" onchange="previewImage(this)">
                            <small class="text-muted mt-2 d-block">* หากไม่ต้องการเปลี่ยนรูปภาพ ให้ปล่อยว่างไว้</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">ชื่อสินค้า <span class="text-danger">*</span></label>
                            <input type="text" name="product_name" class="form-control" value="<?php echo htmlspecialchars($result["product_name"]); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">รายละเอียดสินค้า <span class="text-danger">*</span></label>
                            <textarea name="product_detail" class="form-control" rows="3" ><?php echo htmlspecialchars($result["product_detail"]); ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">ประเภทสินค้า <span class="text-danger">*</span></label>
                            <select name="product_type" class="form-select" required>
                                <option value="" disabled>-- เลือกประเภทสินค้า --</option>
                                <option value="อาหาร" <?php echo (isset($result['product_type']) && $result['product_type'] == 'อาหาร') ? 'selected' : ''; ?>>อาหาร</option>
                                <option value="ผลไม้" <?php echo (isset($result['product_type']) && $result['product_type'] == 'ผลไม้') ? 'selected' : ''; ?>>ผลไม้</option>
                                <option value="เครื่องดื่ม" <?php echo (isset($result['product_type']) && $result['product_type'] == 'เครื่องดื่ม') ? 'selected' : ''; ?>>เครื่องดื่ม</option>
                                <option value="ขนม/ของว่าง" <?php echo (isset($result['product_type']) && $result['product_type'] == 'ขนม/ของว่าง') ? 'selected' : ''; ?>>ขนม/ของว่าง</option>
                                <option value="ของใช้" <?php echo (isset($result['product_type']) && $result['product_type'] == 'ของใช้') ? 'selected' : ''; ?>>ของใช้</option>
                                <option value="อื่นๆ" <?php echo (isset($result['product_type']) && $result['product_type'] == 'อื่นๆ') ? 'selected' : ''; ?>>อื่นๆ</option>
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label class="form-label">ราคา (บาท) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-success fw-bold">฿</span>
                                    <input type="number" step="0.01" min="0" name="price" class="form-control" value="<?php echo htmlspecialchars($result["price"]); ?>" required>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-4">
                                <label class="form-label">สถานะการขาย <span class="text-danger">*</span></label>
                                <select name="status" class="form-select bg-light" required>
                                    <option value="available" <?php echo ($result["status"] == "available") ? "selected" : ""; ?>>✅ พร้อมขาย</option>
                                    <option value="out_of_stock" <?php echo ($result["status"] == "out_of_stock") ? "selected" : ""; ?>>❌ สินค้าหมด (ไม่พร้อมขาย)</option>
                                </select>
                            </div>
                        </div>

                        <hr class="mb-4 opacity-25">

                        <div class="d-flex justify-content-between gap-3">
                            <a href="manage_product.php" class="btn btn-light w-50 btn-custom border">ยกเลิก</a>
                            <button type="submit" name="submit" class="btn btn-warning text-white w-50 btn-custom shadow-sm">
                                <i class="bi bi-save"></i> บันทึกการแก้ไข
                            </button>
                        </div>
                        
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// สคริปต์แสดงรูปภาพตัวอย่างทันทีที่เลือกไฟล์
function previewImage(input){
    const preview = document.getElementById('preview');
    if(input.files && input.files[0]){
        const reader = new FileReader();
        reader.onload = e => {
            preview.src = e.target.result;
            preview.style.border = 'none';
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

<?php if(isset($_SESSION['update_status'])): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            <?php if($_SESSION['update_status'] == 'success'): ?>
                Swal.fire({
                    icon: 'success',
                    title: 'อัปเดตสำเร็จ!',
                    text: 'แก้ไขข้อมูลสินค้าเรียบร้อยแล้ว',
                    confirmButtonColor: '#198754',
                    timer: 2000,
                    timerProgressBar: true
                }).then(() => {
                    window.location = 'manage_product.php';
                });
            <?php else: ?>
                Swal.fire({
                    icon: 'error',
                    title: 'เกิดข้อผิดพลาด!',
                    text: 'ไม่สามารถแก้ไขข้อมูลได้ กรุณาลองใหม่อีกครั้ง',
                    confirmButtonColor: '#dc3545'
                });
            <?php endif; ?>
        });
    </script>
    <?php unset($_SESSION['update_status']); ?>
<?php endif; ?>

<?php require_once("../layout/footer.php"); ?>