<?php
require_once("../config/connect.php");
require_once("../layout/session.php");

$owner_id = $_SESSION['userid'];

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $id = $_POST['id'] ?? 0;
    $status = $_POST['status'] ?? '';
    $note = $_POST['seller_note'] ?? null;
} else {
    $id = $_GET['id'] ?? 0;
    $status = $_GET['status'] ?? '';
    $note = null;
}

if(!$id || !in_array($status, ['new','in_progress','fixed'])){
    header("Location: reports.php");
    exit;
}

// อัปเดตสถานะ (ร้านเป็นคนกดเองอยู่แล้ว)
$result = $controller->update_report_seller_status($id, $status, $note);

if($result){
    $_SESSION['flash_success'] = "อัปเดตสถานะเรียบร้อย";
}else{
    $_SESSION['flash_error'] = "อัปเดตไม่สำเร็จ";
}

header("Location: reports.php");
exit;
