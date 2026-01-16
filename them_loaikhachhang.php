<?php
session_start();
require_once __DIR__ . '/db.php';

if (!isset($_SESSION['user'])) {
    header("Location: dangnhap.php");
    exit;
}

$user = $_SESSION['user'];
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tenloaikh = trim($_POST['tenloaikh'] ?? '');
    $motaloaikh = trim($_POST['motaloaikh'] ?? '');

    if (empty($tenloaikh)) {
        $error = "Tên loại khách hàng không được để trống!";
    } else {
        // Kiểm tra trùng tên loại
        $check = $pdo->prepare("SELECT COUNT(*) FROM loaikhachhang WHERE Tenloaikh = ?");
        $check->execute([$tenloaikh]);
        if ($check->fetchColumn() > 0) {
            $error = "Tên loại khách hàng đã tồn tại, vui lòng chọn tên khác!";
        } else {
            // ===== GENERATE MÃ TỰ ĐỘNG CHỈ SỐ (1, 2, 3...) =====
            $stmt = $pdo->query("SELECT MAX(CAST(Maloaikh AS UNSIGNED)) FROM loaikhachhang");
            $max = $stmt->fetchColumn();

            $new_num = ($max !== null && $max !== false) ? $max + 1 : 1;
            $maloaikh = $new_num; // Chỉ số thuần: 1, 2, 3...
            // ================================================

            try {
                $stmt = $pdo->prepare("INSERT INTO loaikhachhang (Maloaikh, Tenloaikh, Motaloaikh) VALUES (?, ?, ?)");
                $stmt->execute([$maloaikh, $tenloaikh, $motaloaikh]);
                $success = "Thêm loại khách hàng thành công! Mã tự động: <strong>$maloaikh</strong>";
            } catch (PDOException $e) {
                $error = "Lỗi khi thêm: " . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm Loại Khách Hàng</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .sidebar { background-color: #007bff; height: 100vh; position: fixed; width: 250px; color: white; padding-top: 20px; }
        .sidebar .nav-link { color: white !important; padding: 12px 20px; }
        .sidebar .nav-link:hover { background-color: #0069d9; font-weight: bold; transform: translateX(8px); }
        .main-content { margin-left: 250px; padding: 20px; }
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

<main class="main-content">
    <h2 class="mb-4"><i class="fas fa-plus-circle me-2"></i> Thêm Loại Khách Hàng Mới</h2>

    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?php echo $success; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?php echo $error; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-body">
           

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Tên loại khách hàng <span class="text-danger">*</span></label>
                    <input type="text" name="tenloaikh" class="form-control" required placeholder="VD: Khách VIP">
                </div>
                <div class="mb-4">
                    <label class="form-label">Mô tả</label>
                    <textarea name="motaloaikh" class="form-control" rows="3" placeholder="Mô tả chi tiết về loại khách hàng..."></textarea>
                </div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Lưu Loại Khách Hàng</button>
                <a href="loaikhachhang.php" class="btn btn-secondary ms-2">Quay lại danh sách</a>
            </form>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Quản lý sản phẩm
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