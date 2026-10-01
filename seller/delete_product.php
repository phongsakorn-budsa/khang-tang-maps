<?php 
require_once("../config/connect.php");
if(!isset($_GET["id"])){
    header("Location:myshop.php");
}else{
    $id=$_GET["id"];
    $result = $controller->delete_product($id);
    if($result){
        header("Location:myshop.php");
    }
}
?>