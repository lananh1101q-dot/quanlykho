<?php
$conn = mysqli_connect("localhost", "root", "", "quanlykho");
if (!$conn) die("Lỗi kết nối: " . mysqli_connect_error());

if (isset($_GET['Masp'])) {
    $Masp = $_GET['Masp'];

    // XÓA
    $sql = "DELETE FROM Sanpham WHERE Masp = '$Masp'";
    if (mysqli_query($conn, $sql)) {
        header("Location: Sanpham.php");
        exit;
    } else {
        echo "Lỗi khi xóa: " . mysqli_error($conn);
    }
} else {
    echo "Không tìm thấy mã danh mục!";
}
?>
