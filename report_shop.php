<?php 
require_once("config/connect.php");
require_once("layout/header.php");

// รับ shop_id จาก URL
$shop_id = $_GET['id'] ?? 0;
$shop = $controller->get_shop($shop_id);

if(!$shop){
    echo "<div class='container my-5'><div class='alert alert-danger text-center shadow-sm'>ไม่พบร้านค้า</div></div>";
    require_once("layout/footer.php");
    exit;
}

// บันทึกรายงาน
if(isset($_POST["submit"])){
    $report_type  = $_POST["report_type"];
    $detail       = $_POST["detail"];
    $report_date  = date("Y-m-d H:i:s");
    $admin_status = "pending";

    if($report_type == "" || $detail == ""){
        $error = "กรุณากรอกข้อมูลให้ครบถ้วน";
    }else{
        $result = $controller->insert_report_shop(
            $shop_id,
            $report_type,
            $detail,
            $report_date,
            $admin_status
        );

        if($result){
            echo "<script>
                alert('ส่งรายงานเรียบร้อยแล้ว ขอบคุณที่แจ้งข้อมูลให้เราทราบครับ');
                window.location='view_shop.php?id=" . htmlspecialchars($shop_id) . "';
            </script>";
            exit;
        }else{
            $error = "เกิดข้อผิดพลาดในการส่งข้อมูล กรุณาลองใหม่อีกครั้ง";
        }
    }
}
?>

<style>
    /* ตกแต่ง Card ให้ดูนุ่มนวลและมีมิติ */
    .card-custom {
        border-radius: 15px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.06);
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
    
    /* ไอคอนแจ้งเตือนด้านบน */
    .report-icon {
        font-size: 3rem;
        color: #dc3545;
        margin-bottom: -10px;
    }
</style>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card card-custom border-0">
                
                <div class="card-header bg-white pt-4 pb-2 border-0 text-center">
                    <i class="bi bi-exclamation-triangle-fill report-icon"></i>
                    <h4 class="fw-bold mt-2 text-danger">รายงานร้านค้าไม่เหมาะสม</h4>
                    <p class="text-muted small mb-0">แจ้งปัญหาที่พบ เพื่อให้เราตรวจสอบและปรับปรุงให้ดียิ่งขึ้น</p>
                </div>

                <div class="card-body p-4 pt-3">
                    <?php if(isset($error)){ ?>
                        <div class="alert alert-danger shadow-sm border-0" style="border-radius: 10px;">
                            <i class="bi bi-x-circle-fill"></i> <?php echo $error; ?>
                        </div>
                    <?php } ?>

                    <form method="POST">
                        <input type="hidden" name="shop_id" value="<?php echo htmlspecialchars($shop_id); ?>">

                        <div class="mb-4">
                            <label class="form-label">ชื่อร้านค้าที่กำลังรายงาน</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-shop text-muted"></i></span>
                                <input type="text" class="form-control bg-light border-start-0" 
                                       value="<?php echo htmlspecialchars($shop['shop_name']); ?>" readonly>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label text-danger">เรื่องที่จะรายงาน <span class="text-danger">*</span></label>
                            <select name="report_type" class="form-select border-danger-subtle" required>
                                <option value="" disabled selected>-- เลือกหัวข้อที่พบปัญหา --</option>
                                
                                <option value="ปักหมุดผิดตำแหน่ง">📍 ปักหมุดผิดตำแหน่ง / ไม่ตรงกับที่จริง</option>
                                <option value="ไปแล้วไม่พบร้านค้า">👀 ไปแล้วไม่พบร้านค้า / หาไม่เจอ</option>
                                <option value="ร้านเลิกกิจการ">❌ ร้านเลิกกิจการ / ปิดถาวรไปแล้ว</option>
                                
                                <option value="เบอร์ติดต่อไม่ได้">📞 เบอร์โทรศัพท์ติดต่อไม่ได้ / เบอร์ผิด</option>
                                <option value="ข้อมูลหรือราคาไม่ถูกต้อง">💰 ข้อมูลสินค้า หรือราคาไม่ตรงความจริง</option>
                                
                                <option value="พฤติกรรมไม่เหมาะสม">😠 พฤติกรรมผู้ขายไม่เหมาะสม / ไม่สุภาพ</option>
                                <option value="ร้านค้าปลอม/สแปม">🚫 ร้านค้าปลอม / สแปมก่อกวน</option>
                                <option value="สินค้าผิดกฎหมาย/อันตราย">⚠️ ขายสินค้าผิดกฎหมาย / ไม่ปลอดภัย</option>
                                
                                <option value="อื่นๆ">💬 อื่นๆ (โปรดระบุในรายละเอียดด้านล่าง)</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">รายละเอียดเพิ่มเติม <span class="text-danger">*</span></label>
                            <textarea name="detail" class="form-control" rows="4" 
                                      placeholder="อธิบายเหตุการณ์ หรือจุดสังเกตเพิ่มเติมเพื่อให้แอดมินตรวจสอบได้ง่ายขึ้น..." required></textarea>
                        </div>

                        <hr class="mb-4 opacity-25">

                        <div class="d-flex justify-content-between gap-3">
                            <a href="view_shop.php?id=<?php echo htmlspecialchars($shop_id); ?>" class="btn btn-light w-50 btn-custom border">ยกเลิก</a>
                            <button type="submit" name="submit" class="btn btn-danger text-white w-50 btn-custom shadow-sm">
                                <i class="bi bi-send-fill"></i> ส่งรายงาน
                            </button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>
</div>

<?php require_once("layout/footer.php"); ?>