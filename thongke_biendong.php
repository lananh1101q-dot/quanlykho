<?php
// Bắt đầu session và kết nối CSDL
session_start();
require_once 'db.php'; // Sử dụng PDO từ db.php

// 1. Nhận giá trị bộ lọc thời gian (Mặc định là từ đầu tháng đến hiện tại)
$tu_ngay = isset($_GET['tu_ngay']) ? $_GET['tu_ngay'] : date('Y-m-01');
$den_ngay = isset($_GET['den_ngay']) ? $_GET['den_ngay'] : date('Y-m-t');
$kieu_thong_ke = isset($_GET['kieu_thong_ke']) ? $_GET['kieu_thong_ke'] : 'ngay'; // ngày, tháng, năm

// Xác định định dạng thời gian cho SQL (GROUP BY)
if ($kieu_thong_ke == 'thang') {
    $format_sql = "%Y-%m"; // Năm-Tháng
} elseif ($kieu_thong_ke == 'nam') {
    $format_sql = "%Y";    // Năm
} else {
    $format_sql = "%Y-%m-%d"; // Ngày cụ thể
}

$thong_ke = []; // Mảng chứa dữ liệu gộp

try {
    // 2. Truy vấn dữ liệu Phiếu Nhập bằng PDO
    $sql_nhap = "SELECT DATE_FORMAT(Ngaynhaphang, :format) as thoi_gian, 
                        COUNT(Manhaphang) as so_phieu, 
                        SUM(Tongtiennhap) as tong_tien
                 FROM Phieunhap
                 WHERE DATE(Ngaynhaphang) BETWEEN :tu_ngay AND :den_ngay
                 GROUP BY thoi_gian";
    
    $stmt_nhap = $pdo->prepare($sql_nhap);
    $stmt_nhap->execute([
        ':format' => $format_sql,
        ':tu_ngay' => $tu_ngay,
        ':den_ngay' => $den_ngay
    ]);
    
    while ($row = $stmt_nhap->fetch()) {
        $thoi_gian = $row['thoi_gian'];
        $thong_ke[$thoi_gian]['nhap_so_phieu'] = $row['so_phieu'];
        $thong_ke[$thoi_gian]['nhap_tong_tien'] = $row['tong_tien'];
    }

    // 3. Truy vấn dữ liệu Phiếu Xuất bằng PDO
    $sql_xuat = "SELECT DATE_FORMAT(Ngayxuat, :format) as thoi_gian, 
                        COUNT(Maxuathang) as so_phieu, 
                        SUM(Tongtienxuat) as tong_tien
                 FROM Phieuxuat
                 WHERE DATE(Ngayxuat) BETWEEN :tu_ngay AND :den_ngay
                 GROUP BY thoi_gian";
                 
    $stmt_xuat = $pdo->prepare($sql_xuat);
    $stmt_xuat->execute([
        ':format' => $format_sql,
        ':tu_ngay' => $tu_ngay,
        ':den_ngay' => $den_ngay
    ]);

    while ($row = $stmt_xuat->fetch()) {
        $thoi_gian = $row['thoi_gian'];
        $thong_ke[$thoi_gian]['xuat_so_phieu'] = $row['so_phieu'];
        $thong_ke[$thoi_gian]['xuat_tong_tien'] = $row['tong_tien'];
    }

    // Sắp xếp mảng theo thời gian giảm dần (Mới nhất lên đầu)
    krsort($thong_ke);

} catch (PDOException $e) {
    die("Lỗi truy vấn CSDL: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thống Kê Biến Động Nhập Xuất</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body { 
            background-color: #f8f9fa; 
            font-family: 'Segoe UI', sans-serif; 
        }
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
        }
        .sidebar .nav-link:hover {
            background-color: #0069d9;
            font-weight: bold;
            transform: translateX(8px);
        }
        .main-content { 
            margin-left: 250px; 
            padding: 20px; 
        }
        .d-none { display: none !important; }
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
            <ul class="nav flex-column ms-3 d-none" id="submenuBaoCao">
                <li class="nav-item"><a class="nav-link" href="baocaotieuhao.php"><i class="fas fa-chart-line"></i> Báo cáo tiêu hao</a></li>
                <li class="nav-item"><a class="nav-link" href="tonkho.php"><i class="fas fa-chart-pie"></i> Báo cáo tồn kho</a></li>
                
                <li class="nav-item"><a class="nav-link" href="thongke_biendong.php"><i class="fas fa-chart-area"></i> Biến động Nhập/Xuất</a></li>
                
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
    <div class="container-fluid mt-4">
        <h2 class="mb-4">Thống Kê Biến Động Nhập/Xuất</h2>

        <form method="GET" class="row g-3 mb-4 border p-3 bg-white rounded shadow-sm">
            <div class="col-md-3">
                <label>Từ ngày</label>
                <input type="date" name="tu_ngay" class="form-control" value="<?= htmlspecialchars($tu_ngay) ?>">
            </div>
            <div class="col-md-3">
                <label>Đến ngày</label>
                <input type="date" name="den_ngay" class="form-control" value="<?= htmlspecialchars($den_ngay) ?>">
            </div>
            <div class="col-md-3">
                <label>Nhóm theo</label>
                <select name="kieu_thong_ke" class="form-select">
                    <option value="ngay" <?= $kieu_thong_ke == 'ngay' ? 'selected' : '' ?>>Theo Ngày</option>
                    <option value="thang" <?= $kieu_thong_ke == 'thang' ? 'selected' : '' ?>>Theo Tháng</option>
                    <option value="nam" <?= $kieu_thong_ke == 'nam' ? 'selected' : '' ?>>Theo Năm</option>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search"></i> Xem Thống Kê</button>
            </div>
        </form>

        <div class="table-responsive bg-white rounded shadow-sm">
            <table class="table table-bordered table-striped table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th rowspan="2" class="align-middle text-center">Thời gian</th>
                        <th colspan="2" class="text-center text-success">NHẬP KHO</th>
                        <th colspan="2" class="text-center text-warning">XUẤT KHO</th>
                        <th rowspan="2" class="align-middle text-center">Chênh lệch (Xuất - Nhập)</th>
                    </tr>
                    <tr>
                        <th class="text-center">Số phiếu</th>
                        <th class="text-center">Tổng tiền (VNĐ)</th>
                        <th class="text-center">Số phiếu</th>
                        <th class="text-center">Tổng tiền (VNĐ)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($thong_ke)): ?>
                        <tr><td colspan="6" class="text-center py-4">Không có dữ liệu trong khoảng thời gian này.</td></tr>
                    <?php else: ?>
                        <?php 
                        $tong_tien_nhap_all = 0;
                        $tong_tien_xuat_all = 0;
                        foreach ($thong_ke as $thoi_gian => $data): 
                            $so_phieu_nhap = isset($data['nhap_so_phieu']) ? $data['nhap_so_phieu'] : 0;
                            $tien_nhap = isset($data['nhap_tong_tien']) ? $data['nhap_tong_tien'] : 0;
                            
                            $so_phieu_xuat = isset($data['xuat_so_phieu']) ? $data['xuat_so_phieu'] : 0;
                            $tien_xuat = isset($data['xuat_tong_tien']) ? $data['xuat_tong_tien'] : 0;

                            $chenh_lech = $tien_xuat - $tien_nhap;

                            $tong_tien_nhap_all += $tien_nhap;
                            $tong_tien_xuat_all += $tien_xuat;
                        ?>
                        <tr>
                            <td class="text-center fw-bold"><?= htmlspecialchars($thoi_gian) ?></td>
                            <td class="text-center"><?= number_format($so_phieu_nhap) ?></td>
                            <td class="text-end text-success"><?= number_format($tien_nhap) ?></td>
                            <td class="text-center"><?= number_format($so_phieu_xuat) ?></td>
                            <td class="text-end text-warning"><?= number_format($tien_xuat) ?></td>
                            <td class="text-end fw-bold <?= $chenh_lech >= 0 ? 'text-primary' : 'text-danger' ?>">
                                <?= number_format($chenh_lech) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <tr class="table-info fw-bold">
                            <td class="text-center text-uppercase">Tổng cộng</td>
                            <td colspan="2" class="text-end text-success"><?= number_format($tong_tien_nhap_all) ?> VNĐ</td>
                            <td colspan="2" class="text-end text-warning"><?= number_format($tong_tien_xuat_all) ?> VNĐ</td>
                            <td class="text-end <?= ($tong_tien_xuat_all - $tong_tien_nhap_all) >= 0 ? 'text-primary' : 'text-danger' ?>">
                                <?= number_format($tong_tien_xuat_all - $tong_tien_nhap_all) ?> VNĐ
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Quản lý sidebar toggle submenu - Tối ưu và dễ bảo trì
    document.addEventListener("DOMContentLoaded", function () {
        const menuItems = [
            { btn: "btnBaoCao", submenu: "submenuBaoCao" },
            { btn: "btnSanPham", submenu: "submenuSanPham" },
            { btn: "btnPhieuNhap", submenu: "submenuPhieuNhap" },
            { btn: "btnPhieuXuat", submenu: "submenuPhieuXuat" },
            { btn: "btnKhachHang", submenu: "submenuKhachHang" } // Thêm quản lý khách hàng
        ];

        menuItems.forEach(item => {
            const button = document.getElementById(item.btn);
            if (button) {
                button.addEventListener("click", function (e) {
                    e.preventDefault();
                    const submenu = document.getElementById(item.submenu);
                    if (submenu) {
                        submenu.classList.toggle("d-none");
                        
                        // Xoay icon chevron
                        const icon = this.querySelector(".fa-chevron-down");
                        if (icon) {
                            icon.style.transform = submenu.classList.contains("d-none") 
                                ? "rotate(0deg)" 
                                : "rotate(180deg)";
                            icon.style.transition = "transform 0.3s ease";
                        }
                    }
                });
            }
        });
    });
</script>
</body>
</html>