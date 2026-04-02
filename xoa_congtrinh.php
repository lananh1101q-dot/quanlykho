<?php
session_start();
require_once __DIR__ . '/db.php';

// 1. Kiểm tra đăng nhập
if (!isset($_SESSION['user'])) {
    header("Location: dangnhap.php");
    exit;
}

// 2. Kiểm tra nếu có ID (Mã công trình) truyền qua URL
if (isset($_GET['id'])) {
    $mact = $_GET['id'];

    try {
        // Thực hiện câu lệnh xóa
        $sql = "DELETE FROM congtrinh WHERE Mact = ?";
        $stmt = $pdo->prepare($sql);
        
        if ($stmt->execute([$mact])) {
            // Hiện thông báo thành công bằng Javascript và quay lại trang danh sách
            echo "<script>
                    alert('Đã xóa công trình thành công!');
                    window.location.href = 'congtrinh.php';
                  </script>";
        } else {
            echo "<script>
                    alert('Có lỗi xảy ra, không thể xóa công trình.');
                    window.location.href = 'congtrinh.php';
                  </script>";
        }
    } catch (PDOException $e) {
        // Trường hợp bị lỗi ràng buộc dữ liệu (ví dụ công trình đã có phiếu xuất)
        echo "<script>
                alert('Lỗi: Không thể xóa công trình này do đang có dữ liệu liên quan!');
                window.location.href = 'congtrinh.php';
              </script>";
    }
} else {
    // Nếu không có ID thì quay lại trang danh sách luôn
    header("Location: congtrinh.php");
    exit;
}
?>