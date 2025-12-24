<?php
$conn = mysqli_connect("localhost", "root", "", "quanlykho");

if (isset($_POST['taophieu'])) {
    $maxuathang = $_POST['maxuathang'];
    $makh = $_POST['makh'];
    $ngay = date('Y-m-d');

    mysqli_query($conn,
        "INSERT INTO Phieuxuat(Maxuathang, Makh, Ngayxuat, Tongtienxuat)
         VALUES ('$maxuathang','$makh','$ngay',0)");
}

if (isset($_POST['themsanpham'])) {
    $px = $_POST['maxuathang'];
    $masp = $_POST['masp'];
    $soluong = (int)$_POST['soluong'];

    $sp = mysqli_fetch_assoc(mysqli_query($conn,
        "SELECT Giaban FROM Sanpham WHERE Masp='$masp'"));
    $dongia = $sp['Giaban'];
    $thanhtien = $soluong * $dongia;

    mysqli_query($conn,
        "INSERT INTO Chitiet_Phieuxuat(Maxuathang,Masp,Soluong,Dongiaxuat)
         VALUES('$px','$masp','$soluong','$dongia')");

    mysqli_query($conn,
        "UPDATE Tonkho SET Soluongton = Soluongton - $soluong WHERE Masp='$masp'");

    mysqli_query($conn,
        "UPDATE Phieuxuat 
         SET Tongtienxuat = Tongtienxuat + $thanhtien
         WHERE Maxuathang='$px'");
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Phiếu xuất</title>
</head>
<body>
<h2>Phiếu xuất bán hàng</h2>

<form method="post">
    Mã PX <input name="maxuathang" required>
    Mã KH <input name="makh" required>
    <button name="taophieu">Tạo phiếu</button>
</form>

<hr>

<form method="post">
    Mã PX <input name="maxuathang" required>
    Mã SP <input name="masp" required>
    Số lượng <input type="number" name="soluong" required>
    <button name="themsanpham">Thêm sản phẩm</button>
</form>

</body>
</html>
