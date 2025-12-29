<?php
session_start();
require_once __DIR__ . '/db.php';
$message = "";

/* ========== LẤY THANH TOÁN CẦN SỬA ========== */
$edit_tt = null;
if (isset($_GET['sua_tt'])) {
    $stmt = $pdo->prepare("SELECT * FROM Thanhtoan WHERE Matt=?");
    $stmt->execute([$_GET['sua_tt']]);
    $edit_tt = $stmt->fetch();
}

/* ========== CẬP NHẬT THANH TOÁN ========== */
if (isset($_POST['capnhat_tt'])) {
    $stmt = $pdo->prepare(
        "UPDATE Thanhtoan
         SET Maxuathang=?, Ngaythanhtoan=?, Sotienthanhtoan=?, Hinhthuc=?
         WHERE Matt=?"
    );
    $stmt->execute([
        $_POST['mahd_tt'],
        $_POST['ngaytt'],
        $_POST['sotien'],
        $_POST['hinhthuc'],
        $_POST['matt']
    ]);
    header("Location: quanly_banhang.php");
    exit;
}

/* ========== XÓA CHI TIẾT BÁN ========== */
if (isset($_GET['xoa_ct'])) {
    $stmt = $pdo->prepare("DELETE FROM Chitiet_Phieuxuat WHERE Id=?");
    $stmt->execute([$_GET['xoa_ct']]);
    header("Location: quanly_banhang.php");
    exit;
}

/* ========== SỬA CHI TIẾT BÁN ========== */
$edit_ct = null;
if (isset($_GET['sua_ct'])) {
    $stmt = $pdo->prepare("SELECT * FROM Chitiet_Phieuxuat WHERE Id=?");
    $stmt->execute([$_GET['sua_ct']]);
    $edit_ct = $stmt->fetch();
}

/* ========== CẬP NHẬT CHI TIẾT BÁN ========== */
if (isset($_POST['capnhat_ct'])) {
    $stmt = $pdo->prepare(
        "UPDATE Chitiet_Phieuxuat
         SET Maxuathang=?, Masp=?, Soluong=?, Dongiaxuat=?
         WHERE Id=?"
    );
    $stmt->execute([
        $_POST['maxuat'],
        $_POST['masp'],
        $_POST['soluong'],
        $_POST['dongia'],
        $_POST['id']
    ]);
    header("Location: quanly_banhang.php");
    exit;
}

/* ========== THÊM CHI TIẾT BÁN ========== */
if (isset($_POST['them_ct'])) {
    $stmt = $pdo->prepare(
        "INSERT INTO Chitiet_Phieuxuat(Maxuathang, Masp, Soluong, Dongiaxuat)
         VALUES (?,?,?,?)"
    );
    $stmt->execute([
        $_POST['maxuat'],
        $_POST['masp'],
        $_POST['soluong'],
        $_POST['dongia']
    ]);
}

/* ========== XÓA PHIẾU XUẤT ========== */
if (isset($_GET['xoa_px'])) {
    $stmt = $pdo->prepare("DELETE FROM Phieuxuat WHERE Maxuathang=?");
    $stmt->execute([$_GET['xoa_px']]);
    header("Location: quanly_banhang.php");
    exit;
}

/* ========== XÓA THANH TOÁN ========== */
if (isset($_GET['xoa_tt'])) {
    $stmt = $pdo->prepare("DELETE FROM Thanhtoan WHERE Matt=?");
    $stmt->execute([$_GET['xoa_tt']]);
    header("Location: quanly_banhang.php");
    exit;
}
// ====== LẤY DANH SÁCH KHÁCH ======
$dsKhach = $pdo->query("SELECT Makh, Tenkh FROM Khachhang")->fetchAll();
/* ========== LẤY PHIẾU XUẤT CẦN SỬA ========== */
$edit_px = null;
if (isset($_GET['sua_px'])) {
    $stmt = $pdo->prepare("SELECT * FROM Phieuxuat WHERE Maxuathang=?");
    $stmt->execute([$_GET['sua_px']]);
    $edit_px = $stmt->fetch();
}

/* ========== CẬP NHẬT PHIẾU XUẤT ========== */
if (isset($_POST['capnhat_px'])) {
    $stmt = $pdo->prepare(
        "UPDATE Phieuxuat
         SET Makh=?, Ngayxuat=?, Tongtienxuat=?
         WHERE Maxuathang=?"
    );
    $stmt->execute([
        $_POST['makh'],
        $_POST['ngayxuat'],
        $_POST['tongtien'],
        $_POST['maxuat']
    ]);
    header("Location: quanly_banhang.php");
    exit;
}


