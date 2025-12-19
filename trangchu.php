 <?php
// Bỏ comment mảng $stats để dùng lại dữ liệu động
$stats = [
    'don_hang_moi' => ['so' => '0', 'tien' => '0đ', 'icon' => 'shopping-cart', 'color' => 'text-primary'],
    'don_hang_cho' => ['so' => '0', 'icon' => 'clock', 'color' => 'text-warning'],
    'tong_tien_kh' => ['so' => '1.785.000đ', 'icon' => 'dollar-sign', 'color' => 'text-success'],
    'cong_no_hang' => ['so' => '2.125.000đ', 'icon' => 'file-invoice-dollar', 'color' => 'text-danger'],
    'ton_kho' => ['so' => '4', 'icon' => 'warehouse', 'color' => 'text-info'],
    'san_pham_het' => ['so' => '1', 'icon' => 'exclamation-triangle', 'color' => 'text-danger']
];
$page_title = "Trang Chủ - Quản Lý Kho Hàng";  // đổi ở đây
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
        .sidebar { background-color: #007bff; height: 100vh; position: fixed; width: 250px; color: white; padding-top: 20px; }
        .sidebar .nav-link { color: white; padding: 10px 20px; border-radius: 5px; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { background-color: #0056b3; }
        .main-content { margin-left: 250px; padding: 20px; }
        .stat-card { border: none; box-shadow: 0 2px 10px rgba(0,0,0,0.1); border-radius: 12px; text-align: center; padding: 25px; transition: 0.3s; }
        .stat-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.15); }
        .stat-icon { font-size: 3.5rem; margin-bottom: 15px; }
        @media (max-width: 768px) { .sidebar { width: 100%; height: auto; position: relative; } .main-content { margin-left: 0; } }
    </style>
</head>
<body>

    <!-- Sidebar (giữ nguyên) -->
    <nav class="sidebar">
        <div class="text-center mb-4">
            <h4><i class="fas fa-warehouse"></i> Quản Lý Kho Hàng</h4>
        </div>
        <ul class="nav flex-column">
            <li class="nav-item"><a class="nav-link active" href="#"><i class="fas fa-home"></i> Trang Chủ</a></li>
            <li class="nav-item"><a class="nav-link" href="#"><i class="fas fa-shopping-cart"></i> Quản lý bán hàng</a></li>
            <li class="nav-item"><a class="nav-link" href="#"><i class="fas fa-chart-bar"></i> Báo cáo & Thống kê</a></li>
            <li class="nav-item"><a class="nav-link" href="#"><i class="fas fa-box"></i> Báo cáo tồn kho</a></li>
            <li class="nav-item"><a class="nav-link" href="#"><i class="fas fa-users"></i> Báo cáo khách hàng</a></li>
            <li class="nav-item"><a class="nav-link" href="#"><i class="fas fa-file-invoice"></i> Báo cáo công nợ</a></li>
            <li class="nav-item"><a class="nav-link" href="#"><i class="fas fa-cog"></i> Cài đặt</a></li>
            <li class="nav-item"><a class="nav-link text-danger" href="#"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a></li>
        </ul>
    </nav>

    <!-- Nội dung chính -->
    <main class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Trang Chủ</h2>   <!-- đổi từ "Bảng Điều Khiển" thành "Trang Chủ" -->
            <select class="form-select w-auto">
                <option>12 tháng</option>
                <option>6 tháng</option>
                <option>3 tháng</option>
            </select>
        </div>

        <!-- Các card thống kê (giữ nguyên 100%) -->
        <div class="row g-4">
            <div class="col-md-3">
                <div class="card stat-card">
                    <div class="card-body">
                        <i class="fas fa-shopping-cart stat-icon <?php echo $stats['don_hang_moi']['color']; ?>"></i>
                        <h5>Đơn hàng mới</h5>
                        <h3><?php echo $stats['don_hang_moi']['so']; ?></h3>
                        <p><?php echo $stats['don_hang_moi']['tien']; ?></p>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card stat-card">
                    <div class="card-body">
                        <i class="fas fa-clock stat-icon <?php echo $stats['don_hang_cho']['color']; ?>"></i>
                        <h5>Đơn hàng đang chờ</h5>
                        <h3><?php echo $stats['don_hang_cho']['so']; ?></h3>
                        <p class="text-danger">Giảm 100% so với tháng trước ↓</p>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card sta t-card">
                    <div class="card-body">
                        <i class="fas fa-dollar-sign stat-icon <?php echo $stats['tong_tien_kh']['color']; ?>"></i>
                        <h5>Công nợ khách hàng</h5>
                        <h3><?php echo $stats['tong_tien_kh']['so']; ?></h3>
                        <p>Tổng tiền khách hàng còn nợ</p>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card stat-card">
                    <div class="card-body">
                        <i class="fas fa-file-invoice-dollar stat-icon <?php echo $stats['cong_no_hang']['color']; ?>"></i>
                        <h5>Công nợ nhà cung cấp</h5>
                        <h3><?php echo $stats['cong_no_hang']['so']; ?></h3>
                        <p>Tổng tiền cần trả NCC</p>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card stat-card">
                    <div class="card-body">
                        <i class="fas fa-warehouse stat-icon <?php echo $stats['ton_kho']['color']; ?>"></i>
                        <h5>Cảnh báo tồn kho</h5>
                        <h3><?php echo $stats['ton_kho']['so']; ?></h3>
                        <p>Sản phẩm sắp hết hàng</p>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card stat-card">
                    <div class="card-body">
                        <i class="fas fa-exclamation-triangle stat-icon <?php echo $stats['san_pham_het']['color']; ?>"></i>
                        <h5>Hết hàng</h5>
                        <h3><?php echo $stats['san_pham_het']['so']; ?></h3>
                        <p>Sản phẩm đã hết hàng</p>
                    </div>
                </div>
            </div>                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                               
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>