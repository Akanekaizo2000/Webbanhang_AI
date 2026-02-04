<?php
include 'db.php';
header('Content-Type: application/json; charset=utf-8');

try {
    if (!isset($conn)) {
        $conn = new PDO("mysql:host=localhost;dbname=quan_ly_ban_hang;charset=utf8", "root", "");
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    // CÂU TRUY VẤN QUAN TRỌNG:
    // Lấy thông tin sản phẩm VÀ kèm theo 1 ảnh đại diện từ bảng product_images
    // (Bảng product_images là bảng bạn chụp hình có cột product_id và image_url)
    $sql = "SELECT p.*, 
            (SELECT image_url FROM product_images WHERE product_id = p.id LIMIT 1) as img_ref 
            FROM products p";
    
    // Xử lý tìm kiếm nếu có
    if (isset($_GET['search'])) {
        $sql .= " WHERE p.product_name LIKE :search OR p.name LIKE :search";
    }

    $stmt = $conn->prepare($sql);

    if (isset($_GET['search'])) {
        $stmt->execute([':search' => '%' . $_GET['search'] . '%']);
    } else {
        $stmt->execute();
    }

    $products = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // 1. Lấy ảnh từ bảng product_images (img_ref)
        $img = $row['img_ref'];

        // 2. Nếu bảng đó chưa có, tự tạo tên theo ID (Fallback)
        if (empty($img)) {
            $img = 'uploads/hinh' . $row['id'] . '.jpg';
        }

        // 3. Chuẩn hóa tên cột cho JS
        $products[] = [
            'id' => $row['id'],
            // Tự động tìm tên cột đúng (product_name hoặc name hoặc ten_sp)
            'product_name' => $row['product_name'] ?? $row['name'] ?? $row['ten_sp'] ?? 'Sản phẩm',
            'price' => $row['price'] ?? 0,
            'image_url' => $img, // Kết quả: uploads/hinhX.jpg
            'category_id' => $row['category_id'] ?? 0,
            'stock_quantity' => $row['stock_quantity'] ?? 0
        ];
    }

    echo json_encode($products);

} catch (PDOException $e) {
    echo json_encode(['error' => 'Lỗi: ' . $e->getMessage()]);
}
?>