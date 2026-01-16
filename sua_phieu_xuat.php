<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: dangnhap.php');
    exit;
}
require_once __DIR__ . '/db.php';

$errors = [];
$maxuat_get = $_GET['id'] ?? '';

if (empty($maxuat_get)) {
    header('Location: danh_sach_phieu_xuat.php');
    exit;
}

// 1. Lấy dữ liệu dropdown
$khachhangs = $pdo->query("SELECT Makh, Tenkh FROM Khachhang ORDER BY Tenkh")->fetchAll();
$sanphams = $pdo->query("SELECT Masp, Tensp, Dvt, Giaban FROM Sanpham ORDER BY Tensp")->fetchAll();
$khos = $pdo->query("SELECT Makho, Tenkho FROM Kho ORDER BY Tenkho")->fetchAll();

// 2. Lấy dữ liệu phiếu hiện tại
$stmtPhieu = $pdo->prepare("SELECT * FROM Phieuxuat WHERE Maxuathang = ?");
$stmtPhieu->execute([$maxuat_get]);
$phieuXuat = $stmtPhieu->fetch();

if (!$phieuXuat) {
    header('Location: danh_sach_phieu_xuat.php?error=Phiếu không tồn tại');
    exit;
}

// 3. Lấy chi tiết phiếu cũ (để hoàn trả tồn kho)
$stmtCt = $pdo->prepare("SELECT * FROM Chitiet_Phieuxuat WHERE Maxuathang = ?");
$stmtCt->execute([$maxuat_get]);
$chiTietPhieuCu = $stmtCt->fetchAll();

