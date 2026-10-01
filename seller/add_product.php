<?php 
require_once("../config/connect.php");
require_once("../layout/header.php");

$my_shop = $controller->get_my_shop($_SESSION['userid']);
if(!$my_shop){
    header("Location: myshop.php");
    exit();
}
$shop_id_db = $my_shop['shop_id'];

if(isset($_POST["submit"])){
    $shop_id = $_POST["shop_id"];
    $product_name = $_POST["product_name"];
    $product_detail = $_POST["product_detail"]; 
    $product_type = $_POST["product_type"]; // 1. เพิ่มตัวแปรรับค่าประเภทสินค้า
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
        $product_type, // 2. ส่งค่าประเภทสินค้าไปยังฟังก์ชัน (อย่าลืมไปแก้ฟังก์ชันใน class ด้วยนะครับ)
        $price,
        $product_image,
        $status
    );

    if($result){
        echo "<script>alert('เพิ่มสินค้าเรียบร้อยแล้ว'); window.location='myshop.php';</script>";
    }else{
        echo "<script>alert('เกิดข้อผิดพลาด');</script>";
    }
}
?>

<style>
    /* ตกแต่ง Card ให้ดูนุ่มนวลและมีมิติ */
    .card-custom {
        border-radius: 15px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.06);
    }
    
    /* ตกแต่งส่วนแสดงตัวอย่างรูปภาพ */
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
    
    /* ปรับแต่ง Label ตัวหนังสือหนา */
    .form-label {
        font-weight: 600;
        color: #495057;
    }
    
    /* ปรับปุ่มให้มนขึ้น */
    .btn-custom {
        border-radius: 10px;
        padding: 10px;
        font-weight: bold;
    }
</style>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card card-custom border-0">
                
                <div class="card-header bg-white pt-4 pb-2 border-0 text-center">
                    <h4 class="fw-bold mb-0"><i class="bi bi-box-seam text-warning"></i> เพิ่มสินค้า</h4>
                </div>

                <div class="card-body p-4 pt-3">
                    <form action="" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="shop_id" value="<?php echo $shop_id_db; ?>">

                        <div class="mb-4 text-center">
                            <img id="preview" src="" class="preview-img mb-3" alt="Image Preview" style="display: none;">
                            <input type="file" name="product_image" id="product_image" class="form-control" accept="image/*" onchange="previewImage(this)" required>
                            <small class="text-muted mt-2 d-block">รองรับไฟล์ .jpg, .png</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">ชื่อสินค้า <span class="text-danger">*</span></label>
                            <input type="text" name="product_name" class="form-control"
                                   placeholder="เช่น ทุเรียนหมอนทอง, ข้าวกล่อง" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">รายละเอียดสินค้า <span class="text-danger">*</span></label>
                            <textarea name="product_detail" class="form-control" rows="2"
                                      placeholder="เช่น น้ำหนัก, ขนาด, รสชาติ" ></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">ประเภทสินค้า <span class="text-danger">*</span></label>
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
                            <label class="form-label">ราคา (บาท) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="price" class="form-control"
                                   placeholder="0.00" required>
                        </div>

                        <hr class="mb-4 opacity-25">

                        <div class="d-flex justify-content-between gap-3">
                            <a href="myshop.php" class="btn btn-light w-50 btn-custom border">ยกเลิก</a>
                            <button type="submit" name="submit" class="btn btn-warning text-white w-50 btn-custom">
                                ยืนยันการเพิ่มสินค้า
                            </button>
                        </div>
                        
                    </form>
                </div>
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
            preview.style.border = 'none'; 
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

<?php require_once("../layout/footer.php"); ?>