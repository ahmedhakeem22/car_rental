<?php
// db_connect.php
$servername = "localhost";
$username = "root"; 
$password = "root"; 
$dbname = "cars";    

// إنشاء الاتصال
$conn = new mysqli($servername, $username, $password, $dbname);

// التحقق من الاتصال
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// تعيين ترميز الحروف لضمان دعم اللغة العربية
$conn->set_charset("utf8mb4");
?>