<?php 
require_once("../config/connect.php");
if(!isset($_GET["id"])){
    header("Location:manage_product.php");
}else{
    $id=$_GET["id"];
    $result = $controller->delete_product($id);
    if($result){
        header("Location:manage_product.php");
    }
}
?>