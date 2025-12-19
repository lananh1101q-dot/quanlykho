<?php
$conn = mysqli_connect("localhost", "root", "", "quanlykho");
if (!$conn) die("Lỗi kết nối: " . mysqli_connect_error());

if (isset($_GET['Madm'])) {
    $Madm = $_GET['Madm'];

    // XÓA
    $sql = "DELETE FROM danhmucsp WHERE Madm = '$Madm'";

    if (mysqli_query($conn, $sql)) {
        header("Location: dmsp.php");
        exit;
    } else {
        echo "Lỗi khi xóa: " . mysqli_error($conn);
    }
} else {
    echo "Không tìm thấy mã danh mục!";
}
?>
