<?php
require_once("../config/connect.php");

$id = $_GET['id'] ?? 0;
$id = (int)$id;

if($id <= 0){
    header("Location: manage_shop.php");
    exit;
}

$result = $controller->delete_shop($id);

if($result){
    $_SESSION['flash_success'] = "ลบร้านสำเร็จ (ซ่อนร้าน + ซ่อนสินค้าทั้งหมดแล้ว)";
}else{
    $_SESSION['flash_error'] = "ลบร้านไม่สำเร็จ";
}

header("Location: manage_shop.php");
exit;
