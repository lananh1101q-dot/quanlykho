<?php
$conn = mysqli_connect("localhost", "root", "", "quanlykho");

if (isset($_POST['thanhtoan'])) {
    $px = $_POST['maxuathang'];
    $ngay = $_POST['ngay'];
    $sotien = $_POST['sotien'];
    $hinhthuc = $_POST['hinhthuc'];

    mysqli_query($conn,
        "INSERT INTO Thanhtoan(Maxuathang,Ngaythanhtoan,Sotienthanhtoan,Hinhthuc)
         VALUES('$px','$ngay','$sotien','$hinhthuc')");
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Thanh toán</title>
</head>
<body>

<h2>Thanh toán hóa đơn</h2>

<form method="post">
    Mã PX <input name="maxuathang" required><br>
    Ngày <input type="date" name="ngay" required><br>
    Số tiền <input type="number" name="sotien" required><br>
    Hình thức <input name="hinhthuc"><br>
    <button name="thanhtoan">Thanh toán</button>
</form>

</body>
</html>
