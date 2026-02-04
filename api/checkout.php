<?php
include 'db.php';
header('Content-Type: application/json');

// Nhận JSON từ Javascript
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) { echo json_encode(['error' => 'Không có dữ liệu']); exit; }

try {
    $conn->beginTransaction();

    // 1. Tính tổng tiền
    $total = 0;
    foreach ($input['cart'] as $item) {
        $total += $item['price'] * $item['quantity'];
    }

    // 2. Lưu vào bảng orders
    $stmt = $conn->prepare("INSERT INTO orders (customer_name, phone, address, total_money) VALUES (?, ?, ?, ?)");
    $stmt->execute([$input['name'], $input['phone'], $input['address'], $total]);
    $orderId = $conn->lastInsertId();

    // 3. Lưu chi tiết đơn hàng
    $stmtDetail = $conn->prepare("INSERT INTO order_details (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
    foreach ($input['cart'] as $item) {
        $stmtDetail->execute([$orderId, $item['id'], $item['quantity'], $item['price']]);
    }

    $conn->commit();
    echo json_encode(['success' => true, 'order_id' => $orderId]);

} catch (Exception $e) {
    $conn->rollBack();
    echo json_encode(['error' => $e->getMessage()]);
}
?>