<?php
// 1. Kết nối cơ sở dữ liệu
include 'db.php';

// 2. Nhận các tham số từ phía giao diện (Frontend)
$page = $_GET['page'] ?? 1;          // Trang hiện tại, mặc định là 1
$limit = 5;                          // Số bản ghi trên mỗi trang
$offset = ($page - 1) * $limit;      // Vị trí bắt đầu lấy dữ liệu
$search = $_GET['search'] ?? '';     // Từ khóa tìm kiếm username

try {
    // --- BƯỚC 1: TÍNH TỔNG SỐ TRANG ---
    // Câu lệnh đếm tổng số dòng thỏa mãn điều kiện tìm kiếm
    $countSql = "SELECT COUNT(*) FROM users";
    if ($search) {
        $countSql .= " WHERE username LIKE :s";
    }
    
    $stmtCount = $conn->prepare($countSql);
    if ($search) {
        $stmtCount->execute([':s' => "%$search%"]);
    } else {
        $stmtCount->execute();
    }
    
    $totalRows = $stmtCount->fetchColumn();
    $totalPages = ceil($totalRows / $limit); // Tính tổng số trang

    // --- BƯỚC 2: LẤY DỮ LIỆU CỦA TRANG HIỆN TẠI ---
    // Kết nối với bảng roles để lấy tên vai trò
    $sql = "SELECT u.id, u.username, u.role_id, r.role_name 
            FROM users u 
            LEFT JOIN roles r ON u.role_id = r.id";
    
    if ($search) {
        $sql .= " WHERE u.username LIKE :s";
    }
    
    // Thêm LIMIT và OFFSET để phân trang
    $sql .= " LIMIT $limit OFFSET $offset";

    $stmtData = $conn->prepare($sql);
    if ($search) {
        $stmtData->execute([':s' => "%$search%"]);
    } else {
        $stmtData->execute();
    }
    
    $users = $stmtData->fetchAll(PDO::FETCH_ASSOC);

    // 3. Trả về kết quả JSON cho AJAX xử lý
    echo json_encode([
        'status' => 'success',
        'users' => $users,
        'totalPages' => $totalPages,
        'currentPage' => (int)$page
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Lỗi truy vấn: ' . $e->getMessage()
    ]);
}
?>