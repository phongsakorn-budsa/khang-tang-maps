<?php
require_once("../config/connect.php");
require_once("../layout/session.php");


$product_id = $_GET['id'] ?? 0;
$status     = $_GET['status'] ?? '';

if(!in_array($status, ['available','unavailable'])){
    header("Location: myshop.php");
    exit;
}

$result = $controller->update_product_status($product_id, $status);

if($result){
    $_SESSION['flash_success'] = "อัปเดตสถานะสินค้าเรียบร้อย";
}else{
    $_SESSION['flash_error'] = "ไม่สามารถอัปเดตสินค้าได้";
}

header("Location: myshop.php");
exit;
