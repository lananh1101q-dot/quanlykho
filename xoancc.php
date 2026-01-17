<?php
$conn = mysqli_connect("localhost", "root", "", "quanlykho");
if (!$conn) die("Lỗi kết nối: " . mysqli_connect_error());

if (isset($_GET['Mancc'])) {
    $Mancc = mysqli_real_escape_string($conn, $_GET['Mancc']);

    // 1️⃣ KIỂM TRA NHÀ CUNG CẤP CÓ ĐANG ĐƯỢC DÙNG KHÔNG
    // ⚠️ Đổi bảng bên dưới nếu bạn dùng bảng khác (ví dụ: phieunhap)
    $sqlCheck = "SELECT COUNT(*) AS total FROM Phieunhap WHERE Mancc = '$Mancc'";
    $rs = mysqli_query($conn, $sqlCheck);
    $row = mysqli_fetch_assoc($rs);

    if ($row['total'] > 0) {
        echo "<script>
            alert('Không thể xóa! Nhà cung cấp đang được sử dụng.');
            window.location.href='Nhacungcap.php';
        </script>";
        exit;
    }

    // 2️⃣ KHÔNG BỊ DÙNG → CHO XÓA
    $sqlDelete = "DELETE FROM Nhacungcap WHERE Mancc = '$Mancc'";

    if (mysqli_query($conn, $sqlDelete)) {
        header("Location: Nhacungcap.php");
        exit;
    } else {
        echo "Lỗi khi xóa: " . mysqli_error($conn);
    }
} else {
    echo "Không tìm thấy mã nhà cung cấp!";
}
?>
