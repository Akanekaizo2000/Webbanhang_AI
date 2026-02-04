<?php
session_start();
header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

if ($action == 'add') {
    $id = $_POST['id'];
    if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

    if (isset($_SESSION['cart'][$id])) {
        $_SESSION['cart'][$id]['quantity'] += 1;
    } else {
        $_SESSION['cart'][$id] = [
            'name' => $_POST['name'],
            'price' => $_POST['price'],
            'quantity' => 1
        ];
    }
    echo json_encode(['status' => 'success']);

} elseif ($action == 'view') {
    echo json_encode($_SESSION['cart'] ?? []);

} elseif ($action == 'clear') {
    unset($_SESSION['cart']);
    echo json_encode(['status' => 'cleared']);
}
?>