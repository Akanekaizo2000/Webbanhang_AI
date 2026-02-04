<?php
include 'db.php';
$product_id = $_POST['product_id'] ?? 0;
$rating = $_POST['rating'] ?? 5;

if ($product_id > 0) {
    $stmt = $conn->prepare("INSERT INTO reviews (product_id, rating) VALUES (?, ?)");
    $stmt->execute([$product_id, $rating]);
    echo "OK";
}
?>