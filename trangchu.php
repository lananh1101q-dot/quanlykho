<?php
session_start();

// 1. Kiểm tra bảo mật: Nếu chưa đăng nhập thì bắt quay lại trang dangnhap.php
if (!isset($_SESSION['user'])) {
    header("Location: dangnhap.php");
    exit;
}

// Lấy thông tin người dùng từ Session để hiển thị
$user = $_SESSION['user'];

// Dữ liệu thống kê của bạn
$stats = [
    'don_hang_moi' => ['so' => '0', 'tien' => '0đ', 'icon' => 'shopping-cart', 'color' => 'text-primary'],
    'don_hang_cho' => ['so' => '0', 'icon' => 'clock', 'color' => 'text-warning'],
    'tong_tien_kh' => ['so' => '1.785.000đ', 'icon' => 'dollar-sign', 'color' => 'text-success'],
    'cong_no_hang' => ['so' => '2.125.000đ', 'icon' => 'file-invoice-dollar', 'color' => 'text-danger'],
    'ton_kho' => ['so' => '4', 'icon' => 'warehouse', 'color' => 'text-info'],
    'san_pham_het' => ['so' => '1', 'icon' => 'exclamation-triangle', 'color' => 'text-danger']
];
$page_title = "Trang Chủ - Quản Lý Kho Hàng";
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
        
        .stat-card { 
            border: none; 
            box-shadow: 0 2px 10px rgba(0,0,0,0.1); 
            border-radius: 12px; 
            text-align: center; 
            padding: 25px; 
            transition: 0.3s; 
        }
        
        .stat-card:hover { 
            transform: translateY(-5px); 
            box-shadow: 0 10px 20px rgba(0,0,0,0.15); 
        }
        
        .stat-icon { 
            font-size: 3.5rem; 
            margin-bottom: 15px; 
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
    <div class="d-flex justify-content-between align-items-center mb-5">
        <h2>Trang Chủ</h2>
        <div class="user-info">
       
        </div>
    </div>

    <!-- Phần chào mừng mới: chữ lớn, đẹp, ở giữa màn hình -->
    <div class="text-center mt-5 pt-5">
        <h1 class="display-3 fw-bold text-primary mb-4">
            <i class="fas fa-warehouse me-3"></i>
            Chào mừng đến với Trang Chủ
        </h1>
        <p class="lead text-muted fs-4">
            Hệ thống Quản Lý Kho Hàng - Sẵn sàng hỗ trợ bạn quản lý hiệu quả!
        </p>
        <hr class="w-50 mx-auto my-5 border-primary opacity-50">
        <p class="text-secondary fs-5">
            Hãy chọn chức năng từ menu bên trái để bắt đầu.
        </p>
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