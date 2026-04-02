<?php
session_start();
require_once __DIR__ . '/db.php';

// 1. Kiểm tra đăng nhập
if (!isset($_SESSION['user'])) {
    header("Location: dangnhap.php");
    exit;
}

$user = $_SESSION['user'];
$error = '';
$success = '';

// 2. Lấy thông tin công trình cũ để hiển thị lên Form
if (isset($_GET['id'])) {
    $mact_old = $_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM congtrinh WHERE Mact = ?");
    $stmt->execute([$mact_old]);
    $congtrinh = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$congtrinh) {
        die("Công trình không tồn tại!");
    }
} else {
    header("Location: congtrinh.php");
    exit;
}

// 3. Xử lý khi nhấn nút "Thay đổi"
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tenct = trim($_POST['tenct'] ?? '');
    $diachi = trim($_POST['diachi'] ?? '');

    if (empty($tenct)) {
        $error = "Tên công trình không được để trống!";
    } else {
        try {
            $sql = "UPDATE congtrinh SET Tenct = ?, Diachict = ? WHERE Mact = ?";
            $stmt = $pdo->prepare($sql);
            if ($stmt->execute([$tenct, $diachi, $mact_old])) {
                echo "<script>alert('Cập nhật thành công!'); window.location.href='congtrinh.php';</script>";
                exit;
            }
        } catch (PDOException $e) {
            $error = "Lỗi: " . $e->getMessage();
        }
    }
}

$page_title = "Sửa Công Trình - Quản Lý Kho";
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
        .sidebar { background-color: #007bff; height: 100vh; position: fixed; width: 250px; color: white; padding-top: 20px; top: 0; left: 0; }
        .sidebar .nav-link { color: white !important; padding: 12px 20px; border-radius: 5px; margin: 4px 10px; transition: 0.3s; }
        .sidebar .nav-link:hover { background-color: #0069d9; transform: translateX(8px); }
        .main-content { margin-left: 250px; padding: 40px; }
        
        /* Style giống ảnh bạn gửi */
        .form-container { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .form-label { font-weight: bold; margin-top: 15px; }
        .form-control { border-radius: 4px; padding: 12px; border: 1px solid #ddd; }
        .btn-update { background-color: #ff4d4d; color: white; border: none; padding: 10px 25px; border-radius: 5px; font-weight: bold; }
        .btn-update:hover { background-color: #e60000; }
        .btn-cancel { background-color: #ff4d4d; color: white; border: none; padding: 10px 25px; border-radius: 5px; font-weight: bold; text-decoration: none; display: inline-block; }
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

<main class="main-content">
    <h2 class="mb-4 text-uppercase fw-bold" style="color: #334155;">Sửa Thông Tin Công Trình</h2>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <div class="form-container">
        <form action="" method="POST">
            <div class="mb-3">
                <label class="form-label">Mã số:</label>
                <input type="text" class="form-control bg-light" value="<?= htmlspecialchars($congtrinh['Mact']) ?>" readonly>
                <small class="text-muted">(Mã số không được phép thay đổi)</small>
            </div>

            <div class="mb-3">
                <label class="form-label">Tên công trình:</label>
                <input type="text" name="tenct" class="form-control" 
                       value="<?= htmlspecialchars($congtrinh['Tenct']) ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Địa chỉ:</label>
                <input type="text" name="diachi" class="form-control" 
                       value="<?= htmlspecialchars($congtrinh['Diachict'] ?? '') ?>">
            </div>

            <div class="mt-4">
                <a href="congtrinh.php" class="btn btn-cancel me-2">hủy</a>
                <button type="submit" class="btn btn-update">thay đổi</button>
            </div>
        </form>
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