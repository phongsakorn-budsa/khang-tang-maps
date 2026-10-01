<?php
require_once("../config/connect.php");
require_once("../layout/session.php");

$status = $_GET['status'] ?? null;

// ดึงร้านของ user นี้
$my_shop = $controller->get_my_shop($_SESSION['userid']);
if(!$my_shop){
    header("Location: myshop.php");
    exit;
}

if(!in_array($status, ['0','1'])){
    header("Location: myshop.php");
    exit;
}

$result = $controller->update_shop_status($my_shop['shop_id'], $status);

if($result){
    $_SESSION['flash_success'] = ($status == 1)
        ? "เปิดร้านเรียบร้อยแล้ว"
        : "ปิดร้านเรียบร้อยแล้ว";
}else{
    $_SESSION['flash_error'] = "ไม่สามารถเปลี่ยนสถานะร้านได้";
}

header("Location: myshop.php");
exit;
