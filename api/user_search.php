<?php
// Kết nối cơ sở dữ liệu
include 'db.php';

// Lấy từ khóa tìm kiếm từ phương thức GET
$keyword = $_GET['keyword'] ?? '';

try {
    // Chuẩn bị câu lệnh SQL tìm kiếm theo username (đã bỏ email)
    $sql = "SELECT u.id, u.username, u.role_id, r.role_name 
            FROM users u 
            LEFT JOIN roles r ON u.role_id = r.id";
    
    if (!empty($keyword)) {
        $sql .= " WHERE u.username LIKE :keyword";
    }

    $stmt = $conn->prepare($sql);

    if (!empty($keyword)) {
        $stmt->execute([':keyword' => "%$keyword%"]);
    } else {
        $stmt->execute();
    }

    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Trả về kết quả dưới dạng JSON
    echo json_encode([
        "status" => "success",
        "data" => $results
    ]);

} catch (PDOException $e) {
    echo json_encode([
        "status" => "error", 
        "message" => $e->getMessage()
    ]);
}
?>