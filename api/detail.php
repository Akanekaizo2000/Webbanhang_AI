<?php
// Kết nối CSDL
include 'db.php';
header('Content-Type: application/json; charset=utf-8');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

try {
    if (!isset($conn)) {
        $conn = new PDO("mysql:host=localhost;dbname=quan_ly_ban_hang;charset=utf8", "root", "");
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    // 1. Lấy thông tin cơ bản sản phẩm
    $stmt = $conn->prepare("SELECT p.*, c.category_name 
                            FROM products p 
                            LEFT JOIN categories c ON p.category_id = c.id 
                            WHERE p.id = :id");
    $stmt->execute([':id' => $id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        echo json_encode(['error' => 'Sản phẩm không tồn tại']);
        exit;
    }

    // 2. Lấy danh sách ảnh từ bảng product_images (QUAN TRỌNG)
    // Dựa vào hình bạn gửi, bảng này có cột 'image_url' chứa sẵn 'uploads/...'
    $stmtImg = $conn->prepare("SELECT image_url FROM product_images WHERE product_id = :id");
    $stmtImg->execute([':id' => $id]);
    $images = $stmtImg->fetchAll(PDO::FETCH_ASSOC);

    // Nếu không có ảnh trong bảng product_images, thử tìm fallback ở bảng products
    // hoặc tạo ảnh mặc định hinh+id.jpg
    if (empty($images)) {
        // Kiểm tra xem bảng products có cột image_url hay hinh_anh không
        $fallbackImg = $product['image_url'] ?? $product['hinh_anh'] ?? $product['image'] ?? '';
        
        if (empty($fallbackImg)) {
             // Nếu database trống trơn, tự tạo tên ảnh theo ID
            $fallbackImg = 'hinh' . $id . '.jpg';
        }
        $images[] = ['image_url' => $fallbackImg];
    }

    // 3. Chuẩn hóa dữ liệu sản phẩm
    $stdProduct = [
        'id' => $product['id'],
        'product_name' => $product['product_name'] ?? $product['name'] ?? $product['ten_sp'],
        'price' => $product['price'] ?? 0,
        'category_name' => $product['category_name'] ?? '',
        'category_id' => $product['category_id'] ?? 0,
        'stock_quantity' => $product['stock_quantity'] ?? $product['quantity'] ?? 0,
        'description' => $product['description'] ?? $product['mo_ta'] ?? ''
    ];

    // 4. Lấy sản phẩm tương tự
    $similar = [];
    if (!empty($stdProduct['category_id'])) {
        // Lấy 4 sản phẩm cùng loại, kèm theo 1 ảnh đại diện từ bảng product_images
        $stmtSim = $conn->prepare("
            SELECT p.*, 
                   (SELECT image_url FROM product_images WHERE product_id = p.id LIMIT 1) as img_ref 
            FROM products p 
            WHERE category_id = :cid AND id != :id LIMIT 4
        ");
        $stmtSim->execute([':cid' => $stdProduct['category_id'], ':id' => $id]);
        
        while ($row = $stmtSim->fetch(PDO::FETCH_ASSOC)) {
            // Ưu tiên ảnh từ bảng product_images, nếu không có thì lấy ảnh fallback
            $sImg = $row['img_ref'] ?? ('hinh' . $row['id'] . '.jpg');
            
            $similar[] = [
                'id' => $row['id'],
                'product_name' => $row['product_name'] ?? $row['name'] ?? $row['ten_sp'],
                'price' => $row['price'],
                'image_url' => $sImg
            ];
        }
    }

    echo json_encode([
        'product' => $stdProduct,
        'images' => $images,
        'similar' => $similar
    ]);

} catch (PDOException $e) {
    echo json_encode(['error' => 'Lỗi: ' . $e->getMessage()]);
}
?>