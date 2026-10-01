<?php 
require_once("config/connect.php");
require_once("layout/header.php");

// ดึงข้อมูลร้านค้าพร้อมสินค้า
$shops = $controller->get_all_open_shops_with_products();
$json_shops = json_encode($shops, JSON_UNESCAPED_UNICODE);

// ดึงข้อมูลสินค้าทั้งหมด เพื่อเอามาเช็คว่าแต่ละร้านขายสินค้าหมวดหมู่ไหนบ้าง
$all_products = $controller->search_products();
$json_products = json_encode($all_products ?: [], JSON_UNESCAPED_UNICODE);
?>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>

<style>
    body { background-color: #f8f9fa; } 

    /* --- Hero Banner --- */
    .hero-banner {
        background: linear-gradient(to right, #fffdf2, #fff6d9);
        border-radius: 20px;
        padding: 40px;
        position: relative;
        overflow: hidden;
        margin-bottom: 30px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.03);
    }
    .hero-placeholder {
        width: 100%;
        height: 200px;
        background: #f0f0f0;
        border: 2px dashed #ccc;
        border-radius: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #999;
        font-weight: 500;
    }

    /* --- UI หมวดหมู่ & Search --- */
    .category-section { margin-bottom: 30px; }
    .category-btn {
        display: flex; flex-direction: column; align-items: center; justify-content: center;
        background: white; border: 1px solid #eee; border-radius: 15px;
        padding: 15px 10px; cursor: pointer; transition: all 0.2s;
        box-shadow: 0 2px 8px rgba(0,0,0,0.02);
        min-width: 80px; flex: 1;
    }
    .category-btn:hover { transform: translateY(-3px); box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
    .category-btn.active { border-color: #ffc107; background-color: #fffdf5; }
    
    .category-icon-wrapper {
        width: 45px; height: 45px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        margin-bottom: 8px;
    }
    .category-btn.active .category-icon-wrapper {
        transform: scale(1.1);
    }
    /* สีพื้นหลังแบบจางๆ สำหรับไอคอน */
    .icon-all { background-color: #fff3cd; color: #ffc107; }
    .icon-food { background-color: #f8d7da; color: #dc3545; }
    .icon-drink { background-color: #ffe5d0; color: #fd7e14; }
    .icon-fruit { background-color: #d1e7dd; color: #198754; }
    .icon-snack { background-color: #e0cffc; color: #6f42c1; }
    .icon-item { background-color: #cfe2ff; color: #0d6efd; }
    .category-icon-wrapper i { font-size: 20px; }

    .category-name { font-size: 13px; color: #495057; font-weight: 600; }

    /* --- Search Bar & Toggles --- */
    .search-bar-home {
        background: white; border-radius: 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.02);
        padding: 2px 10px; border: 1px solid #eaeaea;
    }
    .search-bar-home input { border: none; box-shadow: none; padding: 10px; background: transparent; }
    .search-bar-home input:focus { box-shadow: none; }
    .btn-toggle-view {
        border-radius: 20px !important; padding: 8px 20px;
        font-weight: 600; font-size: 0.9rem; border: none; transition: 0.2s;
    }
    .btn-toggle-view.active {
        background-color: #ffc107; color: white !important; box-shadow: 0 4px 10px rgba(255,193,7,0.3);
    }
    .btn-toggle-view.btn-light {
        border: 1px solid #eaeaea;
    }

    /* --- Shop Card Styles --- */
    .shop-card {
        transition: transform 0.2s, box-shadow 0.2s; cursor: pointer;
        border-radius: 16px; overflow: hidden; background: white;
        border: 1px solid #f0f0f0;
    }
    .shop-card:hover { transform: translateY(-4px); box-shadow: 0 8px 25px rgba(0,0,0,0.08) !important; }
    
    .card-img-wrapper { position: relative; }
    .shop-tag {
        position: absolute; top: 12px; left: 12px;
        background: white; padding: 4px 10px; border-radius: 12px;
        font-size: 0.75rem; font-weight: bold; color: #333;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    .shop-fav {
        position: absolute; top: 12px; right: 12px;
        background: rgba(0,0,0,0.4); padding: 5px; border-radius: 50%;
        color: white; width: 30px; height: 30px;
        display: flex; align-items: center; justify-content: center;
        transition: 0.2s;
    }
    .shop-fav:hover { background: #dc3545; }

    /* --- Bottom Banner --- */
    .bottom-banner {
        background: white; border-radius: 16px; padding: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.02); margin-top: 40px; margin-bottom: 20px;
    }
    .feature-item {
        display: flex; align-items: center; gap: 15px;
    }
    .feature-icon {
        width: 50px; height: 50px; border-radius: 50%; min-width: 50px;
        display: flex; align-items: center; justify-content: center; font-size: 24px; color: white;
    }
    .explorer-box {
        background: #fff8e1; border-radius: 16px; padding: 20px;
        position: relative; overflow: hidden; height: 100%; display: flex; flex-direction: row; justify-content: start; align-items: center;
    }

    /* --- Map Styles --- */
    #mapView { display: none; }
    #map { height: 500px; width: 100%; border-radius: 16px; border: 1px solid #eaeaea; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
    
    .shop-marker { text-align: center; transition: transform 0.2s; }
    .shop-marker:hover { transform: scale(1.1); }
    .shop-marker img {
        width: 50px !important; 
        height: 50px !important;
        min-width: 50px !important;
        min-height: 50px !important;
        border-radius: 50% !important;
        border: 3px solid #ffc107 !important; 
        background: #fff;
        object-fit: cover !important; 
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    }
    .shop-marker .shop-name {
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

<div class="container pb-5 mt-4">
    
    <!-- Hero Banner -->
    <div class="position-relative mb-4" style="border-radius: 20px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.03);">
        <img src="image/Hero_Banner.png" class="w-100" alt="Hero Banner">
        <!-- ปุ่มสำรวจร้านค้าใกล้คุณ ปรับขนาดและตำแหน่งให้ใหญ่เกือบเท่ากรอบเส้นประ -->
        <a href="all_shops.php" class="btn btn-warning text-white position-absolute d-flex justify-content-center align-items-center shadow" 
           style="bottom: 13%; left: 6%; width: 24%; height: 15%; border-radius: 50px; font-size: 1.2vw; font-weight: 800;">
            สำรวจร้านค้าใกล้คุณ <i class="bi bi-chevron-right ms-1"></i>
        </a>
    </div>

    <!-- Category & Search Section -->
    <div class="row align-items-end mb-4 category-section">
        <div class="col-lg-7">
            <h6 class="fw-bold mb-3">เลือกหมวดหมู่ที่คุณสนใจ</h6>
            <div class="d-flex flex-wrap gap-2" id="categoryButtons">
                <button class="category-btn active" data-category="" onclick="selectCategory('', this)">
                    <div class="category-icon-wrapper icon-all"><i class="bi bi-shop"></i></div>
                    <span class="category-name">ทั้งหมด</span>
                </button>
                <button class="category-btn" data-category="อาหาร" onclick="selectCategory('อาหาร', this)">
                    <div class="category-icon-wrapper icon-food"><i class="bi bi-egg-fried"></i></div>
                    <span class="category-name">อาหาร</span>
                </button>
                <button class="category-btn" data-category="เครื่องดื่ม" onclick="selectCategory('เครื่องดื่ม', this)">
                    <div class="category-icon-wrapper icon-drink"><i class="bi bi-cup-straw"></i></div>
                    <span class="category-name">เครื่องดื่ม</span>
                </button>
                <button class="category-btn" data-category="ของทานเล่น" onclick="selectCategory('ของทานเล่น', this)">
                    <div class="category-icon-wrapper" style="background-color: #f3e5f5; color: #9c27b0;"><i class="bi bi-basket-fill"></i></div>
                    <span class="category-name">ของทานเล่น</span>
                </button>
                <button class="category-btn" data-category="ผลไม้" onclick="selectCategory('ผลไม้', this)">
                    <div class="category-icon-wrapper icon-fruit"><i class="bi bi-apple"></i></div>
                    <span class="category-name">ผลไม้</span>
                </button>
                <button class="category-btn" data-category="ของฝาก" onclick="selectCategory('ของฝาก', this)">
                    <div class="category-icon-wrapper icon-snack"><i class="bi bi-gift-fill"></i></div>
                    <span class="category-name">ของฝาก</span>
                </button>
                <button class="category-btn" data-category="ของใช้" onclick="selectCategory('ของใช้', this)">
                    <div class="category-icon-wrapper icon-item"><i class="bi bi-bag-fill"></i></div>
                    <span class="category-name">ของใช้</span>
                </button>
            </div>
        </div>
        
        <div class="col-lg-5 mt-4 mt-lg-0">
            <div class="d-flex align-items-center gap-2 mb-3">
                <div class="input-group search-bar-home flex-grow-1">
                    <span class="input-group-text bg-transparent border-0 ps-3"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" id="mainSearchInput" class="form-control" placeholder="ค้นหาร้านค้า หรือ สินค้า..." oninput="applyFilters()">
                    <button class="btn btn-light rounded-pill ms-1 me-1 my-1 px-3 border border-light" style="background:#f8f9fa;" type="button" data-bs-toggle="modal" data-bs-target="#filterModal"><i class="bi bi-sliders"></i> ตัวกรอง</button>
                </div>
            </div>
            
            <div class="d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-toggle-view active text-white" id="btnListView" onclick="toggleView('list')">
                    <i class="bi bi-star-fill"></i> แนะนำยอดนิยม
                </button>
                <button type="button" class="btn btn-light text-dark btn-toggle-view" id="btnMapView" onclick="toggleView('map')">
                    <i class="bi bi-map"></i> ดูแผนที่
                </button>
            </div>
        </div>
    </div>

    <!-- Shop List Header -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold mb-0">🔥 ร้านแนะนำยอดนิยม</h5>
        <a href="all_shops.php" class="text-dark text-decoration-none small fw-bold">ดูทั้งหมด <i class="bi bi-chevron-right"></i></a>
    </div>

    <div id="listView">
        <div class="row g-4" id="shopListContainer">
            <!-- Shop Cards Here -->
        </div>
    </div>

    <div id="mapView">
        <div class="row">
            <div class="col-12">
                <div id="map"></div>
            </div>
        </div>
    </div>

    <!-- Features & Explorer Cards -->
    <div class="row g-3 g-xl-4 mb-5 mt-4">
        <!-- 3 Features Card -->
        <div class="col-lg-6">
            <div class="row g-0 h-100 shadow-sm" style="border-radius: 16px; overflow: hidden; border: 1px solid #f0f0f0;">
                <!-- Feature 1 -->
                <div class="col-4 p-2 p-md-3 d-flex align-items-center justify-content-center" style="background-color: #fffbf0;">
                    <div class="d-flex flex-column align-items-center gap-2 w-100 justify-content-center text-center">
                        <div class="rounded-circle d-flex align-items-center justify-content-center text-white flex-shrink-0 my-2" style="width: 55px; height: 55px; background-color: #ffb800; font-size: 1.5rem;">
                            <i class="bi bi-shop"></i>
                        </div>
                        <div class="d-none d-md-block">
                            <h6 class="fw-bold mb-1 text-dark" style="font-size: 0.85rem;">ร้านค้าท้องถิ่น</h6>
                            <small class="text-muted d-none d-xl-block" style="font-size: 0.7rem; line-height: 1.2;">รวมร้านเด็ด ร้านดัง ในอำเภอเมืองตรัง</small>
                        </div>
                    </div>
                </div>
                <!-- Feature 2 (ค้นหาง่าย) -->
                <div class="col-4 p-2 p-md-3 d-flex align-items-center justify-content-center" style="background-color: #f4f6ff;">
                    <div class="d-flex flex-column align-items-center gap-2 w-100 justify-content-center text-center">
                        <div class="rounded-circle d-flex align-items-center justify-content-center text-white flex-shrink-0 my-2" style="width: 55px; height: 55px; background-color: #5c7cfa; font-size: 1.5rem;">
                            <i class="bi bi-geo-alt-fill"></i>
                        </div>
                        <div class="d-none d-md-block">
                            <h6 class="fw-bold mb-1 text-dark" style="font-size: 0.85rem;">ค้นหาง่าย</h6>
                            <small class="text-muted d-none d-xl-block" style="font-size: 0.7rem; line-height: 1.2;">เจอร้านที่ใช่ ใกล้คุณที่สุด</small>
                        </div>
                    </div>
                </div>
                <!-- Feature 3 (สนับสนุนชุมชน) -->
                <div class="col-4 p-2 p-md-3 d-flex align-items-center justify-content-center" style="background-color: #fff4f8;">
                    <div class="d-flex flex-column align-items-center gap-2 w-100 justify-content-center text-center">
                        <div class="rounded-circle d-flex align-items-center justify-content-center text-white flex-shrink-0 my-2" style="width: 55px; height: 55px; background-color: #f03e6e; font-size: 1.5rem;">
                            <i class="bi bi-heart-fill"></i>
                        </div>
                        <div class="d-none d-md-block">
                            <h6 class="fw-bold mb-1 text-dark" style="font-size: 0.85rem;">สนับสนุนชุมชน</h6>
                            <small class="text-muted d-none d-xl-block" style="font-size: 0.7rem; line-height: 1.2;">ช่วยกันอุดหนุน ร้านค้าในท้องถิ่น</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Explorer Banner Right -->
        <div class="col-lg-6">
            <div class="h-100 shadow-sm" style="border-radius: 16px; overflow: hidden; min-height: 100px;">
                <img src="image/banner_above.png" class="w-100 h-100" style="object-fit: cover; object-position: center;" alt="Explorer Banner">
            </div>
        </div>
    </div>

</div>

<!-- Filter Modal -->
<div class="modal fade" id="filterModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 16px; border: none;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">ตัวกรอง</h5>
                <button type="button" class="btn-close shadow-sm bg-white rounded-circle p-2" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-bold">หมวดหมู่</label>
                    <select id="modalFilterCategory" class="form-select" style="border-radius: 8px;">
                        <option value="">ทั้งหมด</option>
                        <option value="อาหาร">อาหาร</option>
                        <option value="เครื่องดื่ม">เครื่องดื่ม</option>
                        <option value="ของทานเล่น">ของทานเล่น</option>
                        <option value="ผลไม้">ผลไม้</option>
                        <option value="ของฝาก">ของฝาก</option>
                        <option value="ของใช้">ของใช้</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">เรียงตาม</label>
                    <select id="modalFilterSort" class="form-select" style="border-radius: 8px;">
                        <option value="default">แนะนำยอดนิยม (ค่าเริ่มต้น)</option>
                        <option value="name_asc">ชื่อร้าน (A-Z, ก-ฮ)</option>
                        <option value="name_desc">ชื่อร้าน (Z-A, ฮ-ก)</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal" style="border-radius: 8px;">ยกเลิก</button>
                <button type="button" class="btn fw-bold px-4 text-dark" style="border-radius: 8px; background-color: #ffeb3b; border: none;" onclick="applyAdvancedFilters()" data-bs-dismiss="modal">นำไปใช้</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="shopModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content" style="border-radius: 20px; overflow: hidden; border: none; background-color: #f8f9fa;">
            <div class="modal-header border-0 pb-0 position-absolute w-100" style="z-index: 1050; justify-content: flex-end;">
                <button type="button" class="btn-close bg-white rounded-circle p-2 m-2 shadow-sm" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0" id="shopModalContent">
                <div class="text-center py-5">
                    <div class="spinner-border text-warning" role="status"></div>
                    <p class="mt-2 text-muted">กำลังโหลดข้อมูลร้านค้า...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
// ข้อมูลร้านและสินค้าจาก PHP
const shopsData = <?php echo $json_shops ?: '[]'; ?>;
const allProducts = <?php echo $json_products ?: '[]'; ?>;

const shopProductMap = {};
allProducts.forEach(p => {
    if (!shopProductMap[p.shop_id]) {
        shopProductMap[p.shop_id] = { types: new Set(), names: [] };
    }
    if (p.product_type) shopProductMap[p.shop_id].types.add(p.product_type);
    if (p.product_name) shopProductMap[p.shop_id].names.push(p.product_name.toLowerCase());
});

let currentCategory = '';
let currentKeyword = '';

// ==========================================
// สลับมุมมอง รายการ/แผนที่
// ==========================================
function toggleView(viewType) {
    const btnList = document.getElementById('btnListView');
    const btnMap = document.getElementById('btnMapView');

    if(viewType === 'list') {
        document.getElementById('listView').style.display = 'block';
        document.getElementById('mapView').style.display = 'none';
        
        btnList.classList.add('active', 'text-white', 'btn-warning');
        btnList.classList.remove('btn-light', 'text-dark');
        
        btnMap.classList.add('btn-light', 'text-dark');
        btnMap.classList.remove('active', 'text-white', 'btn-warning');
    } else {
        document.getElementById('listView').style.display = 'none';
        document.getElementById('mapView').style.display = 'block';
        
        btnMap.classList.add('active', 'text-white', 'btn-warning');
        btnMap.classList.remove('btn-light', 'text-dark');
        
        btnList.classList.add('btn-light', 'text-dark');
        btnList.classList.remove('active', 'text-white', 'btn-warning');
        
        setTimeout(() => { map.invalidateSize(); }, 100);
    }
}

// ==========================================
// เลือกหมวดหมู่ 
// ==========================================
function selectCategory(category, btnElement) {
    currentCategory = category;
    
    document.querySelectorAll('.category-btn').forEach(btn => btn.classList.remove('active'));
    btnElement.classList.add('active');
    
    // Sync กลับไปที่ Modal ถ้ามีการเปลี่ยนหมวดหมู่จากปุ่มด้านนอก
    const modalCategory = document.getElementById('modalFilterCategory');
    if (modalCategory) {
        modalCategory.value = category;
    }
    
    applyFilters();
}

// ==========================================
// กรองข้อมูลร้านค้า (ใช้ Set() เพื่อความแม่นยำ)
// ==========================================
function applyFilters() {
    currentKeyword = document.getElementById('mainSearchInput').value.toLowerCase().trim();

    const filteredShops = shopsData.filter(shop => {
        const shopProds = shopProductMap[shop.shop_id] || { types: new Set(), names: [] };

        const nameMatch = shop.shop_name?.toLowerCase().includes(currentKeyword);
        const descMatch = shop.description?.toLowerCase().includes(currentKeyword);
        const productMatch = shopProds.names.some(name => name.includes(currentKeyword));
        
        const keywordOk = currentKeyword === '' || nameMatch || descMatch || productMatch;

        let categoryOk = true;
        if(currentCategory !== '') {
            categoryOk = (shop.shop_type === currentCategory);
        }

        return keywordOk && categoryOk;
    });

    const sortValue = document.getElementById('modalFilterSort') ? document.getElementById('modalFilterSort').value : 'default';
    if (sortValue === 'name_asc') {
        filteredShops.sort((a, b) => (a.shop_name || '').localeCompare(b.shop_name || '', 'th'));
    } else if (sortValue === 'name_desc') {
        filteredShops.sort((a, b) => (b.shop_name || '').localeCompare(a.shop_name || '', 'th'));
    }

    renderShopList(filteredShops);
    renderMarkers(filteredShops);
}

// ==========================================
// กรองข้อมูลขั้นสูงจาก Modal
// ==========================================
function applyAdvancedFilters() {
    currentCategory = document.getElementById('modalFilterCategory').value;
    
    // Sync ปุ่มหมวดหมู่ด้านนอกให้ตรงกับใน Modal
    document.querySelectorAll('.category-btn').forEach(btn => {
        btn.classList.remove('active');
        const onclickStr = btn.getAttribute('onclick');
        if (onclickStr && onclickStr.includes(`'${currentCategory}'`)) {
            btn.classList.add('active');
        }
    });
    
    applyFilters();
}

// ==========================================
// วาดรายการร้านค้า (List View)
// ==========================================
function renderShopList(data) {
    const container = document.getElementById('shopListContainer');
    container.innerHTML = '';

    if(data.length === 0) {
        container.innerHTML = `
            <div class="col-12 text-center py-5 mt-4 text-muted">
                <i class="bi bi-shop display-1 opacity-25"></i>
                <h5 class="mt-4 fw-bold">ไม่พบร้านค้าในหมวดหมู่นี้</h5>
                <p>ลองค้นหาด้วยคำอื่น หรือเลือกหมวดหมู่ใหม่</p>
            </div>
        `;
        return;
    }

    data.forEach(shop => {
        const img = shop.shop_image ? `uploads/${shop.shop_image}` : 'uploads/no-image.png';
        
        // หาหมวดหมู่ของร้าน เพื่อนำมาแสดงที่มุมซ้ายบน
        let mainType = shop.shop_type || 'ทั่วไป';
        let badgeClass = 'text-success'; // default color
        
        if(mainType === 'อาหาร') badgeClass = 'text-danger';
        else if(mainType === 'เครื่องดื่ม') badgeClass = 'text-warning';
        else if(mainType === 'ผลไม้') badgeClass = 'text-success';
        else if(mainType === 'ของฝาก' || mainType === 'ขนม/ของว่าง' || mainType === 'ของทานเล่น') badgeClass = 'text-primary';
        else if(mainType === 'ของใช้') badgeClass = 'text-info';
        
        const favs = getFavorites();
        const isFav = favs.includes(shop.shop_id.toString());
        const iconClass = isFav ? 'bi-heart-fill' : 'bi-heart';
        const favBg = isFav ? 'background: #dc3545;' : '';
        
        const html = `
            <div class="col-md-6 col-lg-3">
                <div class="card border-0 shadow-sm shop-card h-100" onclick="window.location.href='view_shop.php?id=${shop.shop_id}'">
                    <div class="card-img-wrapper">
                        <img src="${img}" class="card-img-top" style="height: 180px; object-fit: cover;" alt="Shop Image">
                        <div class="shop-tag ${badgeClass}"><i class="bi bi-tag-fill me-1"></i>${mainType}</div>
                        <div class="shop-fav" style="${favBg}" onclick="toggleFavorite('${shop.shop_id}', event, this)"><i class="bi ${iconClass}"></i></div>
                    </div>
                    <div class="card-body">
                        <h6 class="fw-bold mb-1 text-dark">${shop.shop_name}</h6>
                        <p class="small text-muted mb-0" style="display:-webkit-box; -webkit-line-clamp:1; -webkit-box-orient:vertical; overflow:hidden;">
                            ${shop.description || 'ไม่มีรายละเอียด'}
                        </p>
                    </div>
                </div>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', html);
    });
}

// ==========================================
// แผนที่และหมุด (Map View)
// ==========================================
const map = L.map('map').setView([13.1119, 99.9447], 13);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap' }).addTo(map);

let markers = [];

function renderMarkers(data){
    markers.forEach(m => map.removeLayer(m));
    markers = [];
    const bounds = [];

    data.forEach(shop => {
        if(!shop.latitude || !shop.longitude) return;
        const lat = parseFloat(shop.latitude);
        const lng = parseFloat(shop.longitude);

        const icon = L.divIcon({
            html: `<div class="shop-marker"><img src="uploads/${shop.shop_image || 'no-image.png'}"><div class="shop-name">${shop.shop_name}</div></div>`,
            className: '', iconSize: [60,70], iconAnchor: [30,60], popupAnchor: [0, -65]
        });

        const marker = L.marker([lat, lng], { icon }).addTo(map);
        
        // 🔹 โหมดแผนที่: เรียกฟังก์ชัน openShopModal แทน!
        marker.bindPopup(`
            ${shop.shop_image ? `<img src="uploads/${shop.shop_image}" class="popup-img">` : `<div style="height:120px;background:#eee;display:flex;align-items:center;justify-content:center;color:#999;">ไม่มีรูปภาพ</div>`}
            <div class="popup-body text-center">
                <h6 class="fw-bold mb-3">${shop.shop_name}</h6>
                <button class="btn btn-sm btn-warning w-100 text-white fw-bold shadow-sm" onclick="openShopModal(${shop.shop_id})">
                    <i class="bi bi-shop"></i> เยี่ยมชมร้าน
                </button>
            </div>
        `);
        markers.push(marker);
        bounds.push([lat, lng]);
    });

    if(bounds.length > 0) map.fitBounds(bounds, { padding: [50,50], maxZoom: 16 });
}

// ==========================================
// ระบบ Modal สำหรับแสดงร้านบนแผนที่
// ==========================================
function openShopModal(shopId) {
    const shopModal = new bootstrap.Modal(document.getElementById('shopModal'));
    const content = document.getElementById('shopModalContent');
    
    // เคลียร์เนื้อหาและเปิด Modal โชว์ Loader ก่อน
    content.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-warning" role="status"></div><p class="mt-2 text-muted">กำลังโหลดข้อมูลร้าน...</p></div>';
    shopModal.show();
    
    // เรียกหน้า view_shop พร้อมแนบพารามิเตอร์ modal=true
    fetch(`view_shop.php?id=${shopId}&modal=true`)
        .then(response => response.text())
        .then(html => {
            content.innerHTML = html;
        })
        .catch(error => {
            content.innerHTML = '<div class="text-center py-5 text-danger"><i class="bi bi-exclamation-circle display-1"></i><p class="mt-3">เกิดข้อผิดพลาดในการดึงข้อมูล</p></div>';
        });
}

// โหลดข้อมูลเข้าสู่หน้าจอครั้งแรก
// --- Favorites Functionality ---
function getFavorites() {
    const match = document.cookie.match(new RegExp('(^| )favorites=([^;]+)'));
    if (match) {
        try { return JSON.parse(decodeURIComponent(match[2])); } catch (e) { return []; }
    }
    return [];
}

function toggleFavorite(shopId, event, el) {
    event.stopPropagation();
    let favs = getFavorites();
    const index = favs.indexOf(shopId.toString());
    const icon = el.querySelector('i');
    
    if (index > -1) {
        favs.splice(index, 1);
        el.style.background = '';
        icon.className = 'bi bi-heart';
    } else {
        favs.push(shopId.toString());
        el.style.background = '#dc3545';
        icon.className = 'bi bi-heart-fill';
    }
    document.cookie = "favorites=" + encodeURIComponent(JSON.stringify(favs)) + "; path=/; max-age=31536000";
}

// Initial render
applyFilters();
</script>