<?php

session_start();
require_once __DIR__ . '/db.php';

if (!isset($_SESSION['user'])) {
    header("Location: dangnhap.php");
    exit;
}

$user = $_SESSION['user'];

// Tìm kiếm
$search_ma  = trim($_GET['search_ma'] ?? '');
$search_ten = trim($_GET['search_ten'] ?? '');

$sql = "SELECT * FROM congtrinh WHERE 1=1";
$params = [];

if ($search_ma !== '') {
    $sql .= " AND Mact LIKE ?";
    $params[] = "%$search_ma%";
}

if ($search_ten !== '') {
    $sql .= " AND Tenct LIKE ?";
    $params[] = "%$search_ten%";
}

$sql .= " ORDER BY Mact";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$congtrinhs = $stmt->fetchAll(PDO::FETCH_ASSOC);

$page_title = "Công Trình - Quản Lý Kho Hàng";
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
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
            font-weight: normal;
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
                <i class="fas fa-file-export"></i> Phiếu xuất
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
            <a class="nav-link" href="javascript:void(0)" id="btnCongtrinh">
                <i class="fas fa-building"></i> Quản lý công trình
                <i class="fas fa-chevron-down float-end"></i>
            </a>
            <ul class="nav flex-column ms-3 d-none" id="submenuCongtrinh">
                <li class="nav-item"><a class="nav-link" href="congtrinh.php"><i class="fas fa-list"></i> Danh sách công trình</a></li>
                <li class="nav-item"><a class="nav-link" href="them_congtrinh.php"><i class="fas fa-plus-circle"></i> Thêm công trình</a></li>
            </ul>
        </li>

        <li class="nav-item">
            <a class="nav-link text-danger" href="logout.php"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a>
        </li>
    </ul>
</nav>

  <main class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Công Trình</h2>
        <a href="them_congtrinh.php" class="btn btn-success"><i class="fas fa-plus me-2"></i>Thêm công trình</a>
    </div>

    <form action="" method="GET" class="mb-4">
        <div class="d-flex gap-3 align-items-center">
            <div class="input-group" style="max-width: 300px;">
                <span class="input-group-text text-danger fw-bold">Q</span>
                <input type="text" name="search_ma" class="form-control" placeholder="Tìm kiếm mã công trình..." 
                       value="<?= htmlspecialchars($search_ma ?? '') ?>">
            </div>

            <div class="input-group" style="max-width: 300px;">
                <span class="input-group-text text-danger fw-bold">Q</span>
                <input type="text" name="search_ten" class="form-control" placeholder="Tìm kiếm tên công trình..." 
                       value="<?= htmlspecialchars($search_ten ?? '') ?>">
            </div>

            <button type="submit" class="btn btn-dark px-4">
                <i class="fas fa-search me-2"></i>Tìm kiếm
            </button>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>Mã CT</th>
                    <th>Tên công trình</th>
                    <th>Địa chỉ</th>
                    <th class="text-center">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($congtrinhs as $row): ?>
                <tr>
                    <td class="fw-bold"><?= htmlspecialchars($row['Mact']) ?></td>
                    <td><?= htmlspecialchars($row['Tenct']) ?></td>
                    <td><?= htmlspecialchars($row['Diachict'] ?? '') ?></td>
                    <td class="text-center">
                        <a href="sua_congtrinh.php?id=<?= htmlspecialchars($row['Mact']) ?>" 
                           class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                        <a href="xoa_congtrinh.php?id=<?= htmlspecialchars($row['Mact']) ?>" 
                           class="btn btn-sm btn-outline-danger" 
                           onclick="return confirm('Bạn có chắc muốn xóa công trình này?')"><i class="fas fa-trash"></i></a>
                    </td>
                </tr>
                <?php endforeach; ?>

                <?php if (empty($congtrinhs)): ?>
                <tr>
                    <td colspan="4" class="text-center py-4 text-muted">Không tìm thấy công trình nào.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

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