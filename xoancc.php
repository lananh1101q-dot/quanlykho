<?php
$conn = mysqli_connect("localhost", "root", "", "quanlykho");
if (!$conn) die("Lỗi kết nối: " . mysqli_connect_error());

if (isset($_GET['Mancc'])) {
    $Mancc = $_GET['Mancc'];

    // XÓA
    $sql = "DELETE FROM Nhacungcap WHERE Mancc = '$Mancc'";
    if (mysqli_query($conn, $sql)) {
        header("Location: Nhacungcap.php");
        exit;
    } else {
        echo "Lỗi khi xóa: " . mysqli_error($conn);
    }
} else {
    echo "Không tìm thấy mã danh mục!";
}
?>
