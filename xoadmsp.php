<?php
$conn = mysqli_connect("localhost", "root", "", "quanlykho");
if (!$conn) die("Lỗi kết nối: " . mysqli_connect_error());

if (isset($_GET['Madm'])) {
    $Madm = mysqli_real_escape_string($conn, $_GET['Madm']);

    // 1️⃣ KIỂM TRA DANH MỤC CÓ SẢN PHẨM KHÔNG
    $sqlCheck = "SELECT COUNT(*) AS total FROM Sanpham WHERE Madm = '$Madm'";
    $rs = mysqli_query($conn, $sqlCheck);
    $row = mysqli_fetch_assoc($rs);

    if ($row['total'] > 0) {
        echo "<script>
            alert('Không thể xóa! Danh mục đang có sản phẩm.');
            window.location.href='dmsp.php';
        </script>";
        exit;
    }

    // 2️⃣ KHÔNG CÓ SẢN PHẨM → CHO XÓA
    $sqlDelete = "DELETE FROM danhmucsp WHERE Madm = '$Madm'";

    if (mysqli_query($conn, $sqlDelete)) {
        header("Location: dmsp.php");
        exit;
    } else {
        echo "Lỗi khi xóa: " . mysqli_error($conn);
    }
} else {
    echo "Không tìm thấy mã danh mục!";
}
?>
