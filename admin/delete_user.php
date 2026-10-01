<?php 
require_once("../config/connect.php");

if(!isset($_GET["id"])){
    header("Location:manage_users.php");
}else{
    $id=$_GET["id"];
    $result = $controller->delete_user($id);
    if($result){
        header("Location:manage_users.php");
    }
}
?>