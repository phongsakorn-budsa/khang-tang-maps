<?php
require_once("../config/connect.php");
require_once("../layout/session.php");
require_once("../layout/check_admin.php");

$id = $_GET['id'] ?? 0;

if(!$id){
    header("Location: /wayside_edit/admin/report.php");
    exit;
}

$result = $controller->delete_report($id);

if($result){
    $_SESSION['flash_success'] = "ลบรายงานปัญหาเรียบร้อยแล้ว";
}else{
    $_SESSION['flash_error'] = "ไม่สามารถลบรายงานปัญหาได้";
}

header("Location: /wayside_edit/admin/report.php");
exit;
