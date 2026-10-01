<?php 
require_once("../config/connect.php");
require_once("../layout/header.php");
require_once("../layout/check_admin.php");

if(isset($_POST["submit"])){
    $shop_id   = $_POST["shop_id"];
    $owner_id  = $_POST["owner_id"];
    $shop_name = $_POST["shop_name"];
    $description = $_POST["description"];
    $latitude  = $_POST["latitude"];
    $longitude = $_POST["longitude"];
    $is_open = $_POST["is_open"];
    $shop_type = $_POST["shop_type"];
    $old_image = $_POST["old_image"];
    $shop_image = $old_image;

    if(isset($_FILES['shop_image']) && $_FILES['shop_image']['error'] == 0){
        $ext = pathinfo($_FILES['shop_image']['name'], PATHINFO_EXTENSION);
        $new_filename = "shop_" . uniqid() . "." . $ext;
        $upload_path = "../uploads/" . $new_filename;

        if(move_uploaded_file($_FILES['shop_image']['tmp_name'], $upload_path)){
            $shop_image = $new_filename;

            if($old_image && file_exists("../uploads/".$old_image)){
                unlink("../uploads/".$old_image);
            }
        }
    }

    $result = $controller->update_shop_admin(
        $shop_id,
        $owner_id,
        $shop_name,
        $description,
        $shop_image,
        $latitude,
        $longitude,
        $is_open,
        $shop_type
    );

    if($result){
        $_SESSION['update_status'] = 'success';
    }else{
        $_SESSION['update_status'] = 'error';
    }
    
    echo "<script>window.location='edit_shop.php?id=$shop_id';</script>";
    exit;
}

if(!isset($_GET["id"])){
    echo "<script>window.location='manage_shop.php';</script>";
    exit;
}

$id = $_GET["id"];
$result = $controller->get_shop($id);
if(!$result) {
    echo "<script>window.location='manage_shop.php';</script>";
    exit;
}
?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>

<style>
    /* ตกแต่ง Card ให้ดูนุ่มนวลและมีมิติ */
    .card-custom {
        border-radius: 15px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.06);
    }
    
    /* ตกแต่งส่วนแสดงตัวอย่างรูปร้านค้า (แนวยาว) */
    .preview-img {
        width: 100%;
        height: 220px;
        border-radius: 15px;
        object-fit: cover;
        background: #f8f9fa;
        border: 2px dashed #dee2e6;
        margin: 0 auto;
        display: block;
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

    /* ตกแต่งแผนที่ Leaflet */
    #pickMap {
        height: 300px;
        width: 100%;
        border-radius: 12px;
        border: 2px solid #f0f0f0;
        z-index: 1; 
    }
