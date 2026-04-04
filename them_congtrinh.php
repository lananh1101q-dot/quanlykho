<?php
session_start();
require_once __DIR__ . '/db.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user'])) {
    header("Location: dangnhap.php");
    exit;
}

$user = $_SESSION['user'];
$message = '';
$error = '';

// Xử lý khi nhấn nút "Tạo"
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mact = trim($_POST['mact'] ?? '');
    $tenct = trim($_POST['tenct'] ?? '');
    $diachi = trim($_POST['diachi'] ?? '');

    if (empty($mact) || empty($tenct)) {
        $error = "Vui lòng nhập đầy đủ Mã và Tên công trình!";
    } else {
        try {
            // Kiểm tra mã công trình đã tồn tại chưa
            $checkSql = "SELECT COUNT(*) FROM congtrinh WHERE Mact = ?";
            $checkStmt = $pdo->prepare($checkSql);
            $checkStmt->execute([$mact]);
            
            if ($checkStmt->fetchColumn() > 0) {
                $error = "Mã công trình này đã tồn tại!";
            } else {
                // Thêm mới vào database
                $sql = "INSERT INTO congtrinh (Mact, Tenct, Diachict) VALUES (?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                if ($stmt->execute([$mact, $tenct, $diachi])) {
                    echo "<script>alert('Thêm công trình thành công!'); window.location.href='congtrinh.php';</script>";
                    exit;
                }
            }
        } catch (PDOException $e) {
            $error = "Lỗi hệ thống: " . $e->getMessage();
        }
    }
}

$page_title = "Thêm Công Trình - Quản Lý Kho Hàng";
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
        body { background-color: #f8f9fa; font-family: 'Segoe UI', sans-serif; }
        .sidebar { background-color: #007bff; height: 100vh; position: fixed; width: 250px; color: white; padding-top: 20px; top: 0; left: 0; overflow-y: auto; }
        .sidebar .nav-link { color: white !important; padding: 12px 20px; border-radius: 5px; margin: 4px 10px; transition: all 0.3s ease; font-weight: normal; }
        .sidebar .nav-link:hover { background-color: #0069d9; font-weight: bold; transform: translateX(8px); }
        .main-content { margin-left: 250px; padding: 20px; }
        .card { border: none; border-radius: 15px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        @media (max-width: 768px) { .sidebar { width: 100%; height: auto; position: relative; } .main-content { margin-left: 0; } }
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
                
                <li class="nav-item"><a class="nav-link" href="thongke_biendong.php"><i class="fas fa-chart-area"></i> Biến động Nhập/Xuất</a></li>
                
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

<main class="main-content">
    <div class="mb-4">
        <h2 class="fw-bold">Thêm Công Trình</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="congtrinh.php" class="text-decoration-none">Quản lý công trình</a></li>
                <li class="breadcrumb-item active">Tạo mới</li>
            </ol>
        </nav>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i> <?= $error ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body p-4">
            <h5 class="card-title text-primary mb-4 border-bottom pb-3">Thông tin công trình</h5>
            
            <form action="" method="POST">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Mã công trình*</label>
                        <input type="text" name="mact" class="form-control" placeholder="Ví dụ: CT01" required 
                               value="<?= htmlspecialchars($_POST['mact'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Tên công trình*</label>
                        <input type="text" name="tenct" class="form-control" placeholder="Ví dụ: Vinhomes Grand Park" required
                               value="<?= htmlspecialchars($_POST['tenct'] ?? '') ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-bold">Địa chỉ</label>
                        <input type="text" name="diachi" class="form-control" placeholder="Ví dụ: Quận 9, TP. Thủ Đức"
                               value="<?= htmlspecialchars($_POST['diachi'] ?? '') ?>">
                    </div>
                    
                    <div class="col-12 mt-4 d-flex justify-content-end gap-2">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="fas fa-save me-2"></i>Tạo
                        </button>
                        <a href="congtrinh.php" class="btn btn-light border px-4">
                            <i class="fas fa-arrow-left me-2"></i>Quay lại
                        </a>
                    </div>
                </div>
            </form>
        </div>
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