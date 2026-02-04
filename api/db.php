<?php
$host = "localhost";
$db_name = "quan_ly_ban_hang";
$username = "root";
$password = ""; // Mặc định trên XAMPP để trống
try {
    $conn = new PDO("mysql:host=$host;dbname=$db_name", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Thiết lập font chữ tiếng Việt
    $conn->exec("set names utf8");
} catch(PDOException $e) {
    echo "Lỗi kết nối: " . $e->getMessage();
}
?>