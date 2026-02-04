<?php
include 'db.php';

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '123456'; 
$role_id = $_POST['role_id'] ?? 2;

$hashed_password = password_hash($password, PASSWORD_DEFAULT);

try {
    // Câu lệnh SQL không còn cột email
    $sql = "INSERT INTO users (username, password, role_id) VALUES (:username, :password, :role_id)";
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':username' => $username,
        ':password' => $hashed_password,
        ':role_id' => $role_id
    ]);
    echo json_encode(["status" => "success", "message" => "Thêm người dùng thành công!"]);
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>