<?php
$host="your_database_host";
$username="your_database_username";
$password="your_database_password";
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
