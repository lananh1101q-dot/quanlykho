 <?php
session_start();
require_once __DIR__ . '/db.php';

if (!isset($_SESSION['user']) || !isset($_GET['id'])) {
    header("Location: loaikhachhang.php");
    exit;
}

$user = $_SESSION['user'];
$id = $_GET['id'];

$stmt = $pdo->prepare("SELECT * FROM loaikhachhang WHERE Maloaikh = ?");
$stmt->execute([$id]);
$type = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$type) {
    header("Location: loaikhachhang.php");
    exit;
}

$error = $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tenloaikh = trim($_POST['tenloaikh']);
    $motaloaikh = trim($_POST['motaloaikh']);

    if (empty($tenloaikh)) {
        $error = "Tên loại không được để trống!";
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE loaikhachhang SET Tenloaikh = ?, Motaloaikh = ? WHERE Maloaikh = ?");
            $stmt->execute([$tenloaikh, $motaloaikh, $id]);
            $success = "Cập nhật loại khách hàng thành công!";
        } catch (PDOException $e) {
            $error = "Lỗi: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sửa Loại Khách Hàng</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Giữ nguyên style sidebar -->
    <style>
        body { background-color: #f8f9fa; }
        .sidebar { background-color: #007bff; height: 100vh; position: fixed; width: 250px; color: white; padding-top: 20px; }
        .sidebar .nav-link { color: white !important; padding: 12px 20px; }
        .sidebar .nav-link:hover { background-color: #0069d9; font-weight: bold; transform: translateX(8px); }
        .main-content { margin-left: 250px; padding: 20px; }
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
    <h2 class="mb-4"><i class="fas fa-edit me-2"></i> Sửa Loại Khách Hàng: <?php echo htmlspecialchars($type['Tenloaikh']); ?></h2>

    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?php echo $success; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?php echo $error; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Mã loại (không sửa được)</label>
                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($type['Maloaikh']); ?>" disabled>
                </div>
                <div class="mb-3">
                    <label class="form-label">Tên loại khách hàng <span class="text-danger">*</span></label>
                    <input type="text" name="tenloaikh" class="form-control" required value="<?php echo htmlspecialchars($type['Tenloaikh']); ?>">
                </div>
                <div class="mb-4">
                    <label class="form-label">Mô tả</label>
                    <textarea name="motaloaikh" class="form-control" rows="3"><?php echo htmlspecialchars($type['Motaloaikh']); ?></textarea>
                </div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Cập nhật</button>
                <a href="loaikhachhang.php" class="btn btn-secondary">Quay lại</a>
            </form>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>