<?php
session_start();
require_once __DIR__ . '/db.php'; // Đảm bảo file db.php kết nối đúng PDO

// 1. Kiểm tra đăng nhập
if (!isset($_SESSION['user'])) {
    header("Location: dangnhap.php");
    exit;
}

$user = $_SESSION['user'];

// 2. Xử lý tìm kiếm và lấy dữ liệu (Sửa tên cột theo Database của bạn)
$search = trim($_GET['search'] ?? '');

if ($search !== '') {
    $stmt = $pdo->prepare("SELECT * FROM khachhang 
                           WHERE Makh LIKE ? 
                              OR Tenkh LIKE ? 
                              OR Sdtkh LIKE ?");
    $param = "%$search%";
    $stmt->execute([$param, $param, $param]);
} else {
    $stmt = $pdo->prepare("SELECT * FROM khachhang ORDER BY Makh");
    $stmt->execute();
}

$customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

$page_title = "Quản Lý Khách Hàng - Quản Lý Kho Hàng";
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
                    <li class="nav-item">
                        <a class="nav-link" href="Sanpham.php">
                            <i class="fas fa-cube"></i> Sản phẩm
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="dmsp.php">
                            <i class="fas fa-tags"></i> Danh mục sản phẩm
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="Nhacungcap.php">
                            <i class="fas fa-truck"></i> Nhà cung cấp
                        </a>
                    </li>
                </ul>
            </li>


            <li class="nav-item">
                <a class="nav-link" href="phieu_nhap.php"><i class="fas fa-file-import"></i> Phiếu nhập kho</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="javascript:void(0)" id="btnBaoCao">
                    <i class="fas fa-chart-bar"></i> Báo cáo & Thống kê
                    <i class="fas fa-chevron-down float-end"></i>
                </a>

                <ul class="nav flex-column ms-3 d-none" id="submenuBaoCao">
                    <li class="nav-item">
                        <a class="nav-link" href="baocao_banhang.php">
                            <i class="fas fa-cash-register"></i> Báo cáo bán hàng
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="tonkho.php">
                            <i class="fas fa-warehouse"></i> Báo cáo tồn kho
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="baocao_khachhang.php">
                            <i class="fas fa-users"></i> Báo cáo khách hàng
                        </a>
                    </li>
                </ul>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="khachhang.php"><i class="fas fa-users"></i> Khách hàng</a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-danger" href="logout.php"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a>
            </li>
        </ul>
    </nav>

    <main class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Quản Lý Khách Hàng</h2>
            <a href="them_khachhang.php" class="btn btn-primary"><i class="fas fa-plus me-2"></i>Thêm khách hàng</a>
        </div>

        <div class="card-table">
            <div class="d-flex justify-content-end mb-3">
                <form action="" method="GET" class="search-box input-group">
                    <input type="text" name="search" class="form-control" placeholder="Tìm kiếm..." value="<?= htmlspecialchars($search) ?>">
                    <button class="btn btn-outline-secondary" type="submit"><i class="fas fa-search"></i></button>
                </form>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Mã KH</th>
                            <th>Tên khách hàng</th>
                            <th>Số điện thoại</th>
                            <th>Địa chỉ</th>
                            <th>Loại KH</th>
                            <th class="text-center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($customers as $row): ?>
                        <tr>
                            <td class="fw-bold"><?= $row['Makh'] ?></td>
                            <td><?= htmlspecialchars($row['Tenkh']) ?></td>
                            <td><?= $row['Sdtkh'] ?></td>
                            <td><?= htmlspecialchars($row['Diachikh']) ?></td>
                            <td>
                                <span class="badge bg-info text-dark">Loại <?= $row['Maloaikh'] ?></span>
                            </td>
                            <td class="text-center">
                                <a href="sua_khachhang.php?id=<?= $row['Makh'] ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                                <a href="xoa_khachhang.php?id=<?= $row['Makh'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Xóa khách hàng này?')"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <?php if (empty($customers)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">Không có dữ liệu khách hàng.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>