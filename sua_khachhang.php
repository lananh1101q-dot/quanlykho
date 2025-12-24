<?php
session_start();
require_once __DIR__ . '/db.php';

if (!isset($_SESSION['user'])) {
    header("Location: dangnhap.php");
    exit;
}

$user = $_SESSION['user'];
$makh_edit = $_GET['id'] ?? '';

// Lấy dữ liệu cũ
$stmt = $pdo->prepare("SELECT * FROM khachhang WHERE Makh = ?");
$stmt->execute([$makh_edit]);
$customer = $stmt->fetch();

if (!$customer) {
    die("Không tìm thấy khách hàng!");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tenkh = $_POST['tenkh'];
    $sdtkh = $_POST['sdtkh'];
    $diachikh = $_POST['diachikh'];
    $maloaikh = $_POST['maloaikh'];

    try {
        $sql = "UPDATE khachhang SET Tenkh = ?, Sdtkh = ?, Diachikh = ?, Maloaikh = ? WHERE Makh = ?";
        $pdo->prepare($sql)->execute([$tenkh, $sdtkh, $diachikh, $maloaikh, $makh_edit]);
        header("Location: khachhang.php");
        exit;
    } catch (PDOException $e) {
        $error = "Lỗi cập nhật: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Sửa Khách Hàng</title>
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
                <a class="nav-link text-danger" href="logout.php"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a>
            </li>
        </ul>
    </nav>

    <main class="main-content">
        <h2 class="mb-4">Sửa Khách Hàng: <?= htmlspecialchars($customer['Makh']) ?></h2>
        <div class="form-card">
            <form method="POST">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Tên Khách Hàng</label>
                        <input type="text" name="tenkh" class="form-control" value="<?= htmlspecialchars($customer['Tenkh']) ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Số Điện Thoại</label>
                        <input type="text" name="sdtkh" class="form-control" value="<?= $customer['Sdtkh'] ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Mã Loại KH</label>
                        <input type="number" name="maloaikh" class="form-control" value="<?= $customer['Maloaikh'] ?>" required>
                    </div>
                    <div class="col-md-12 mb-3">
                        <label class="form-label">Địa Chỉ</label>
                        <textarea name="diachikh" class="form-control" rows="2"><?= htmlspecialchars($customer['Diachikh']) ?></textarea>
                    </div>
                </div>
                <button type="submit" class="btn btn-success px-4">Cập nhật</button>
                <a href="khachhang.php" class="btn btn-secondary px-4">Hủy bỏ</a>
            </form>
        </div>
    </main>
</body>
</html>