<?php
require_once("../config/connect.php");
require_once("../layout/header.php");
require_once("../layout/check_login.php");

$owner_id = $_SESSION['userid'];
$status = $_GET['status'] ?? '';

$reports = $controller->get_reports_for_owner($owner_id, $status);
?>

<div class="container my-4">
    <h3 class="mb-3">📩 รายงานที่ร้านของคุณได้รับ</h3>

    <?php if(isset($_SESSION['flash_success'])){ ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?php echo $_SESSION['flash_success']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php unset($_SESSION['flash_success']); } ?>

    <?php if(isset($_SESSION['flash_error'])){ ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?php echo $_SESSION['flash_error']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php unset($_SESSION['flash_error']); } ?>

    <div class="d-flex gap-2 mb-3">
        <a href="reports.php" class="btn btn-outline-secondary btn-sm">ทั้งหมด</a>
        <a href="reports.php?status=new" class="btn btn-outline-danger btn-sm">ใหม่</a>
        <a href="reports.php?status=in_progress" class="btn btn-outline-warning btn-sm">กำลังแก้ไข</a>
        <a href="reports.php?status=fixed" class="btn btn-outline-success btn-sm">แก้ไขแล้ว</a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <table class="table table-hover table-bordered align-middle">
                <thead class="table-warning text-center">
                    <tr>
                        <th>#</th>
                        <th>ร้าน</th>
                        <th>ประเภท</th>
                        <th>รายละเอียด</th>
                        <th>วันที่</th>
                        <th>สถานะร้าน</th>
                        <th style="width:260px;">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                <?php if(!empty($reports)){ $i=1; foreach($reports as $r){ ?>
                    <tr>
                        <td class="text-center"><?php echo $i++; ?></td>
                        <td><?php echo htmlspecialchars($r['shop_name']); ?></td>
                        <td><?php echo htmlspecialchars($r['report_type']); ?></td>
                        <td><?php echo htmlspecialchars($r['detail']); ?></td>
                        <td class="text-center"><?php echo date("d/m/Y H:i", strtotime($r['report_date'])); ?></td>

                        <td class="text-center">
                            <?php
                                $st = $r['seller_status'];
                                if($st=='new') echo '<span class="badge bg-danger">ใหม่</span>';
                                elseif($st=='in_progress') echo '<span class="badge bg-warning text-dark">กำลังแก้</span>';
                                else echo '<span class="badge bg-success">แก้แล้ว</span>';
                            ?>
                        </td>

                        <td class="text-center">
                            <!-- ปุ่มรับเรื่อง -->
                            <?php if($r['seller_status'] == 'new'){ ?>
                                <a class="btn btn-sm btn-warning text-white"
                                   href="toggle_report_seller.php?id=<?php echo (int)$r['report_id']; ?>&status=in_progress"
                                   onclick="return confirm('รับเรื่องและเริ่มแก้ไข?');">
                                    รับเรื่อง
                                </a>
                            <?php } ?>

                            <!-- ปุ่มแก้แล้ว + note -->
                            <button class="btn btn-sm btn-success"
                                    data-bs-toggle="modal"
                                    data-bs-target="#fixedModal<?php echo (int)$r['report_id']; ?>">
                                แก้แล้ว
                            </button>
                        </td>
                    </tr>

                    <!-- Modal ใส่ note แล้วกดแก้แล้ว -->
                <?php } } else { ?>
                    <tr><td colspan="7" class="text-center text-muted">ยังไม่มีรายงาน</td></tr>
                <?php } ?>
                </tbody>
            </table>

            <div class="modal fade" id="fixedModal<?php echo (int)$r['report_id']; ?>" tabindex="-1">
                      <div class="modal-dialog modal-dialog-centered">
                        <form class="modal-content" method="POST" action="toggle_report_seller.php">
                          <div class="modal-header">
                            <h5 class="modal-title">ส่งว่าแก้ไขแล้ว</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                          </div>
                          <div class="modal-body">
                            <input type="hidden" name="id" value="<?php echo (int)$r['report_id']; ?>">
                            <input type="hidden" name="status" value="fixed">

                            <label class="form-label">รายละเอียดที่แก้ไข (ส่งให้แอดมินเห็น)</label>
                            <textarea name="seller_note" class="form-control" rows="4"
                                      placeholder="เช่น: ย้ายหมุดให้ตรงแล้ว / เปิดร้านตามปกติแล้ว / อัปเดตเบอร์ติดต่อแล้ว"></textarea>
                          </div>
                          <div class="modal-footer">
                            <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">ยกเลิก</button>
                            <button class="btn btn-success" type="submit">ยืนยัน</button>
                          </div>
                        </form>
                      </div>
                    </div>
        </div>
    </div>
</div>

<?php require_once("../layout/footer.php"); ?>
