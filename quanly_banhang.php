<?php
$conn = new mysqli("localhost", "root", "", "quanlykho");
$conn->set_charset("utf8");
if ($conn->connect_error) die("Lỗi kết nối CSDL");

// ================= XỬ LÝ XÓA =================
if (isset($_GET['xoa_hd'])) {
    $id = $_GET['xoa_hd'];
    $conn->query("DELETE FROM Phieuxuat WHERE Maxuathang='$id'");
    header("Location: quanly_banhang.php");
}

if (isset($_GET['xoa_tt'])) {
    $id = $_GET['xoa_tt'];
    $conn->query("DELETE FROM Thanhtoan WHERE Matt='$id'");
    header("Location: quanly_banhang.php");
}

// ================= THÊM HÓA ĐƠN =================
if (isset($_POST['them_hd'])) {
    $conn->query("
        INSERT INTO Phieuxuat(Maxuathang, Makh, Ngayxuat, Tongtienxuat)
        VALUES('{$_POST['mahd']}', '{$_POST['makh']}', '{$_POST['ngay']}', '{$_POST['tongtien']}')
    ");
}

// ================= THÊM THANH TOÁN =================
if (isset($_POST['them_tt'])) {
    $conn->query("
        INSERT INTO Thanhtoan(Maxuathang, Ngaythanhtoan, Sotienthanhtoan, Hinhthuc)
        VALUES('{$_POST['mahd']}', '{$_POST['ngaytt']}', '{$_POST['sotien']}', '{$_POST['hinhthuc']}')
    ");
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Quản lý bán hàng</title>
<link rel="stylesheet" href="quanly_banhang.css">

<style>
body{font-family:Arial;margin:20px}
table{border-collapse:collapse;width:100%}
th,td{border:1px solid #ccc;padding:6px;text-align:center}
th{background:#eee}
form{margin:10px 0}
h2,h3{color:#2c3e50}
a{color:red;text-decoration:none}
input{padding:5px}
</style>
</head>
<body>

<h2>📊 QUẢN LÝ BÁN HÀNG</h2>

<!-- ================= HÓA ĐƠN ================= -->
<h3>🧾 Hóa đơn bán</h3>

<form method="get">
    <input name="tim_hd" placeholder="Tìm mã HĐ / khách hàng">
    <button>Tìm</button>
</form>

<table>
<tr>
<th>Mã HĐ</th><th>Ngày</th><th>Khách</th><th>Tổng tiền</th><th>Xóa</th>
</tr>

<?php
$tim = $_GET['tim_hd'] ?? '';
$sql = "
SELECT px.*, kh.Tenkh FROM Phieuxuat px
LEFT JOIN Khachhang kh ON px.Makh=kh.Makh
WHERE px.Maxuathang LIKE '%$tim%' OR kh.Tenkh LIKE '%$tim%'
";
$res = $conn->query($sql);
while ($r = $res->fetch_assoc()) {
echo "<tr>
<td>{$r['Maxuathang']}</td>
<td>{$r['Ngayxuat']}</td>
<td>{$r['Tenkh']}</td>
<td>".number_format($r['Tongtienxuat'])."</td>
<td><a href='?xoa_hd={$r['Maxuathang']}'>Xóa</a></td>
</tr>";
}
?>
</table>

<h4>➕ Thêm hóa đơn</h4>
<form method="post">
<input name="mahd" placeholder="Mã HĐ" required>
<input name="makh" placeholder="Mã KH">
<input type="date" name="ngay">
<input name="tongtien" placeholder="Tổng tiền">
<button name="them_hd">Thêm</button>
</form>

<!-- ================= THANH TOÁN ================= -->
<h3>💳 Lịch sử thanh toán</h3>

<form method="get">
<input name="tim_tt" placeholder="Tìm mã HĐ">
<button>Tìm</button>
</form>

<table>
<tr>
<th>ID</th><th>Mã HĐ</th><th>Ngày</th><th>Số tiền</th><th>Hình thức</th><th>Xóa</th>
</tr>

<?php
$timtt = $_GET['tim_tt'] ?? '';
$res = $conn->query("
SELECT * FROM Thanhtoan 
WHERE Maxuathang LIKE '%$timtt%'
");
while ($r = $res->fetch_assoc()) {
echo "<tr>
<td>{$r['Matt']}</td>
<td>{$r['Maxuathang']}</td>
<td>{$r['Ngaythanhtoan']}</td>
<td>".number_format($r['Sotienthanhtoan'])."</td>
<td>{$r['Hinhthuc']}</td>
<td><a href='?xoa_tt={$r['Matt']}'>Xóa</a></td>
</tr>";
}
?>
</table>

<h4>➕ Thêm thanh toán</h4>
<form method="post">
<input name="mahd" placeholder="Mã HĐ" required>
<input type="date" name="ngaytt">
<input name="sotien" placeholder="Số tiền">
<input name="hinhthuc" placeholder="Tiền mặt / CK">
<button name="them_tt">Thêm</button>
</form>

</body>
</html>
