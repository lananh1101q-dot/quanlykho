<?php
$conn = mysqli_connect("localhost", "root", "", "quanlykho");
if (!$conn) die("Lỗi kết nối: " . mysqli_connect_error());

if (isset($_GET['Masp'])) {
    $Masp = mysqli_real_escape_string($conn, $_GET['Masp']);

    // 1️⃣ KIỂM TRA SẢN PHẨM ĐÃ PHÁT SINH GIAO DỊCH CHƯA
    $sqlCheckNhap = "SELECT COUNT(*) AS total FROM Chitiet_Phieunhap WHERE Masp = '$Masp'";
    $rsNhap = mysqli_query($conn, $sqlCheckNhap);
    $rowNhap = mysqli_fetch_assoc($rsNhap);

    $sqlCheckXuat = "SELECT COUNT(*) AS total FROM Chitiet_phieuxuat WHERE Masp = '$Masp'";
    $rsXuat = mysqli_query($conn, $sqlCheckXuat);
    $rowXuat = mysqli_fetch_assoc($rsXuat);

    if ($rowNhap['total'] > 0 || $rowXuat['total'] > 0) {
        echo "<script>
            alert('Không thể xóa! Sản phẩm đã phát sinh nhập / xuất kho.');
            window.location.href='Sanpham.php';
        </script>";
        exit;
    }

    // 2️⃣ CHƯA PHÁT SINH → CHO XÓA
    $sqlDelete = "DELETE FROM Sanpham WHERE Masp = '$Masp'";

    if (mysqli_query($conn, $sqlDelete)) {
        header("Location: Sanpham.php");
        exit;
    } else {
        echo "Lỗi khi xóa: " . mysqli_error($conn);
    }
} else {
    echo "Không tìm thấy mã sản phẩm!";
}
?>