</style>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-11 col-xl-10">
            
            <div class="mb-3">
                <a href="manage_shop.php" class="text-decoration-none text-muted fw-bold">
                    <i class="bi bi-arrow-left"></i> กลับไปหน้าจัดการร้านค้า
                </a>
            </div>

            <div class="card card-custom border-0">
                <div class="card-header bg-white pt-4 pb-2 border-0 text-center">
                    <h4 class="fw-bold mb-0 text-dark"><i class="bi bi-pencil-square text-warning"></i> แก้ไขร้านค้า (Admin)</h4>
                </div>
                
                <div class="card-body p-4 pt-3">
                    <form action="" method="POST" enctype="multipart/form-data">

                        <input type="hidden" name="shop_id" value="<?php echo htmlspecialchars($result['shop_id']); ?>">
                        <input type="hidden" name="owner_id" value="<?php echo htmlspecialchars($result['owner_id']); ?>">
                        <input type="hidden" name="old_image" value="<?php echo htmlspecialchars($result['shop_image']); ?>">

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-4 text-center">
                            <label class="form-label d-block">รูปภาพร้านค้า</label>
                            <img id="preview" src="../uploads/<?php echo $result['shop_image'] ?: 'no-image.png'; ?>" class="preview-img mb-3 shadow-sm" alt="Shop Preview">
                            <input type="file" name="shop_image" class="form-control" accept="image/*" onchange="previewImage(this)">
                            <small class="text-muted mt-2 d-block">* ถ้าไม่ต้องการเปลี่ยนรูปภาพ ไม่ต้องเลือกไฟล์ใหม่</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">ชื่อร้านค้า <span class="text-danger">*</span></label>
                            <input type="text" name="shop_name" class="form-control" 
                                   value="<?php echo htmlspecialchars($result['shop_name']); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">ประเภทร้านค้า</label>
                            <select name="shop_type" class="form-select">
                                <option value="" <?php if(empty($result['shop_type'])) echo 'selected'; ?>>-- ไม่ระบุ --</option>
                                <option value="อาหาร" <?php if($result['shop_type'] == 'อาหาร') echo 'selected'; ?>>อาหาร</option>
                                <option value="เครื่องดื่ม" <?php if($result['shop_type'] == 'เครื่องดื่ม') echo 'selected'; ?>>เครื่องดื่ม</option>
                                <option value="ของทานเล่น" <?php if($result['shop_type'] == 'ของทานเล่น') echo 'selected'; ?>>ของทานเล่น</option>
                                <option value="ผลไม้" <?php if($result['shop_type'] == 'ผลไม้') echo 'selected'; ?>>ผลไม้</option>
                                <option value="ของฝาก" <?php if($result['shop_type'] == 'ของฝาก') echo 'selected'; ?>>ของฝาก</option>
                                <option value="ของใช้" <?php if($result['shop_type'] == 'ของใช้') echo 'selected'; ?>>ของใช้</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">คำอธิบาย / จุดสังเกต <span class="text-danger">*</span></label>
                            <textarea name="description" class="form-control" rows="3" 
                                      required><?php echo htmlspecialchars($result['description']); ?></textarea>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">สถานะร้านค้า (โดย Admin) <span class="text-danger">*</span></label>
                            <select name="is_open" class="form-select bg-light">
                                <option value="1" <?php if($result['is_open'] == 1) echo 'selected'; ?>>✅ เปิดร้าน</option>
                                <option value="0" <?php if($result['is_open'] == 0) echo 'selected'; ?>>❌ ปิดร้าน</option>
                            </select>
                        </div>

                        <input type="hidden" id="latitude" name="latitude" value="<?php echo htmlspecialchars($result['latitude']); ?>" required>
                        <input type="hidden" id="longitude" name="longitude" value="<?php echo htmlspecialchars($result['longitude']); ?>" required>

                            </div> <!-- End left column -->

                            <!-- Right Column: Map -->
                            <div class="col-md-6 d-flex flex-column">
                                <div class="mb-4 mt-4 mt-md-0 flex-grow-1 d-flex flex-column h-100">
                                    <label class="form-label mb-2"><i class="bi bi-geo-alt-fill text-danger"></i> ตำแหน่งร้านบนแผนที่ <span class="text-danger">*</span></label>
                                    <div id="pickMap" class="shadow-sm flex-grow-1" style="min-height: 400px; border-radius: 12px; border: 2px solid #f0f0f0;"></div>
                                    <small class="text-muted d-block mt-2">
                                        * ซูมและคลิกบนแผนที่ เพื่อแก้ไขตำแหน่งร้านค้า (พิกัด: <span id="latDisplay"><?php echo $result['latitude']; ?></span>, <span id="lngDisplay"><?php echo $result['longitude']; ?></span>)
                                    </small>
                                </div>
                            </div> <!-- End right column -->
                        </div> <!-- End row -->

                        <hr class="mb-4 opacity-25">

                        <div class="d-flex justify-content-between gap-3">
                            <a href="manage_shop.php" class="btn btn-light w-50 btn-custom border">ยกเลิก</a>
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

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
/* ===== Map เลือกพิกัด ===== */
const latInit = <?php echo (float)$result['latitude']; ?>;
const lngInit = <?php echo (float)$result['longitude']; ?>;

const pickMap = L.map('pickMap').setView([latInit, lngInit], 15);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap contributors'
}).addTo(pickMap);

let marker = L.marker([latInit, lngInit]).addTo(pickMap)
    .bindPopup("ตำแหน่งปัจจุบัน")
    .openPopup();

pickMap.on('click', function(e){
    const lat = e.latlng.lat.toFixed(6);
    const lng = e.latlng.lng.toFixed(6);

    document.getElementById('latitude').value = lat;
    document.getElementById('longitude').value = lng;
    
    // อัปเดตตัวหนังสือแสดงพิกัดด้านล่าง
    document.getElementById('latDisplay').innerText = lat;
    document.getElementById('lngDisplay').innerText = lng;

    marker.setLatLng([lat, lng]).bindPopup("ตำแหน่งใหม่").openPopup();
});

// แก้บั๊กแผนที่สีเทา: สั่งให้โหลดขนาดใหม่เมื่อหน้าเว็บและรูปภาพโหลดเสร็จ
window.addEventListener('load', function() {
    setTimeout(function(){ 
        pickMap.invalidateSize(); 
    }, 500);
});

/* ===== Preview รูป ===== */
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
                    text: 'แก้ไขข้อมูลร้านค้าเรียบร้อยแล้ว',
                    confirmButtonColor: '#198754',
                    timer: 2000,
                    timerProgressBar: true
                }).then(() => {
                    window.location = 'manage_shop.php';
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