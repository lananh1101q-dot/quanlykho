<?php
$conn = mysqli_connect("localhost", "root", "", "quanlykho");

$sql = "
SELECT 
    px.Maxuathang,
    kh.Tenkh,
    px.Tongtienxuat,
    IFNULL(SUM(tt.Sotienthanhtoan),0) AS Dathanhtoan,
    px.Tongtienxuat - IFNULL(SUM(tt.Sotienthanhtoan),0) AS Conno
FROM Phieuxuat px
JOIN Khachhang kh ON px.Makh = kh.Makh
LEFT JOIN Thanhtoan tt ON px.Maxuathang = tt.Maxuathang
GROUP BY px.Maxuathang
ORDER BY px.Ngayxuat DESC
";

$list = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Báo cáo bán hàng</title>
</head>
<body>

<h2>Báo cáo bán hàng & lịch sử thanh toán</h2>

<table border="1" cellpadding="5">
<tr>
    <th>Mã PX</th>
    <th>Khách hàng</th>
    <th>Tổng tiền</th>
    <th>Đã trả</th>
    <th>Còn nợ</th>
</tr>

<?php while ($row = mysqli_fetch_assoc($list)) { ?>
<tr>
    <td><?= $row['Maxuathang'] ?></td>
    <td><?= $row['Tenkh'] ?></td>
    <td><?= number_format($row['Tongtienxuat'],0,',','.') ?></td>
    <td><?= number_format($row['Dathanhtoan'],0,',','.') ?></td>
    <td><?= number_format($row['Conno'],0,',','.') ?></td>
</tr>
<?php } ?>

</table>

</body>
</html>