/* ========== THÊM PHIẾU XUẤT ========== */
if (isset($_POST['them_px'])) {
    try {
        $stmt = $pdo->prepare(
            "INSERT INTO Phieuxuat(Maxuathang, Makh, Ngayxuat, Tongtienxuat)
             VALUES (?, ?, ?, ?)"
        );
        $stmt->execute([
            $_POST['maxuat'],
            $_POST['makh'],
            $_POST['ngayxuat'],
            $_POST['tongtien']
        ]);
        $message = "✔ Thêm phiếu xuất thành công";
    } catch (PDOException $e) {
        $message = "❌ Lỗi: " . $e->getMessage();
    }
}

/* ========== THÊM THANH TOÁN ========== */
if (isset($_POST['them_tt'])) {
    try {
        $stmt = $pdo->prepare(
            "INSERT INTO Thanhtoan(Maxuathang, Ngaythanhtoan, Sotienthanhtoan, Hinhthuc)
             VALUES (?, ?, ?, ?)"
        );
        $stmt->execute([
            $_POST['mahd_tt'],
            $_POST['ngaytt'],
            $_POST['sotien'],
            $_POST['hinhthuc']
        ]);
        $message = "✔ Thêm thanh toán thành công";
    } catch (PDOException $e) {
        $message = "❌ Lỗi: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
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


<!-- ================= MAIN ================= -->

<h2 class="mb-3"> Quản Lý Bán Hàng</h2>

<?php if ($message): ?>
<div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<!-- ===== PHIẾU XUẤT ===== -->
<div class="card mb-4">
<div class="card-header fw-bold"> Phiếu xuất / Hóa đơn</div>
<div class="card-body">

<form method="get" class="mb-3 d-flex">
<input name="tim_px" class="form-control me-2" placeholder="Tìm mã phiếu / khách hàng">
<button class="btn btn-primary">Tìm</button>
</form>

<table class="table table-bordered">
<tr class="table-light">
<th>Mã</th><th>Ngày</th><th>Khách</th><th>Tổng tiền</th><th>Sửa</th><th>Xóa</th>

</tr>

<?php
$tim = $_GET['tim_px'] ?? '';
$stmt = $pdo->prepare(
"SELECT px.*, kh.Tenkh FROM Phieuxuat px
 LEFT JOIN Khachhang kh ON px.Makh=kh.Makh
 WHERE px.Maxuathang LIKE ? OR kh.Tenkh LIKE ?"
);
$stmt->execute(["%$tim%","%$tim%"]);
foreach ($stmt as $r):
?>
<tr>
<td><?= $r['Maxuathang'] ?></td>
<td><?= $r['Ngayxuat'] ?></td>
<td><?= $r['Tenkh'] ?></td>
<td><?= number_format($r['Tongtienxuat']) ?></td>
<td>
<a class="btn btn-sm btn-warning"
href="?sua_px=<?= $r['Maxuathang'] ?>">Sửa</a>
</td>
<td>
<a class="btn btn-sm btn-danger"
onclick="return confirm('Xóa phiếu xuất?')"
href="?xoa_px=<?= $r['Maxuathang'] ?>">Xóa</a>
</td>

</tr>
<?php endforeach; ?>
</table>

<h5 class="mt-3"> Thêm phiếu xuất</h5>
<form method="post" class="row g-2">
<input class="col-md-3 form-control" name="maxuat"
value="<?= $edit_px['Maxuathang'] ?? '' ?>"
<?= $edit_px ? 'readonly' : '' ?>
placeholder="Mã phiếu" required>

<select class="col-md-3 form-select" name="makh" required>
    <option value="">Mã KH</option>
    <?php foreach ($dsKhach as $kh): ?>
        <option value="<?= $kh['Makh'] ?>"
            <?= (isset($edit_px) && $edit_px && $edit_px['Makh'] == $kh['Makh']) ? 'selected' : '' ?>>
            <?= $kh['Makh'] ?> - <?= $kh['Tenkh'] ?>
        </option>
    <?php endforeach; ?>
</select>



<input class="col-md-3 form-control" type="date" name="ngayxuat"
value="<?= $edit_px['Ngayxuat'] ?? '' ?>">

<input class="col-md-2 form-control" name="tongtien"
value="<?= $edit_px['Tongtienxuat'] ?? '' ?>"
placeholder="Tổng tiền">

<button class="col-md-1 btn btn-success"
name="<?= $edit_px ? 'capnhat_px' : 'them_px' ?>">
<?= $edit_px ? 'Lưu' : 'Thêm' ?>
</button>

</form>

</div>
</div>

<!-- ===== THANH TOÁN ===== -->
<div class="card">
<div class="card-header fw-bold"> Lịch sử thanh toán</div>
<div class="card-body">

<form method="get" class="mb-3 d-flex">
<input name="tim_tt" class="form-control me-2" placeholder="Tìm mã hóa đơn">
<button class="btn btn-primary">Tìm</button>
</form>

<table class="table table-bordered">
<tr class="table-light">
<th>ID</th><th>Mã HĐ</th><th>Ngày</th><th>Số tiền</th><th>Hình thức</th><th>Sửa</th><th>Xóa</th>
</tr>

<?php
$timtt = $_GET['tim_tt'] ?? '';
$stmt = $pdo->prepare("SELECT * FROM Thanhtoan WHERE Maxuathang LIKE ?");
$stmt->execute(["%$timtt%"]);
foreach ($stmt as $r):
?>
<tr>
<td><?= $r['Matt'] ?></td>
<td><?= $r['Maxuathang'] ?></td>
<td><?= $r['Ngaythanhtoan'] ?></td>
<td><?= number_format($r['Sotienthanhtoan']) ?></td>
<td><?= $r['Hinhthuc'] ?></td>
<td>
    <a class="btn btn-sm btn-warning"
       href="?sua_tt=<?= $r['Matt'] ?>">Sửa</a>
</td>
<td>
<a class="btn btn-sm btn-danger"
onclick="return confirm('Xóa thanh toán?')"
href="?xoa_tt=<?= $r['Matt'] ?>">Xóa</a>
</td>

</tr>
<?php endforeach; ?>
</table>

<h5 class="mt-3"> Thêm thanh toán</h5>
<form method="post" class="row g-2">

<input type="hidden" name="matt"
value="<?= $edit_tt['Matt'] ?? '' ?>">

<input class="col-md-3 form-control" name="mahd_tt"
value="<?= $edit_tt['Maxuathang'] ?? '' ?>"
placeholder="Mã HĐ" required>

<input class="col-md-3 form-control" type="date" name="ngaytt"
value="<?= $edit_tt['Ngaythanhtoan'] ?? '' ?>">

<input class="col-md-3 form-control" name="sotien"
value="<?= $edit_tt['Sotienthanhtoan'] ?? '' ?>"
placeholder="Số tiền">

<input class="col-md-2 form-control" name="hinhthuc"
value="<?= $edit_tt['Hinhthuc'] ?? '' ?>"
list="dsHinhThuc" placeholder="Hình thức thanh toán">

<button class="col-md-1 btn btn-success"
name="<?= $edit_tt ? 'capnhat_tt' : 'them_tt' ?>">
<?= $edit_tt ? 'Lưu' : 'Thêm' ?>
</button>

</form>

</form>
</div>

</div>
<div class="card mt-4">
<div class="card-header fw-bold"> Quản lý sản phẩm bán ra</div>
<div class="card-body">

<form class="mb-3 d-flex">
<input name="tim_ct" class="form-control me-2" placeholder="Tìm mã phiếu / mã SP">
<button class="btn btn-primary">Tìm</button>
</form>

<table class="table table-bordered">
<tr class="table-light">
<th>Mã PX</th><th>Mã SP</th><th>Số lượng</th><th>Đơn giá</th><th>Sửa</th><th>Xóa</th>
</tr>

<?php
$timct = $_GET['tim_ct'] ?? '';
$stmt = $pdo->prepare(
"SELECT * FROM Chitiet_Phieuxuat
 WHERE Maxuathang LIKE ? OR Masp LIKE ?"
);
$stmt->execute(["%$timct%","%$timct%"]);
foreach ($stmt as $r):
?>
<tr>
<td><?= $r['Maxuathang'] ?></td>
<td><?= $r['Masp'] ?></td>
<td><?= $r['Soluong'] ?></td>
<td><?= number_format($r['Dongiaxuat']) ?></td>
<td>
<a class="btn btn-sm btn-warning"
href="?sua_ct=<?= $r['Id'] ?>">Sửa</a>
</td>
<td>
<a class="btn btn-sm btn-danger"
href="?xoa_ct=<?= $r['Id'] ?>"
onclick="return confirm('Xóa?')">Xóa</a>
</td>
</tr>
<?php endforeach; ?>
</table>

<h5>Thêm / Sửa sản phẩm bán</h5>
<form method="post" class="row g-2">
<input type="hidden" name="id" value="<?= $edit_ct['Id'] ?? '' ?>">

<input class="col-md-3 form-control" name="maxuat"
value="<?= $edit_ct['Maxuathang'] ?? '' ?>" placeholder="Mã PX">

<input class="col-md-3 form-control" name="masp"
value="<?= $edit_ct['Masp'] ?? '' ?>" placeholder="Mã SP">

<input class="col-md-2 form-control" name="soluong"
value="<?= $edit_ct['Soluong'] ?? '' ?>" placeholder="SL">

<input class="col-md-2 form-control" name="dongia"
value="<?= $edit_ct['Dongiaxuat'] ?? '' ?>" placeholder="Đơn giá">

<button class="col-md-2 btn btn-success"
name="<?= $edit_ct ? 'capnhat_ct' : 'them_ct' ?>">
<?= $edit_ct ? 'Lưu' : 'Thêm' ?>
</button>
</form>

</div>
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
