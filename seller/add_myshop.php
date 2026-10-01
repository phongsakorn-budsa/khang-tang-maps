<?php 
require_once("../config/connect.php");
require_once("../layout/header.php");

if(isset($_POST["submit"])){
    $owner_id   = $_SESSION["userid"];
    $shop_name  = $_POST["shop_name"];
    $description = $_POST["description"];
    $latitude   = $_POST["latitude"];
    $longitude  = $_POST["longitude"];
    $is_open    = 1;

    $shop_image = "";

    if(isset($_FILES['shop_image']) && $_FILES['shop_image']['error'] == 0){
        $ext = pathinfo($_FILES['shop_image']['name'], PATHINFO_EXTENSION);
        $new_filename = "shop_" . uniqid() . "." . $ext;
        $upload_path = "../uploads/" . $new_filename;

        if(move_uploaded_file($_FILES['shop_image']['tmp_name'], $upload_path)){
            $shop_image = $new_filename;
        }
    }

    $result = $controller->insert_shop(
        $owner_id,
        $shop_name,
        $description,
        $shop_image,
        $latitude,
        $longitude,
        $is_open
    );

    // ==========================================
    // เปลี่ยนมาใช้ SweetAlert2 แจ้งเตือนแทน alert ธรรมดา
    // ==========================================
    if($result){
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'success',
                    title: 'สร้างร้านค้าสำเร็จ!',
                    text: 'ระบบได้บันทึกข้อมูลร้านค้าของคุณเรียบร้อยแล้ว',
                    confirmButtonColor: '#198754',
                    timer: 2000,
                    timerProgressBar: true
                }).then(() => {
                    window.location = 'myshop.php';
                });
            });
        </script>";
    }else{
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'error',
                    title: 'เกิดข้อผิดพลาด!',
                    text: 'ไม่สามารถบันทึกข้อมูลได้ กรุณาลองใหม่อีกครั้ง',
                    confirmButtonColor: '#dc3545'
                });
            });
        </script>";
    }
}
?>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css" />

<style>
    /* ตกแต่ง Card ให้ดูนุ่มนวลและมีมิติ */
    .card-custom {
        border-radius: 15px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.06);
    }
    
    /* ตกแต่งส่วนแสดงตัวอย่างรูปร้านค้า */
    .preview-img {
        width: 100%;
        height: 220px;
        border-radius: 15px;
        object-fit: cover;
        background: #f8f9fa;
        border: 2px dashed #dee2e6;
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
        <div class="col-md-8 col-lg-6">
            <div class="card card-custom border-0">
                
                <div class="card-header bg-white pt-4 pb-2 border-0 text-center">
                    <h4 class="fw-bold mb-0"><i class="bi bi-shop text-warning"></i> เพิ่มร้านค้าของฉัน</h4>
                </div>
                
                <div class="card-body p-4 pt-3">
                    <form action="" method="POST" enctype="multipart/form-data">

                        <div class="mb-4 text-center">
                            <img id="preview" src="" class="preview-img mb-3 shadow-sm" alt="Shop Preview" style="display: none;">
                            
                            <input type="file" name="shop_image" class="form-control" accept="image/*" onchange="previewImage(this)" required>
                            <small class="text-muted mt-2 d-block">อัปโหลดรูปภาพหน้าร้าน (รองรับ .jpg, .png)</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">ชื่อร้าน <span class="text-danger">*</span></label>
                            <input type="text" name="shop_name" class="form-control" 
                                   required placeholder="เช่น ร้านทุเรียนเจ๊สมศรี, ข้าวไข่เจียวอาหวัง">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">รายละเอียด / จุดสังเกต <span class="text-danger">*</span></label>
                            <textarea name="description" class="form-control" rows="3" 
                                      required placeholder="เช่น ตั้งอยู่หน้าเซเว่น ซอย 3..."></textarea>
                        </div>

                        <input type="hidden" id="latitude" name="latitude" required>
                        <input type="hidden" id="longitude" name="longitude" required>

                        <div class="mb-4 mt-4">
                            <label class="form-label mb-2"><i class="bi bi-geo-alt-fill text-danger"></i> ปักหมุดตำแหน่งร้าน <span class="text-danger">*</span></label>
                            <div id="pickMap"></div>
                            <small class="text-muted d-block mt-2">
                                * ซูมและคลิกบนแผนที่ 1 ครั้ง เพื่อระบุตำแหน่งร้านของคุณ
                            </small>
                        </div>

                        <hr class="mb-4 opacity-25">

                        <div class="d-flex justify-content-between gap-3">
                            <a href="/wayside_edit/seller/myshop.php" class="btn btn-light w-50 btn-custom border">ยกเลิก</a>
                            <button type="submit" name="submit" class="btn btn-warning text-white w-50 btn-custom shadow-sm">
                                <i class="bi bi-save"></i> บันทึกข้อมูลร้านค้า
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>

<script>
/* ===== Map เลือกพิกัด ===== */
const pickMap = L.map('pickMap').setView([13.1119, 99.9447], 12);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap contributors'
}).addTo(pickMap);

let marker;

// เพิ่มระบบค้นหาสถานที่
L.Control.geocoder({
    defaultMarkGeocode: false,
    placeholder: "ค้นหาสถานที่ เช่น ชื่อถนน, อำเภอ..."
}).on('markgeocode', function(e) {
    const latlng = e.geocode.center;
    const lat = latlng.lat.toFixed(6);
    const lng = latlng.lng.toFixed(6);

    document.getElementById('latitude').value = lat;
    document.getElementById('longitude').value = lng;

    if(marker){
        pickMap.removeLayer(marker);
    }
    marker = L.marker([lat, lng]).addTo(pickMap)
        .bindPopup(e.geocode.name || "ตำแหน่งที่ค้นหา")
        .openPopup();
    
    pickMap.setView(latlng, 16);
}).addTo(pickMap);

pickMap.on('click', function(e){
    const lat = e.latlng.lat.toFixed(6);
    const lng = e.latlng.lng.toFixed(6);

    document.getElementById('latitude').value = lat;
    document.getElementById('longitude').value = lng;

    if(marker){
        pickMap.removeLayer(marker);
    }

    marker = L.marker([lat, lng]).addTo(pickMap)
        .bindPopup("ตำแหน่งร้านของคุณ")
        .openPopup();
});

// แก้บั๊กแผนที่สีเทา: สั่งให้โหลดขนาดใหม่เมื่อหน้าเว็บและรูปภาพโหลดเสร็จ
window.addEventListener('load', function() {
    setTimeout(function(){ 
        pickMap.invalidateSize(); 
    }, 500);
});

/* ===== Preview รูปร้าน ===== */
function previewImage(input){
    const preview = document.getElementById('preview');
    if(input.files && input.files[0]){
        const reader = new FileReader();
        reader.onload = e => {
            preview.src = e.target.result;
            preview.style.display = 'block'; // แสดงรูปขึ้นมา
            preview.style.border = 'none'; 
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

<?php require_once("../layout/footer.php"); ?>