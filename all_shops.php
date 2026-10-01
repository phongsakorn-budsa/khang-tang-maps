<?php 
require_once("config/connect.php");
require_once("layout/header.php");

// ดึงข้อมูลร้านค้าพร้อมสินค้าทั้งหมด
$shops = $controller->get_all_open_shops_with_products();
$json_shops = json_encode($shops, JSON_UNESCAPED_UNICODE);

// ดึงข้อมูลสินค้าทั้งหมด เพื่อเอามาเช็คว่าแต่ละร้านขายสินค้าหมวดหมู่ไหนบ้าง
$all_products = $controller->search_products();
$json_products = json_encode($all_products ?: [], JSON_UNESCAPED_UNICODE);
?>

<style>
    body { background-color: #f8f9fa; } 
    
    .search-bar-home {
        background: white; border-radius: 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.02);
        padding: 2px 10px; border: 1px solid #eaeaea;
    }
    .search-bar-home input { border: none; box-shadow: none; padding: 10px; background: transparent; }
    .search-bar-home input:focus { box-shadow: none; }
    
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
</style>

<div class="container pb-5 mt-4">
    <div class="d-flex align-items-center mb-4">
        <a href="index.php" class="btn btn-light rounded-circle shadow-sm me-3"><i class="bi bi-arrow-left"></i></a>
        <h3 class="fw-bold mb-0 text-dark">ร้านค้าทั้งหมด</h3>
    </div>

    <div class="row mb-4">
        <div class="col-md-6 col-lg-5">
            <div class="input-group search-bar-home flex-grow-1">
                <span class="input-group-text bg-transparent border-0 ps-3"><i class="bi bi-search text-muted"></i></span>
                <input type="text" id="mainSearchInput" class="form-control" placeholder="ค้นหาร้านค้า หรือ สินค้า..." oninput="applyFilters()">
            </div>
        </div>
    </div>

    <div class="row g-4" id="shopListContainer">
        <!-- Shop Cards Here -->
    </div>
</div>

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

// ==========================================
// กรองข้อมูลร้านค้า
// ==========================================
function applyFilters() {
    const currentKeyword = document.getElementById('mainSearchInput').value.toLowerCase().trim();
    const urlParams = new URLSearchParams(window.location.search);
    const filterFav = urlParams.get('filter') === 'favorites';
    const favs = getFavorites();

    const filteredShops = shopsData.filter(shop => {
        const shopProds = shopProductMap[shop.shop_id] || { types: new Set(), names: [] };

        const nameMatch = shop.shop_name?.toLowerCase().includes(currentKeyword);
        const descMatch = shop.description?.toLowerCase().includes(currentKeyword);
        const productMatch = shopProds.names.some(name => name.includes(currentKeyword));
        
        let match = currentKeyword === '' || nameMatch || descMatch || productMatch;
        
        if (filterFav) {
            match = match && favs.includes(shop.shop_id.toString());
        }
        
        return match;
    });

    renderShopList(filteredShops);
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
                <h5 class="mt-4 fw-bold">ไม่พบร้านค้า</h5>
                <p>ลองค้นหาด้วยคำอื่น</p>
            </div>
        `;
        return;
    }

    data.forEach(shop => {
        const img = shop.shop_image ? `uploads/${shop.shop_image}` : 'uploads/no-image.png';
        
        // หาหมวดหมู่ของร้าน เพื่อนำมาแสดงที่มุมซ้ายบน
        let mainType = 'ทั่วไป';
        let badgeClass = 'text-success'; // default color
        
        if(shopProductMap[shop.shop_id] && shopProductMap[shop.shop_id].types.size > 0) {
            mainType = Array.from(shopProductMap[shop.shop_id].types)[0];
            if(mainType === 'อาหาร') badgeClass = 'text-danger';
            if(mainType === 'เครื่องดื่ม') badgeClass = 'text-warning';
            if(mainType === 'ผลไม้') badgeClass = 'text-success';
            if(mainType === 'ขนม/ของว่าง') { mainType = 'ของฝาก'; badgeClass = 'text-primary'; }
        }
        
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

// โหลดข้อมูลครั้งแรก
applyFilters();
</script>
