<?php
session_start();
require_once __DIR__ . '/db.php';

if (!isset($_SESSION['user'])) {
    header("Location: dangnhap.php");
    exit;
}

$user = $_SESSION['user'];
$message = "";

// Lấy danh sách loại khách hàng để đổ vào combobox
$stmt = $pdo->query("SELECT Maloaikh, Tenloaikh FROM loaikhachhang ORDER BY Maloaikh");
$loaiKhachHang = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $makh = trim($_POST['makh']);
    $tenkh = trim($_POST['tenkh']);
    $sdtkh = trim($_POST['sdtkh']);
    $diachikh = trim($_POST['diachikh']);
    $maloaikh = $_POST['maloaikh']; // Lấy từ combobox

    // Kiểm tra bắt buộc (có thể thêm kiểm tra trùng mã khách hàng nếu cần)
    if (empty($makh) || empty($tenkh) || empty($sdtkh) || empty($maloaikh)) {
        $message = "Vui lòng nhập đầy đủ các trường bắt buộc!";
    } else {
        try {
            $sql = "INSERT INTO khachhang (Makh, Tenkh, Sdtkh, Diachikh, Maloaikh) VALUES (?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$makh, $tenkh, $sdtkh, $diachikh, $maloaikh]);
            
            // Chuyển hướng về danh sách sau khi thành công
            header("Location: khachhang.php");
            exit;
        } catch (PDOException $e) {
            $message = "Lỗi khi thêm khách hàng: " . $e->getMessage() . " (Có thể mã khách hàng đã tồn tại!)";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm Khách Hàng - Quản Lý Kho Hàng</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { 
            background-color: #f8f9fa; 
            font-family: 'Segoe UI', sans-serif; 
        }
        
        /* Sidebar giống hệt trang chủ */
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
        
        /* Chỉ hover mới nổi bật */
        .sidebar .nav-link:hover {
            background-color: #0069d9;
            font-weight: bold;
            transform: translateX(8px);
        }
        
        /* Bỏ hoàn toàn style active cố định */
        .sidebar .nav-link.active {
            background-color: transparent !important;
            font-weight: normal !important;
            transform: none !important;
        }
        
        .main-content { 
            margin-left: 250px; 
            padding: 30px; 
        }
        
        .form-card { 
            background: white; 
            padding: 40px; 
            border-radius: 12px; 
            box-shadow: 0 4px 20px rgba(0,0,0,0.1); 
            max-width: 900px;
            margin: 0 auto;
        }
        
        @media (max-width: 768px) { 
            .sidebar { 
                width: 100%; 
                height: auto; 
                position: relative; 
            } 
            .main-content { 
                margin-left: 0; 
                padding: 20px;
            }
            .form-card {
                padding: 20px;
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
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Thêm Khách Hàng Mới</h2>
            <a href="khachhang.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Quay lại danh sách
            </a>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?= htmlspecialchars($message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="form-card">
            <form method="POST">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Mã Khách Hàng <span class="text-danger">*</span></label>
                        <input type="text" name="makh" class="form-control" placeholder="Ví dụ: KH001" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Tên Khách Hàng <span class="text-danger">*</span></label>
                        <input type="text" name="tenkh" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Số Điện Thoại <span class="text-danger">*</span></label>
                        <input type="text" name="sdtkh" class="form-control" placeholder="Ví dụ: 0901234567" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Loại Khách Hàng <span class="text-danger">*</span></label>
                        <select name="maloaikh" class="form-select" required>
                            <option value="">-- Chọn loại khách hàng --</option>
                            <?php foreach ($loaiKhachHang as $loai): ?>
                                <option value="<?= htmlspecialchars($loai['Maloaikh']) ?>">
                                    <?= htmlspecialchars($loai['Tenloaikh']) ?> (Mã: <?= $loai['Maloaikh'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (empty($loaiKhachHang)): ?>
                            <small class="text-muted">Chưa có loại khách hàng nào. Vui lòng thêm ở <a href="loaikhachhang.php">đây</a>.</small>
                        <?php endif; ?>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-bold">Địa Chỉ</label>
                        <textarea name="diachikh" class="form-control" rows="3" placeholder="Nhập địa chỉ khách hàng..."></textarea>
                    </div>
                </div>
                
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary btn-lg px-5">
                        <i class="fas fa-save"></i> Lưu Khách Hàng
                    </button>
                    <a href="khachhang.php" class="btn btn-secondary btn-lg px-5 ms-3">
                        Hủy bỏ
                    </a>
                </div>
            </form>
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