// 4. Xử lý khi nhấn Cập nhật
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $maxuat_post = trim($_POST['maxuathang'] ?? ''); // Mã phiếu (readonly)
    $makh = $_POST['makh'] ?? '';
    $ngayxuat = $_POST['ngayxuat'] ?? '';
    $ghichu = trim($_POST['ghichu'] ?? '');
    $makho_update = $_POST['makho'] ?? ''; // KHO NGƯỜI DÙNG CHỌN ĐỂ CẬP NHẬT TỒN

    $maspArr = $_POST['masp'] ?? [];
    $soluongArr = $_POST['soluong'] ?? [];
    $dongiaArr = $_POST['dongia'] ?? [];

    if (empty($makh) || empty($ngayxuat) || empty($makho_update)) {
        $errors[] = 'Vui lòng chọn Khách hàng, Ngày xuất và Kho cập nhật.';
    }

    // Chuẩn hóa danh sách item mới
    $items = [];
    for ($i = 0; $i < count($maspArr); $i++) {
        $ms = trim($maspArr[$i] ?? '');
        $sl = (int)($soluongArr[$i] ?? 0);
        $dg = (float)($dongiaArr[$i] ?? 0);
        if ($ms !== '' && $sl > 0) {
            $items[] = ['masp' => $ms, 'soluong' => $sl, 'dongia' => $dg];
        }
    }

    if (empty($items)) $errors[] = 'Cần ít nhất một sản phẩm hợp lệ.';

    if (!$errors) {
        try {
            $pdo->beginTransaction();

            /* BƯỚC 1: HOÀN TRẢ TỒN KHO CŨ */
            // Vì bảng Phieuxuat không có mã kho, ta hoàn trả vào kho người dùng vừa chọn trên form
            $stmtHoan = $pdo->prepare("UPDATE Tonkho SET Soluongton = Soluongton + ? WHERE Masp = ? AND Makho = ?");
            foreach ($chiTietPhieuCu as $ct) {
                $stmtHoan->execute([$ct['Soluong'], $ct['Masp'], $makho_update]);
            }

            /* BƯỚC 2: XÓA CHI TIẾT CŨ */
            $pdo->prepare("DELETE FROM Chitiet_Phieuxuat WHERE Maxuathang = ?")->execute([$maxuat_get]);

            /* BƯỚC 3: CẬP NHẬT PHIẾU XUẤT */
            $tong = 0;
            foreach ($items as $it) { $tong += $it['soluong'] * $it['dongia']; }

            $stmtUpPhieu = $pdo->prepare("UPDATE Phieuxuat SET Makh = ?, Ngayxuat = ?, Tongtienxuat = ?, Ghichu = ? WHERE Maxuathang = ?");
            $stmtUpPhieu->execute([$makh, $ngayxuat, $tong, $ghichu, $maxuat_get]);

            /* BƯỚC 4: THÊM CHI TIẾT MỚI & TRỪ TỒN KHO */
            $stmtIns = $pdo->prepare("INSERT INTO Chitiet_Phieuxuat (Maxuathang, Masp, Soluong, Dongiaxuat) VALUES (?, ?, ?, ?)");
            $stmtTru = $pdo->prepare("UPDATE Tonkho SET Soluongton = Soluongton - ? WHERE Masp = ? AND Makho = ? AND Soluongton >= ?");

            foreach ($items as $it) {
                // Thêm chi tiết
                $stmtIns->execute([$maxuat_get, $it['masp'], $it['soluong'], $it['dongia']]);

                // Trừ tồn kho tại kho đã chọn
                $stmtTru->execute([$it['soluong'], $it['masp'], $makho_update, $it['soluong']]);

                if ($stmtTru->rowCount() === 0) {
                    throw new Exception("Sản phẩm {$it['masp']} không đủ tồn kho tại kho đã chọn!");
                }
            }

            $pdo->commit();
            header("Location: danh_sach_phieu_xuat.php?success=sua");
            exit;

        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = 'Lỗi: ' . $e->getMessage();
        }
    }
}
?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8" />
    <title>Sửa phiếu xuất kho</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .sidebar { background-color: #007bff; height: 100vh; position: fixed; width: 250px; color: white; padding-top: 20px; top: 0; left: 0; }
        .sidebar .nav-link { color: white !important; padding: 12px 20px; }
        .main-content { margin-left: 250px; padding: 20px; }
        .readonly-field { background-color: #e9ecef !important; cursor: not-allowed; }
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
                <li class="nav-item"><a class="nav-link" href="Sanpham.php"><i class="fas fa-cube"></i> Sản phẩm</a></li>
                <li class="nav-item"><a class="nav-link" href="dmsp.php"><i class="fas fa-tags"></i> Danh mục sản phẩm</a></li>
                <li class="nav-item"><a class="nav-link" href="Nhacungcap.php"><i class="fas fa-truck"></i> Nhà cung cấp</a></li>
            </ul>
        </li>

        <li class="nav-item">
            <a class="nav-link" href="javascript:void(0)" id="btnPhieuNhap">
                <i class="fas fa-file-import"></i> Phiếu nhập kho
                <i class="fas fa-chevron-down float-end"></i>
            </a>
            <ul class="nav flex-column ms-3 d-none" id="submenuPhieuNhap">
                <li class="nav-item"><a class="nav-link" href="danh_sach_phieu_nhap.php"><i class="fas fa-list"></i> Danh sách phiếu nhập</a></li>
                <li class="nav-item"><a class="nav-link" href="phieu_nhap.php"><i class="fas fa-plus-circle"></i> Tạo phiếu nhập</a></li>
            </ul>
        </li>

        <li class="nav-item">
            <a class="nav-link" href="javascript:void(0)" id="btnPhieuXuat">
                <i class="fas fa-file-export"></i> Phiếu xuất <!-- Đã sửa icon đúng -->
                <i class="fas fa-chevron-down float-end"></i>
            </a>
            <ul class="nav flex-column ms-3 d-none" id="submenuPhieuXuat">
                <li class="nav-item"><a class="nav-link" href="danh_sach_phieu_xuat.php"><i class="fas fa-list"></i> Danh sách phiếu xuất</a></li>
                <li class="nav-item"><a class="nav-link" href="phieu_xuat.php"><i class="fas fa-plus-circle"></i> Tạo phiếu xuất</a></li>
            </ul>
        </li>

        <li class="nav-item">
            <a class="nav-link" href="javascript:void(0)" id="btnBaoCao">
                <i class="fas fa-chart-bar"></i> Báo cáo & Thống kê
                <i class="fas fa-chevron-down float-end"></i>
            </a>
            <ul class="nav flex-column ms-3 d-none" id="submenuBaoCao"> <!-- ĐÃ SỬA: thêm ul đúng id -->
                <li class="nav-item"><a class="nav-link" href="tonkho.php"><i class="fas fa-warehouse"></i> Báo cáo tồn kho</a></li>
            </ul>
        </li>

        <li class="nav-item">
            <a class="nav-link" href="javascript:void(0)" id="btnKhachHang">
                <i class="fas fa-users"></i> Quản lý khách hàng <!-- Đã sửa icon đúng -->
                <i class="fas fa-chevron-down float-end"></i>
            </a>
            <ul class="nav flex-column ms-3 d-none" id="submenuKhachHang">
                <li class="nav-item"><a class="nav-link" href="khachhang.php"><i class="fas fa-user"></i> Khách hàng</a></li>
                <li class="nav-item"><a class="nav-link" href="loaikhachhang.php"><i class="fas fa-users-cog"></i> Loại khách hàng</a></li>
            </ul>
        </li>

        <li class="nav-item">
            <a class="nav-link text-danger" href="logout.php"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a>
        </li>
    </ul>
</nav>

<div class="main-content">
    <div class="max-w-5xl mx-auto bg-white p-6 rounded-lg shadow">
        <h2 class="text-2xl font-bold mb-4">Sửa Phiếu Xuất: <?= htmlspecialchars($maxuat_get) ?></h2>

        <?php if ($errors): ?>
            <div class="alert alert-danger">
                <?php foreach ($errors as $er) echo "• $er<br>"; ?>
            </div>
        <?php endif; ?>

        <form method="post">
            <div class="grid md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="form-label font-bold">Mã xuất hàng</label>
                    <input name="maxuathang" readonly class="form-control readonly-field" value="<?= htmlspecialchars($phieuXuat['Maxuathang']) ?>" />
                </div>
                <div>
                    <label class="form-label font-bold text-primary">Chọn Kho để cập nhật tồn *</label>
                    <select name="makho" required class="form-select border-primary">
                        <option value="">-- Chọn kho hàng --</option>
                        <?php foreach ($khos as $k): ?>
                            <option value="<?= $k['Makho'] ?>"><?= htmlspecialchars($k['Tenkho']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="form-label font-bold">Khách hàng</label>
                    <select name="makh" required class="form-select">
                        <?php foreach ($khachhangs as $kh): ?>
                            <option value="<?= $kh['Makh'] ?>" <?= ($phieuXuat['Makh'] == $kh['Makh']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($kh['Tenkh']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="form-label font-bold">Ngày xuất</label>
                    <input type="date" name="ngayxuat" required class="form-control" value="<?= $phieuXuat['Ngayxuat'] ?>" />
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label font-bold">Ghi chú</label>
                <textarea name="ghichu" rows="2" class="form-control"><?= htmlspecialchars($phieuXuat['Ghichu']) ?></textarea>
            </div>

            <div class="flex justify-between items-center mb-2">
                <h5 class="font-bold">Chi tiết sản phẩm</h5>
                <button type="button" onclick="addRow()" class="btn btn-sm btn-info text-white">+ Thêm dòng</button>
            </div>

            <table class="table table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>Sản phẩm</th>
                        <th width="150">Số lượng</th>
                        <th width="200">Đơn giá</th>
                        <th width="50"></th>
                    </tr>
                </thead>
                <tbody id="detail-rows">
                    <?php foreach ($chiTietPhieuCu as $ct): ?>
                    <tr>
                        <td>
                            <select name="masp[]" class="form-select">
                                <?php foreach ($sanphams as $sp): ?>
                                    <option value="<?= $sp['Masp'] ?>" <?= ($ct['Masp'] == $sp['Masp']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($sp['Tensp']) ?> (<?= $sp['Dvt'] ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td><input name="soluong[]" type="number" class="form-control" value="<?= $ct['Soluong'] ?>" /></td>
                        <td><input name="dongia[]" type="number" step="0.01" class="form-control" value="<?= $ct['Dongiaxuat'] ?>" /></td>
                        <td><button type="button" onclick="this.closest('tr').remove()" class="text-red-500">Xóa</button></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="mt-4">
                <button type="submit" class="btn btn-success px-5">Cập nhật phiếu xuất</button>
                <a href="danh_sach_phieu_xuat.php" class="btn btn-secondary">Hủy bỏ</a>
            </div>
        </form>
    </div>
</div>

<script>
function addRow() {
    const tbody = document.getElementById('detail-rows');
    const tr = document.createElement('tr');
    tr.innerHTML = `
        <td>
            <select name="masp[]" class="form-select">
                <option value="">-- Chọn --</option>
                <?php foreach ($sanphams as $sp): ?>
                    <option value="<?= $sp['Masp'] ?>"><?= addslashes($sp['Tensp']) ?></option>
                <?php endforeach; ?>
            </select>
        </td>
        <td><input name="soluong[]" type="number" class="form-control" value="1" /></td>
        <td><input name="dongia[]" type="number" step="0.01" class="form-control" value="0" /></td>
        <td><button type="button" onclick="this.closest('tr').remove()" class="text-red-500">Xóa</button></td>
    `;
    tbody.appendChild(tr);
}
 document.getElementById("btnSanPham").addEventListener("click", function () {
        document.getElementById("submenuSanPham").classList.toggle("d-none");
    });

    // Phiếu nhập kho
    document.getElementById("btnPhieuNhap").addEventListener("click", function () {
        document.getElementById("submenuPhieuNhap").classList.toggle("d-none");
    });

    // Phiếu xuất
    document.getElementById("btnPhieuXuat").addEventListener("click", function () {
        document.getElementById("submenuPhieuXuat").classList.toggle("d-none");
    });

    // Báo cáo & Thống kê (giờ hoạt động)
    document.getElementById("btnBaoCao").addEventListener("click", function () {
        document.getElementById("submenuBaoCao").classList.toggle("d-none");
    });

    // QUẢN LÝ KHÁCH HÀNG (đã thêm đầy đủ toggle)
    document.getElementById("btnKhachHang").addEventListener("click", function () {
        document.getElementById("submenuKhachHang").classList.toggle("d-none");
    });
</script>
</body>
</html>