<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: dangnhap.php');
    exit;
}
require_once __DIR__ . '/db.php';

// 1. Lấy dữ liệu tiêu hao bằng cách SUM (tổng) số lượng từ chi tiết phiếu xuất
// Kết nối các bảng: Phieuxuat -> Chitiet_Phieuxuat -> Sanpham & Congtrinh
$sql = "
    SELECT 
        ct.Tenct, 
        sp.Masp, 
        sp.Tensp, 
        sp.DVT,
        SUM(ctx.Soluong) as TongTieuHao,
        MAX(px.Ngayxuat) as NgayXuatGanNhat
    FROM Phieuxuat px
    JOIN Chitiet_Phieuxuat ctx ON px.Maxuathang = ctx.Maxuathang
    JOIN Congtrinh ct ON px.Mact = ct.Mact
    JOIN Sanpham sp ON ctx.Masp = sp.Masp
    GROUP BY ct.Mact, sp.Masp
    ORDER BY ct.Tenct ASC, sp.Tensp ASC
";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 2. Gộp dữ liệu theo công trình để hiển thị giao diện phân tầng
$grouped_data = [];
foreach ($data as $row) {
    $grouped_data[$row['Tenct']][] = $row;
}

$page_title = "Báo Cáo Tiêu Hao Vật Tư Thực Tế";
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title><?= $page_title ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background-color: #f4f7f6; font-family: 'Segoe UI', sans-serif; }
        /* Sidebar giữ phong cách giống danh sách phiếu xuất */
        .sidebar { background-color: #007bff; height: 100vh; position: fixed; width: 250px; color: white; padding-top: 20px; }
        .sidebar .nav-link { color: white !important; padding: 12px 20px; transition: 0.3s; }
        .sidebar .nav-link:hover { background-color: #0069d9; font-weight: bold; padding-left: 30px; }
        
        .main-content { margin-left: 250px; padding: 30px; }
        .card-report { background: white; border-radius: 12px; border: none; box-shadow: 0 4px 20px rgba(0,0,0,0.08); padding: 25px; }
        
        .table thead { background-color: #2d3436; color: white; }
        .group-row { background-color: #dfe6e9 !important; font-weight: bold; color: #0984e3; }
        .qty-highlight { color: #d63031; font-weight: 800; font-size: 1.1em; }
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
            <a class="nav-link" href="javascript:void(0)" id="btnBaoCao">
                <i class="fas fa-chart-bar"></i> Báo cáo & Thống kê
                <i class="fas fa-chevron-down float-end"></i>
            </a>
            <ul class="nav flex-column ms-3 d-none" id="submenuBaoCao">
                <li class="nav-item"><a class="nav-link" href="baocaotieuhao.php"><i class="fas fa-chart-line"></i> Báo cáo tiêu hao</a></li>
                <li class="nav-item"><a class="nav-link" href="tonkho.php"><i class="fas fa-chart-pie"></i> Báo cáo tồn kho</a></li>
            </ul>
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
            <a class="nav-link" href="javascript:void(0)" id="btnCongTrinh">
                <i class="fas fa-briefcase"></i> Quản lý công trình
                <i class="fas fa-chevron-down float-end"></i>
            </a>
            <ul class="nav flex-column ms-3 d-none" id="submenuCongTrinh">
                <li class="nav-item"><a class="nav-link" href="congtrinh.php"><i class="fas fa-folder-open"></i> Công trình</a></li>
            </ul>
        </li>

        <li class="nav-item">
            <a class="nav-link text-danger" href="logout.php"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a>
        </li>
    </ul>
</nav>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-dark text-uppercase">Báo Cáo Tiêu Hao</h2>
        <button onclick="window.print()" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-print"></i> In báo cáo
        </button>
    </div>

    <div class="card-report">
        <table class="table table-hover table-bordered align-middle">
            <thead>
                <tr>
                    <th width="45%">Tên Vật Tư / Sản Phẩm</th>
                    <th width="15%" class="text-center">Mã SP</th>
                    <th width="15%" class="text-center">Tổng Xuất</th>
                    <th width="10%" class="text-center">ĐVT</th>
                    <th width="15%" class="text-center">Ngày xuất cuối</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($grouped_data)): ?>
                    <tr><td colspan="5" class="text-center py-5 text-muted">Chưa ghi nhận dữ liệu xuất kho nào.</td></tr>
                <?php else: ?>
                    <?php foreach ($grouped_data as $tenCT => $vattu): ?>
                        <tr class="group-row">
                            <td colspan="5"><i class="fas fa-building me-2"></i> CÔNG TRÌNH: <?= htmlspecialchars($tenCT) ?></td>
                        </tr>
                        <?php foreach ($vattu as $item): ?>
                        <tr>
                            <td class="ps-4"><?= htmlspecialchars($item['Tensp']) ?></td>
                            <td class="text-center"><code><?= htmlspecialchars($item['Masp']) ?></code></td>
                            <td class="text-center qty-highlight">
                                <?= number_format($item['TongTieuHao'], 2, ',', '.') ?>
                            </td>
                            <td class="text-center"><?= htmlspecialchars($item['DVT']) ?></td>
                            <td class="text-center text-muted small">
                                <?= date('d/m/Y', strtotime($item['NgayXuatGanNhat'])) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
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
            { btn: "btnCongTrinh", submenu: "submenuCongTrinh" }
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