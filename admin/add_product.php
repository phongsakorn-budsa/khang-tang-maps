<?php
require_once("../config/connect.php");
require_once("../layout/header.php");

// รับค่า shop_id จาก URL
$shop_id = $_GET['shop_id'] ?? 0;
$shop_id = (int)$shop_id;

if ($shop_id === 0) {
    echo "<div class='container my-5'><div class='alert alert-danger text-center'>ไม่พบข้อมูลร้านค้า กรุณากลับไปที่หน้าร้านค้า</div></div>";
    require_once("../layout/footer.php");
    exit;
}

$success_msg = false;
$error_msg = "";

// ตรวจสอบการส่งฟอร์ม (POST Request)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_name = trim($_POST['product_name']);
    $product_detail = trim($_POST['product_detail']);
    $product_type = $_POST["product_type"];
    $price = (float)$_POST['price'];
    $status = $_POST['status'] ?? 'available';
    
    $product_image = ''; // ค่าเริ่มต้นหากไม่มีการอัปโหลดรูป

    // จัดการอัปโหลดรูปภาพ
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
        $tmp_name = $_FILES['product_image']['tmp_name'];
        $file_name = $_FILES['product_image']['name'];
        
        // สร้างชื่อไฟล์ใหม่กันชื่อซ้ำ (ใช้เวลาปัจจุบัน + สุ่มตัวเลข)
        $ext = pathinfo($file_name, PATHINFO_EXTENSION);
        $new_file_name = time() . '_' . rand(1000, 9999) . '.' . $ext;
        $upload_path = "../uploads/" . $new_file_name;

        // ถ้าย้ายไฟล์สำเร็จ ให้เก็บชื่อไฟล์ไว้บันทึกลงฐานข้อมูล
        if (move_uploaded_file($tmp_name, $upload_path)) {
            $product_image = $new_file_name;
        }
    }

    /* *** ส่วนสำคัญ ***
        เรียกใช้ฟังก์ชันใน Controller เพื่อบันทึกลงฐานข้อมูล
        (คุณอาจจะต้องปรับชื่อฟังก์ชัน 'add_product' ให้ตรงกับที่คุณเขียนไว้ใน class ของคุณ)
    */
    $result = $controller->insert_products(
        $shop_id,
        $product_name,
        $product_detail,
        $product_type, // 2. ส่งค่าประเภทสินค้าไปยังฟังก์ชัน (อย่าลืมไปแก้ฟังก์ชันใน class ด้วยนะครับ)
        $price,
        $product_image,
        $status
    );

    if ($result) {
        $success_msg = true; // ตั้งสถานะความสำเร็จเพื่อไปโชว์ SweetAlert ด้านล่าง
    } else {
        $error_msg = "เกิดข้อผิดพลาดในการบันทึกข้อมูล กรุณาลองใหม่อีกครั้ง";
    }
}
?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-header bg-white pt-4 pb-2 border-0 text-center">
                    <h4 class="fw-bold"><i class="bi bi-box-seam text-warning"></i> เพิ่มสินค้าใหม่</h4>
                </div>
                
                <div class="card-body p-4">
                    
                    <form action="" method="POST" enctype="multipart/form-data">
                        
                        <div class="mb-4 text-center">
                            <img id="imgPreview" src="../uploads/no-image.png" alt="Preview" 
                                 class="shadow-sm mb-3" style="height: 200px; width: 200px; object-fit: cover; border-radius: 15px;">
                            
                            <input class="form-control" type="file" id="product_image" name="product_image" accept="image/*" onchange="previewImage(event)">
                            <small class="text-muted d-block mt-2">อัปโหลดรูปภาพสินค้า (รองรับไฟล์ .jpg, .png)</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">ชื่อสินค้า <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="product_name" required placeholder="เช่น ทุเรียนหมอนทอง, เสื้อยืด">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">รายละเอียดสินค้า</label>
                            <textarea class="form-control" name="product_detail" rows="3" placeholder="ระบุรายละเอียดเพิ่มเติม..."></textarea>
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

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">ราคา (บาท) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" class="form-control text-success fw-bold" name="price" required placeholder="0.00">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">สถานะ</label>
                                <select class="form-select" name="status">
                                    <option value="available">พร้อมขาย</option>
                                    <option value="unavailable">สินค้าหมด</option>
                                </select>
                            </div>
                        </div>

                        <hr class="my-4 opacity-25">

                        <div class="d-flex justify-content-between gap-3">
                            <a href="shop_detail.php?id=<?php echo $shop_id; ?>" class="btn btn-light border w-50 fw-bold">ยกเลิก</a>
                            <button type="submit" class="btn btn-warning text-white fw-bold w-50 shadow-sm">บันทึกสินค้า</button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// สคริปต์สำหรับโชว์รูปตัวอย่าง
function previewImage(event) {
    const reader = new FileReader();
    reader.onload = function(){
        const output = document.getElementById('imgPreview');
        output.src = reader.result;
    };
    if(event.target.files[0]) {
        reader.readAsDataURL(event.target.files[0]);
    }
}
</script>

<?php if($success_msg): ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            icon: 'success',
            title: 'เพิ่มสินค้าสำเร็จ!',
            text: 'ระบบได้บันทึกข้อมูลสินค้าใหม่ของคุณแล้ว',
            confirmButtonColor: '#198754',
            timer: 2000,
            timerProgressBar: true
        }).then(() => {
            // ดึง shop_id จาก PHP เพื่อพากลับไปหน้ารายละเอียดร้านให้ถูกต้อง
            window.location.href = 'shop_detail.php?id=<?php echo $shop_id; ?>';
        });
    });
</script>
<?php endif; ?>

<?php if(!empty($error_msg)): ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            icon: 'error',
            title: 'เกิดข้อผิดพลาด!',
            text: '<?php echo $error_msg; ?>',
            confirmButtonColor: '#dc3545'
        });
    });
</script>
<?php endif; ?>

<?php require_once("../layout/footer.php"); ?>