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
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
       <style>
        body { 
            background-color: #f8f9fa; 
            font-family: 'Segoe UI', sans-serif; 
        }
        
        /* Sidebar */
        .sidebar { 
            background-color: #007bff; 
            height: 100vh; 
            position: fixed; 
            width: 250px; 
            color: white; 
            padding-top: 20px; 
            top: 0;
            left: 0;
            overflow-y: auto;
        }
        
        .sidebar .nav-link {
            color: white !important;
            padding: 12px 20px;
            border-radius: 5px;
            margin: 4px 10px;
            transition: all 0.3s ease;
            font-weight: normal; /* Chữ bình thường mặc định */
        }
        
        /* CHỈ hover mới in đậm và nổi bật */
        .sidebar .nav-link:hover {
            background-color: #0069d9;    /* Nền xanh đậm hơn một chút */
            font-weight: bold;            /* Chữ in đậm */
            transform: translateX(8px);   /* Dịch nhẹ sang phải cho đẹp */
        }
        
        /* Bỏ hoàn toàn style active - tất cả đều giống nhau */
        .sidebar .nav-link.active {
            background-color: transparent;
            font-weight: normal;
            transform: none;
        }
        
        .main-content { 
            margin-left: 250px; 
            padding: 20px; 
        }
        @media (max-width: 768px) { 
            .sidebar { 
                width: 100%; 
                height: auto; 
                position: relative; 
            } 
            .main-content { 
                margin-left: 0; 
            } 
        }
         /* tránh ghi đè */
        .d-none {
            display: none !important;
        }
        #submenuSanPham {
            transition: all 0.3s ease;
        }
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
    <nav class="sidebar">
        <div class="text-center mb-4">
            <h4><i class="fas fa-warehouse"></i> Quản Lý Kho</h4>
          </div>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link" href="trangchu.php"><i class="fas fa-home"></i> Trang Chủ</a>
            </li>
           <li class="nav-item">
                <a class="nav-link" href="javascript:void(0)" id="btnSanPham">
                    <i class="fas fa-box"></i> Quản lý sản phẩm
                    <i class="fas fa-chevron-down float-end"></i>
                </a>

                <ul class="nav flex-column ms-3 d-none" id="submenuSanPham">
                    <li class="nav-item">
                        <a class="nav-link" href="Sanpham.php">
                            <i class="fas fa-cube"></i> Sản phẩm
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="dmsp.php">
                            <i class="fas fa-tags"></i> Danh mục sản phẩm
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="Nhacungcap.php">
                            <i class="fas fa-truck"></i> Nhà cung cấp
                        </a>
                    </li>
                </ul>
            </li>


            <li class="nav-item">
              <a class="nav-link" href="javascript:void(0)" id="btnPhieuNhap">
                  <i class="fas fa-file-import"></i> Phiếu nhập kho
                  <i class="fas fa-chevron-down float-end"></i>
              </a>

              <ul class="nav flex-column ms-3 d-none" id="submenuPhieuNhap">
                  <li class="nav-item">
                      <a class="nav-link" href="danh_sach_phieu_nhap.php">
                          <i class="fas fa-list"></i> Danh sách phiếu nhập
                      </a>
                  </li>
                  <li class="nav-item">
                      <a class="nav-link" href="phieu_nhap.php">
                          <i class="fas fa-plus-circle"></i> Tạo phiếu nhập
                      </a>
                  </li>
              </ul>
          </li>
            <li class="nav-item">
                <a class="nav-link" href="javascript:void(0)" id="btnBaoCao">
                    <i class="fas fa-chart-bar"></i> Báo cáo & Thống kê
                    <i class="fas fa-chevron-down float-end"></i>
                </a>

                <ul class="nav flex-column ms-3 d-none" id="submenuBaoCao">
                    <li class="nav-item">
                        <a class="nav-link" href="quanly_banhang.php">
                            <i class="fas fa-cash-register"></i> Báo cáo bán hàng
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="tonkho.php">
                            <i class="fas fa-warehouse"></i> Báo cáo tồn kho
                        </a>
                    </li>
                  
                </ul>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="khachhang.php"><i class="fas fa-users"></i> Khách hàng</a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-danger" href="logout.php"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a>
            </li>
        </ul>
    </nav>

    <div class="main-content">
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
</div>
<script>
document.getElementById("btnSanPham").addEventListener("click", function () {
    const menu = document.getElementById("submenuSanPham");
    menu.classList.toggle("d-none");
    
});
document.getElementById("btnBaoCao").addEventListener("click", function () {
    document.getElementById("submenuBaoCao").classList.toggle("d-none");
});
const btnPhieuNhap = document.getElementById("btnPhieuNhap");
const submenuPhieuNhap = document.getElementById("submenuPhieuNhap");

if (btnPhieuNhap) {
    btnPhieuNhap.addEventListener("click", function () {
        submenuPhieuNhap.classList.toggle("d-none");
    });
}

</script>
</body>
</html>
