<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: dangnhap.php');
    exit;
}
require_once "db.php";

$errors = [];
$success = "";
$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? '';

// Lấy dữ liệu dropdown
$nhacungcaps = $pdo->query("SELECT Mancc, Tenncc FROM Nhacungcap ORDER BY Tenncc")->fetchAll();
$sanphams = $pdo->query("SELECT Masp, Tensp, Dvt FROM Sanpham ORDER BY Tensp")->fetchAll();
$khos = $pdo->query("SELECT Makho, Tenkho FROM Kho ORDER BY Tenkho")->fetchAll();

/* ====== XÓA PHIẾU ====== */
if ($action === 'delete' && $id) {
    try {
        $pdo->beginTransaction();
        // Lấy chi tiết phiếu để hoàn lại kho
        $stmt = $pdo->prepare("SELECT * FROM Chitiet_Phieunhap WHERE Manhaphang=?");
        $stmt->execute([$id]);
        $items = $stmt->fetchAll();
        
        // Lấy makho từ phiếu nhập
        $stmt = $pdo->prepare("SELECT Makho FROM Phieunhap WHERE Manhaphang=?");
        $stmt->execute([$id]);
        $phieu = $stmt->fetch();
        $makho = $phieu['Makho'] ?? '';
        
        // Hoàn lại tồn kho
        foreach ($items as $item) {
            $stmtUpdate = $pdo->prepare("
                UPDATE Tonkho 
                SET Soluongton = Soluongton - ?
                WHERE Makho = ? AND Masp = ?
            ");
            $stmtUpdate->execute([$item['Soluong'], $makho, $item['Masp']]);
        }
        
        $pdo->prepare("DELETE FROM Chitiet_Phieunhap WHERE Manhaphang=?")->execute([$id]);
        $pdo->prepare("DELETE FROM Phieunhap WHERE Manhaphang=?")->execute([$id]);
        $pdo->commit();
        header("Location: danh_sach_phieu_nhap.php?msg=deleted");
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        $errors[] = "Không thể xóa phiếu nhập";
    }
}

/* ====== LOAD DỮ LIỆU SỬA ====== */
$data = [
    'Manhaphang' => '',
    'Mancc' => '',
    'Makho' => '',
    'Ngaynhaphang' => date('Y-m-d'),
    'Ghichu' => '',
    'items' => []
];

if ($action === 'edit' && $id) {
    $stmt = $pdo->prepare("SELECT * FROM Phieunhap WHERE Manhaphang=?");
    $stmt->execute([$id]);
    $data = $stmt->fetch();

    $stmt = $pdo->prepare("SELECT * FROM Chitiet_Phieunhap WHERE Manhaphang=?");
    $stmt->execute([$id]);
    $data['items'] = $stmt->fetchAll();
}

/* ====== SUBMIT ====== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $manhap = trim($_POST['manhaphang'] ?? '');
    $mancc = trim($_POST['mancc'] ?? '');
    $makho = trim($_POST['makho'] ?? '');
    $ngaynhap = $_POST['ngaynhap'] ?? '';
    $ghichu = trim($_POST['ghichu'] ?? '');
    $maspArr = $_POST['masp'] ?? [];
    $soluongArr = $_POST['soluong'] ?? [];
    $dongiaArr = $_POST['dongia'] ?? [];

    if ($manhap === '' || $mancc === '' || $makho === '' || $ngaynhap === '') {
        $errors[] = 'Vui lòng nhập đầy đủ Mã nhập, Nhà cung cấp, Kho, Ngày nhập.';
    }

    $items = [];
    $tong = 0;
    for ($i = 0; $i < count($maspArr); $i++) {
        if (!empty($maspArr[$i]) && floatval($soluongArr[$i] ?? 0) > 0 && floatval($dongiaArr[$i] ?? 0) > 0) {
            $items[] = [
                'masp' => $maspArr[$i],
                'soluong' => floatval($soluongArr[$i]),
                'dongia' => floatval($dongiaArr[$i])
            ];
            $tong += floatval($soluongArr[$i]) * floatval($dongiaArr[$i]);
        }
    }

    if (empty($items)) {
        $errors[] = "Phải có ít nhất 1 sản phẩm";
    }

    if (!$errors) {
        try {
            $pdo->beginTransaction();

            if ($action === 'edit') {
                // Hoàn lại kho cũ
                $stmt = $pdo->prepare("SELECT * FROM Chitiet_Phieunhap WHERE Manhaphang=?");
                $stmt->execute([$id]);
                $oldItems = $stmt->fetchAll();
                
                $stmt = $pdo->prepare("SELECT Makho FROM Phieunhap WHERE Manhaphang=?");
                $stmt->execute([$id]);
                $oldPhieu = $stmt->fetch();
                $oldMakho = $oldPhieu['Makho'] ?? '';
                
                foreach ($oldItems as $oldItem) {
                    $stmtHoan = $pdo->prepare("
                        UPDATE Tonkho 
                        SET Soluongton = Soluongton - ?
                        WHERE Makho = ? AND Masp = ?
                    ");
                    $stmtHoan->execute([$oldItem['Soluong'], $oldMakho, $oldItem['Masp']]);
                }
                
                $pdo->prepare("
                    UPDATE Phieunhap
                    SET Mancc=?, Makho=?, Ngaynhaphang=?, Tongtiennhap=?, Ghichu=?
                    WHERE Manhaphang=?
                ")->execute([$mancc, $makho, $ngaynhap, $tong, $ghichu, $manhap]);

                $pdo->prepare("DELETE FROM Chitiet_Phieunhap WHERE Manhaphang=?")
                    ->execute([$manhap]);
            } else {
                $pdo->prepare("
                    INSERT INTO Phieunhap(Manhaphang, Mancc, Makho, Ngaynhaphang, Tongtiennhap, Ghichu)
                    VALUES (?,?,?,?,?,?)
                ")->execute([$manhap, $mancc, $makho, $ngaynhap, $tong, $ghichu]);
            }

            $stmtCt = $pdo->prepare("INSERT INTO Chitiet_Phieunhap (Manhaphang, Masp, Soluong, Dongianhap) VALUES (?, ?, ?, ?)");
            foreach ($items as $it) {
                $stmtCt->execute([$manhap, $it['masp'], $it['soluong'], $it['dongia']]);
                
                // Cập nhật tồn kho
                $stmtTonkho = $pdo->prepare("
                    INSERT INTO Tonkho (Makho, Masp, Soluongton) 
                    VALUES (?, ?, ?)
                    ON DUPLICATE KEY UPDATE Soluongton = Soluongton + ?
                ");
                $stmtTonkho->execute([
                    $makho,
                    $it['masp'],
                    $it['soluong'],
                    $it['soluong']
                ]);
            }

            $pdo->commit();
            $success = $action === 'edit' ? 'Cập nhật phiếu nhập thành công!' : 'Tạo phiếu nhập thành công và đã cập nhật tồn kho.';
            
            // Reset form
            $data = [
                'Manhaphang' => '',
                'Mancc' => '',
                'Makho' => '',
                'Ngaynhaphang' => date('Y-m-d'),
                'Ghichu' => '',
                'items' => []
            ];
            
        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = "Lỗi xử lý dữ liệu: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Phiếu nhập kho</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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

    .d-none {
        display: none !important;
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
                        <a class="nav-link" href="Sanpham.php"><i class="fas fa-cube"></i> Sản phẩm</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="dmsp.php"><i class="fas fa-tags"></i> Danh mục sản phẩm</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="Nhacungcap.php"><i class="fas fa-truck"></i> Nhà cung cấp</a>
                    </li>
                </ul>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="javascript:void(0)" id="btnPhieuNhap">
                    <i class="fas fa-file-import"></i> Phiếu nhập kho
                    <i class="fas fa-chevron-down float-end"></i>
                </a>
                <ul class="nav flex-column ms-3 d-none" id="submenuPhieuNhap">
                    <li class="nav-item">
                        <a class="nav-link" href="danh_sach_phieu_nhap.php"><i class="fas fa-list"></i> Danh sách phiếu nhập</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="phieu_nhap.php"><i class="fas fa-plus-circle"></i> Tạo phiếu nhập</a>
                    </li>
                </ul>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="javascript:void(0)" id="btnPhieuXuat">
                    <i class="fas fa-file-export"></i> Phiếu xuất
                    <i class="fas fa-chevron-down float-end"></i>
                </a>
                <ul class="nav flex-column ms-3 d-none" id="submenuPhieuXuat">
                    <li class="nav-item">
                        <a class="nav-link" href="danh_sach_phieu_xuat.php"><i class="fas fa-list"></i> Danh sách phiếu xuất</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="phieu_xuat.php"><i class="fas fa-plus-circle"></i> Tạo phiếu xuất</a>
                    </li>
                </ul>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="javascript:void(0)" id="btnBaoCao">
                    <i class="fas fa-chart-bar"></i> Báo cáo & Thống kê
                    <i class="fas fa-chevron-down float-end"></i>
                </a>
                <ul class="nav flex-column ms-3 d-none" id="submenuBaoCao">
                    <li class="nav-item">
                        <a class="nav-link" href="tonkho.php"><i class="fas fa-warehouse"></i> Báo cáo tồn kho</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="baocaotieuhao.php"><i class="fas fa-chart-line"></i> Báo cáo tiêu hao</a>
                    </li>
                </ul>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="congtrinh.php"><i class="fas fa-building"></i> Công trình</a>
            </li>

            <li class="nav-item">
                <a class="nav-link text-danger" href="logout.php"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a>
            </li>
        </ul>
    </nav>

    <div class="main-content">
        <div class="mb-4">
            <h2><?= $action === 'edit' ? 'Sửa phiếu nhập' : 'Tạo phiếu nhập kho' ?></h2>
            <p class="text-muted">Ghi nhận hàng nhập và chi tiết sản phẩm</p>
        </div>

        <?php if($errors): ?>
        <div class="alert alert-danger">
            <?php foreach($errors as $e): ?>
            <div>❌ <?= htmlspecialchars($e) ?></div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php if($success): ?>
        <div class="alert alert-success">
            ✅ <?= htmlspecialchars($success) ?>
            <a href="danh_sach_phieu_nhap.php" class="btn btn-sm btn-primary mt-2">Xem danh sách</a>
        </div>
        <?php endif; ?>

        <form method="post" class="card">
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Mã nhập hàng *</label>
                        <input type="text" name="manhaphang" required class="form-control" 
                               value="<?= htmlspecialchars($data['Manhaphang'] ?? '') ?>"
                               <?= $action === 'edit' ? 'readonly' : '' ?> />
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Nhà cung cấp *</label>
                        <select name="mancc" required class="form-select">
                            <option value="">-- Chọn --</option>
                            <?php foreach ($nhacungcaps as $ncc): ?>
                            <option value="<?= htmlspecialchars($ncc['Mancc']) ?>" 
                                    <?= ($data['Mancc'] === $ncc['Mancc']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($ncc['Tenncc']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Kho nhập *</label>
                        <select name="makho" required class="form-select">
                            <option value="">-- Chọn kho --</option>
                            <?php foreach ($khos as $kho): ?>
                            <option value="<?= htmlspecialchars($kho['Makho']) ?}"
                                    <?= ($data['Makho'] === $kho['Makho']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($kho['Tenkho']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Ngày nhập *</label>
                        <input type="date" name="ngaynhap" required class="form-control"
                               value="<?= htmlspecialchars($data['Ngaynhaphang'] ?? date('Y-m-d')) ?>" />
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Ghi chú</label>
                    <textarea name="ghichu" class="form-control" rows="2"><?= htmlspecialchars($data['Ghichu'] ?? '') ?></textarea>
                </div>

                <hr>
                <h5 class="mb-3">Chi tiết sản phẩm</h5>

                <div class="table-responsive mb-3">
                    <table class="table table-sm table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Sản phẩm</th>
                                <th style="width: 120px;">Số lượng</th>
                                <th style="width: 150px;">Đơn giá nhập</th>
                                <th style="width: 180px;">Thành tiền</th>
                                <th style="width: 80px;">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody id="detail-rows">
                            <?php foreach ($data['items'] as $item): ?>
                            <tr>
                                <td>
                                    <select name="masp[]" required class="form-select form-select-sm">
                                        <option value="">-- Chọn --</option>
                                        <?php foreach ($sanphams as $sp): ?>
                                        <option value="<?= htmlspecialchars($sp['Masp']) ?>"
                                                <?= ($item['Masp'] === $sp['Masp']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($sp['Tensp'] . ' (' . $sp['Dvt'] . ')') ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td>
                                    <input type="number" name="soluong[]" min="1" step="0.01" required class="form-control form-control-sm"
                                           value="<?= htmlspecialchars($item['Soluong'] ?? '') ?>" />
                                </td>
                                <td>
                                    <input type="number" name="dongia[]" min="0" step="0.01" required class="form-control form-control-sm"
                                           value="<?= htmlspecialchars($item['Dongianhap'] ?? '') ?>" />
                                </td>
                                <td>
                                    <span class="badge bg-info">
                                        <?= number_format(($item['Soluong'] ?? 0) * ($item['Dongianhap'] ?? 0), 2) ?>
                                    </span>
                                </td>
                                <td>
                                    <button type="button" onclick="removeRow(this)" class="btn btn-sm btn-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <button type="button" onclick="addRow()" class="btn btn-outline-primary btn-sm mb-3">
                    <i class="fas fa-plus me-2"></i>Thêm sản phẩm
                </button>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save me-2"></i><?= $action === 'edit' ? 'Cập nhật' : 'Lưu phiếu' ?>
                    </button>
                    <a href="danh_sach_phieu_nhap.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Quay lại
                    </a>
                </div>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const optionTemplate = `<?php
            foreach ($sanphams as $sp) {
                $label = htmlspecialchars($sp['Tensp'] . ' (' . $sp['Dvt'] . ')', ENT_QUOTES);
                $val = htmlspecialchars($sp['Masp'], ENT_QUOTES);
                echo "<option value=\"{$val}\">{$label}</option>";
            }
        ?>`;

        function addRow() {
            const tbody = document.getElementById('detail-rows');
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>
                    <select name="masp[]" required class="form-select form-select-sm">
                        <option value="">-- Chọn --</option>
                        ${optionTemplate}
                    </select>
                </td>
                <td>
                    <input type="number" name="soluong[]" min="1" step="0.01" required class="form-control form-control-sm" />
                </td>
                <td>
                    <input type="number" name="dongia[]" min="0" step="0.01" required class="form-control form-control-sm" />
                </td>
                <td>
                    <span class="badge bg-info">0.00</span>
                </td>
                <td>
                    <button type="button" onclick="removeRow(this)" class="btn btn-sm btn-danger">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            `;
            tbody.appendChild(tr);
        }

        function removeRow(btn) {
            btn.closest('tr').remove();
            if (document.getElementById('detail-rows').children.length === 0) {
                addRow();
            }
        }

        document.getElementById("btnSanPham").addEventListener("click", function () {
            document.getElementById("submenuSanPham").classList.toggle("d-none");
        });

        document.getElementById("btnPhieuNhap").addEventListener("click", function () {
            document.getElementById("submenuPhieuNhap").classList.toggle("d-none");
        });

        document.getElementById("btnPhieuXuat").addEventListener("click", function () {
            document.getElementById("submenuPhieuXuat").classList.toggle("d-none");
        });

        document.getElementById("btnBaoCao").addEventListener("click", function () {
            document.getElementById("submenuBaoCao").classList.toggle("d-none");
        });

        // Tạo dòng trống nếu chưa có
        if (document.getElementById('detail-rows').children.length === 0) {
            addRow();
        }
    </script>
</body>
</html>