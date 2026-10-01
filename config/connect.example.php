<?php
$host="localhost";
$username="root";
$password="";
$db="your_database_name";
$dsn="mysql:host=$host;dbname=$db;charset=utf8";

try{
    $pdo = new PDO($dsn,$username,$password);
}catch(PDOException $e){
    echo $e->getMessage();
    return false;
}

require_once("controller.php");
require_once("users.php");

$controller = new controller($pdo);
$users = new users($pdo);
?>
