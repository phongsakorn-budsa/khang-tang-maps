<?php
require_once("../config/connect.php");
require_once("../layout/header.php");
require_once("../layout/check_admin.php");

// ดึงข้อมูลร้านค้าทั้งหมดที่ไม่ได้ถูกลบ
$shops = $controller->search_shops('', '');
?>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

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
   Main Content & Map
========================================= */
.main-content {
    background-color: #f8f9fc;
    padding: 30px;
    min-height: calc(100vh - 60px);
}
#map {
    width: 100%;
    height: calc(100vh - 160px);
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    z-index: 1; /* ป้องกันไม่ให้ทับ nav/modal */
}
.shop-marker {
    position: relative;
    text-align: center;
    cursor: pointer;
    transition: transform 0.2s;
}
.shop-marker:hover { transform: scale(1.1); }
.shop-marker img {
    width: 50px !important; 
    height: 50px !important;
    min-width: 50px !important;
    min-height: 50px !important;
    border-radius: 50% !important;
    border: 3px solid #ffc107; 
    object-fit: cover !important;
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    background: #fff;
}
.shop-name {
    margin-top: -5px; font-size: 12px; font-weight: bold;
    color: #333; background: rgba(255,255,255,0.95);
    padding: 3px 8px; border-radius: 10px;
    white-space: nowrap; box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
.leaflet-popup-content-wrapper { border-radius: 12px; overflow: hidden; padding: 0; }
.leaflet-popup-content { margin: 0; width: 220px !important; }
.popup-img { width: 100%; height: 130px; object-fit: cover; }
.popup-body { padding: 12px; }
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
                    <a href="map.php" class="nav-link nav-link-custom active">
                        <i class="bi bi-map-fill"></i> แผนที่ร้านค้า
                    </a>
                </li>
                <li class="nav-item">
                    <a href="report.php" class="nav-link nav-link-custom">
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
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3 class="fw-bold mb-0 text-dark"><i class="bi bi-map"></i> แผนที่ร้านค้าทั้งหมด (Admin View)</h3>
            </div>
            
            <div id="map"></div>
        </div>
    </div>
</div>

<script>
const shopsData = <?php echo json_encode($shops); ?>;

// ตั้งค่าพิกัดเริ่มต้น (หาค่ากลางจากร้านค้า หรือค่าเริ่มต้นถ้าไม่มีร้าน)
let centerLat = 13.1119;
let centerLng = 99.9447;

const map = L.map('map').setView([centerLat, centerLng], 13);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { 
    attribution: '&copy; OpenStreetMap',
    maxZoom: 19
}).addTo(map);

let bounds = [];

shopsData.forEach(shop => {
    if(!shop.latitude || !shop.longitude) return;
    
    const lat = parseFloat(shop.latitude);
    const lng = parseFloat(shop.longitude);
    bounds.push([lat, lng]);

    const img = shop.shop_image ? `../uploads/${shop.shop_image}` : '../uploads/no-image.png';
    const popupImg = shop.shop_image ? `<img src="../uploads/${shop.shop_image}" class="popup-img">` : `<div style="height:120px;background:#eee;display:flex;align-items:center;justify-content:center;color:#999;">ไม่มีรูปภาพ</div>`;
    
    // Status color
    const isOpen = parseInt(shop.is_open) === 1;
    const borderColor = isOpen ? '#28a745' : '#dc3545';
    const statusText = isOpen ? '<span class="text-success"><i class="bi bi-circle-fill small"></i> เปิดบริการ</span>' : '<span class="text-danger"><i class="bi bi-circle-fill small"></i> ปิดร้าน</span>';

    const icon = L.divIcon({
        html: `
            <div class="shop-marker">
                <img src="${img}" style="border-color: ${borderColor};">
                <div class="shop-name">${shop.shop_name}</div>
            </div>`,
        className: '',
        iconSize: [60,70],
        iconAnchor: [30,60],
        popupAnchor: [0, -65]
    });

    const marker = L.marker([lat, lng], { icon }).addTo(map);
    
    marker.bindPopup(`
        ${popupImg}
        <div class="popup-body text-center">
            <h6 class="fw-bold mb-1">${shop.shop_name}</h6>
            <div class="mb-3 small fw-bold">${statusText}</div>
            <a href="shop_detail.php?id=${shop.shop_id}" class="btn btn-sm btn-primary w-100 fw-bold shadow-sm">
                <i class="bi bi-gear-fill"></i> จัดการร้านนี้
            </a>
        </div>
    `);
});

if(bounds.length > 0) {
    map.fitBounds(bounds, { padding: [50, 50], maxZoom: 17 });
}

// แก้บัคแผนที่โหลดไม่เต็มกรอบ
setTimeout(() => {
    map.invalidateSize();
}, 500);
</script>

<?php require_once("../layout/footer.php"); ?>

