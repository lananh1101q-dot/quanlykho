<?php
session_start();
require_once __DIR__ . '/db.php'; // File kết nối PDO của bạn

// Kiểm tra đăng nhập
if (!isset($_SESSION['user'])) {
    header("Location: dangnhap.php");
    exit;
}

$user = $_SESSION['user'];

// Xử lý tìm kiếm
$search = trim($_GET['search'] ?? '');

if ($search !== '') {
    $stmt = $pdo->prepare("SELECT * FROM loaikhachhang 
                           WHERE Tenloaikh LIKE ? 
                              OR Motaloaikh LIKE ? 
                           ORDER BY Maloaikh");
    $param = "%$search%";
    $stmt->execute([$param, $param]);
} else {
    $stmt = $pdo->prepare("SELECT * FROM loaikhachhang ORDER BY Maloaikh");
    $stmt->execute();
}

$customerTypes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$page_title = "Loại Khách Hàng - Quản Lý Kho Hàng";
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
        
        .sidebar .nav-link.active {
            background-color: #0056b3;
            font-weight: bold;
        }
        
        .main-content { 
            margin-left: 250px; 
            padding: 20px; 
        }
        
        @media (max-width: 768px) { 
            .sidebar { width: 100%; height: auto; position: relative; }
            .main-content { margin-left: 0; }
        }
        
        .badge-type {
            font-size: 0.9em;
            padding: 0.5em 1em;
        }
    </style>
</head>
<body>
 <nav class="sidebar">
        <div class="text-center mb-4">
            <h4><i class="fas fa-warehouse"></i> Quản Lý Kho</h4>
            <p class="small">Xin chào, <strong><?php echo htmlspecialchars($user['fullname']); ?></strong></p>
        </div>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link" href="trangchu.php"><i class="fas fa-home"></i> Trang Chủ</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="Sanpham.php"><i class="fas fa-box"></i> Quản lý sản phẩm</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="phieu_nhap.php"><i class="fas fa-file-import"></i> Phiếu nhập kho</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#"><i class="fas fa-chart-bar"></i> Báo cáo & Thống kê</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="khachhang.php"><i class="fas fa-users"></i> Khách hàng</a>
            </li>
            <li class="nav-item">
            <a class="nav-link" href="loaikhachhang.php"><i class="fas fa-tag"></i> Loại khách hàng</a>
        </li>
            <li class="nav-item">
                <a class="nav-link text-danger" href="logout.php"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a>
            </li>
        </ul>
    </nav>

<main class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-tag me-2"></i> Loại Khách Hàng</h2>
        <a href="them_loaikhachhang.php" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Tạo loại khách hàng
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="d-flex justify-content-end mb-3">
                <form action="" method="GET" class="input-group" style="max-width: 400px;">
                    <input type="text" name="search" class="form-control" placeholder="Tìm kiếm loại khách hàng..." 
                           value="<?= htmlspecialchars($search) ?>">
                    <button class="btn btn-outline-secondary" type="submit">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Mã loại</th>
                            <th>Tên loại</th>
                            <th>Mô tả</th>
                            <th class="text-center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($customerTypes) > 0): ?>
                            <?php foreach ($customerTypes as $row): ?>
                            <tr>
                                <td class="fw-bold"><?= htmlspecialchars($row['Maloaikh']) ?></td>
                                <td>
                                    <span class="badge bg-primary badge-type">
                                        <?= htmlspecialchars($row['Tenloaikh']) ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($row['Motaloaikh']) ?></td>
                                <td class="text-center">
                                    <a href="sua_loaikhachhang.php?id=<?= $row['Maloaikh'] ?>" 
                                       class="btn btn-sm btn-outline-primary" title="Sửa">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="xoa_loaikhachhang.php?id=<?= $row['Maloaikh'] ?>" 
                                       class="btn btn-sm btn-outline-danger" 
                                       onclick="return confirm('Bạn có chắc muốn xóa loại khách hàng này?')" 
                                       title="Xóa">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center py-5 text-muted">
                                    <i class="fas fa-inbox fa-3x mb-3"></i><br>
                                    Không có dữ liệu loại khách hàng.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>