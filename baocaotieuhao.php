<?php

session_start();
require_once __DIR__ . '/db.php';

if (!isset($_SESSION['user'])) {
    header("Location: dangnhap.php");
    exit;
}

$user = $_SESSION['user'];

// Tìm kiếm
$search_ct = trim($_GET['search_ct'] ?? '');
$search_sp = trim($_GET['search_sp'] ?? '');
$search_ngay = trim($_GET['search_ngay'] ?? '');

$sql = "SELECT b.*, c.Tenct, s.Tensp, k.Tenkho 
        FROM Baocaotieuhao b
        JOIN Congtrinh c ON b.Mact = c.Mact
        JOIN Sanpham s ON b.Masp = s.Masp
        JOIN Kho k ON b.Makho = k.Makho
        WHERE 1=1";
$params = [];

if ($search_ct !== '') {
    $sql .= " AND b.Mact LIKE ?";
    $params[] = "%$search_ct%";
}

if ($search_sp !== '') {
    $sql .= " AND b.Masp LIKE ?";
    $params[] = "%$search_sp%";
}

if ($search_ngay !== '') {
    $sql .= " AND b.Ngaybaocao = ?";
    $params[] = $search_ngay;
}

$sql .= " ORDER BY b.Ngaybaocao DESC, b.Mact";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$baocaos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Lấy danh sách công trình & sản phẩm cho dropdown
$congtrinhs = $pdo->query("SELECT Mact, Tenct FROM Congtrinh ORDER BY Tenct")->fetchAll();
$sanphams = $pdo->query("SELECT Masp, Tensp FROM Sanpham ORDER BY Tensp")->fetchAll();
$khos = $pdo->query("SELECT Makho, Tenkho FROM Kho ORDER BY Tenkho")->fetchAll();

$page_title = "Báo Cáo Tiêu Hao - Quản Lý Kho Hàng";
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
            <a class="nav-link" href="javascript:void(0)" id="btnBaoCao">
                <i class="fas fa-chart-bar"></i> Báo cáo & Thống kê
                <i class="fas fa-chevron-down float-end"></i>
            </a>
            <ul class="nav flex-column ms-3" id="submenuBaoCao">
                <li class="nav-item"><a class="nav-link" href="tonkho.php"><i class="fas fa-warehouse"></i> Báo cáo tồn kho</a></li>
                <li class="nav-item"><a class="nav-link" href="baocaotieuhao.php"><i class="fas fa-chart-line"></i> Báo cáo tiêu hao</a></li>
            </ul>
        </li>

        <li class="nav-item">
            <a class="nav-link text-danger" href="logout.php"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a>
        </li>
    </ul>
</nav>

  <main class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-chart-line me-2"></i>Báo Cáo Tiêu Hao Vật Liệu</h2>
        <a href="them_baocaotieuhao.php" class="btn btn-success"><i class="fas fa-plus me-2"></i>Thêm báo cáo</a>
    </div>

    <form action="" method="GET" class="mb-4">
        <div class="row g-3">
            <div class="col-md-3">
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-building"></i></span>
                    <input type="text" name="search_ct" class="form-control" placeholder="Mã công trình..." 
                           value="<?= htmlspecialchars($search_ct ?? '') ?>">
                </div>
            </div>

            <div class="col-md-3">
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-box"></i></span>
                    <input type="text" name="search_sp" class="form-control" placeholder="Mã sản phẩm..." 
                           value="<?= htmlspecialchars($search_sp ?? '') ?>">
                </div>
            </div>

            <div class="col-md-3">
                <input type="date" name="search_ngay" class="form-control" 
                       value="<?= htmlspecialchars($search_ngay ?? '') ?>">
            </div>

            <div class="col-md-3">
                <button type="submit" class="btn btn-dark w-100">
                    <i class="fas fa-search me-2"></i>Tìm kiếm
                </button>
            </div>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Công Trình</th>
                    <th>Sản Phẩm</th>
                    <th>Kho</th>
                    <th>Ngày Báo Cáo</th>
                    <th class="text-end">SL Kế Hoạch</th>
                    <th class="text-end">SL Thực Tế</th>
                    <th class="text-end">SL Không Dùng</th>
                    <th class="text-center">Thao Tác</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($baocaos as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['Id']) ?></td>
                    <td>
                        <strong><?= htmlspecialchars($row['Mact']) ?></strong><br>
                        <small class="text-muted"><?= htmlspecialchars($row['Tenct']) ?></small>
                    </td>
                    <td>
                        <strong><?= htmlspecialchars($row['Masp']) ?></strong><br>
                        <small class="text-muted"><?= htmlspecialchars($row['Tensp']) ?></small>
                    </td>
                    <td><?= htmlspecialchars($row['Tenkho']) ?></td>
                    <td><?= date('d/m/Y', strtotime($row['Ngaybaocao'])) ?></td>
                    <td class="text-end"><?= number_format($row['Soluongkehoach'], 2) ?></td>
                    <td class="text-end fw-bold text-success"><?= number_format($row['Soluongthuc'], 2) ?></td>
                    <td class="text-end text-warning"><?= number_format($row['Soluongkhongdung'], 2) ?></td>
                    <td class="text-center">
                        <a href="sua_baocaotieuhao.php?id=<?= htmlspecialchars($row['Id']) ?>" 
                           class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                        <a href="xoa_baocaotieuhao.php?id=<?= htmlspecialchars($row['Id']) ?>" 
                           class="btn btn-sm btn-outline-danger" 
                           onclick="return confirm('Bạn có chắc muốn xóa báo cáo này?')"><i class="fas fa-trash"></i></a>
                    </td>
                </tr>
                <?php endforeach; ?>

                <?php if (empty($baocaos)): ?>
                <tr>
                    <td colspan="9" class="text-center py-4 text-muted">Không tìm thấy báo cáo nào.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.getElementById("btnBaoCao").addEventListener("click", function () {
        document.getElementById("submenuBaoCao").classList.toggle("d-none");
    });
</script>
</body>
</html>