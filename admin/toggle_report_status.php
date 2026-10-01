<?php
require_once("../config/connect.php");
require_once("../layout/session.php");
require_once("../layout/check_admin.php");

$id = $_GET['id'] ?? 0;
$status = $_GET['status'] ?? '';

if(!$id || !in_array($status, ['pending','resolved'])){
    header("Location: /wayside_edit/admin/report.php");
    exit;
}

$result = $controller->update_report_admin_status($id, $status);

if($result){
    $_SESSION['flash_success'] = "อัปเดตสถานะแอดมินเรียบร้อย";
}else{
    $_SESSION['flash_error'] = "อัปเดตสถานะแอดมินไม่สำเร็จ";
}

header("Location: /wayside_edit/admin/report.php");
exit;
