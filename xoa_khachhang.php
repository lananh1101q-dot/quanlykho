<?php
session_start();
require_once __DIR__ . '/db.php';

if (!isset($_SESSION['user'])) {
    header("Location: dangnhap.php");
    exit;
}

if (isset($_GET['id'])) {
    $makh = $_GET['id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM khachhang WHERE Makh = ?");
        $stmt->execute([$makh]);
        // Nếu xóa thành công → không làm gì, chuyển hướng bình thường
    } catch (PDOException $e) {
        // Nếu có lỗi (thường là do khách hàng đã có hóa đơn, công nợ... nên không xóa được)
        // Không hiển thị gì, chỉ bỏ qua lỗi và vẫn chuyển hướng về danh sách
    }
}

header("Location: khachhang.php");
exit;