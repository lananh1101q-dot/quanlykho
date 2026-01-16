<?php
session_start();
require_once __DIR__ . '/db.php';

if (!isset($_SESSION['user']) || !isset($_GET['id'])) {
    header("Location: loaikhachhang.php");
    exit;
}

$id = $_GET['id'];

// Kiểm tra xem loại này có đang được sử dụng trong bảng khachhang không
$stmt = $pdo->prepare("SELECT COUNT(*) FROM khachhang WHERE Maloaikh = ?");
$stmt->execute([$id]);
$count = $stmt->fetchColumn();

if ($count > 0) {
    // Nếu đang dùng thì không cho xóa
    header("Location: loaikhachhang.php?error=Loại khách hàng này đang được sử dụng, không thể xóa!");
    exit;
}

// Nếu không dùng thì xóa
$stmt = $pdo->prepare("DELETE FROM loaikhachhang WHERE Maloaikh = ?");
$stmt->execute([$id]);

header("Location: loaikhachhang.php?success=Xóa loại khách hàng thành công!");
exit;
?>