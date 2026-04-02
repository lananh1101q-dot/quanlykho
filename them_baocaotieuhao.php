<?php
<?php
session_start();
require_once __DIR__ . '/db.php';

if (!isset($_SESSION['user'])) {
    header("Location: dangnhap.php");
    exit;
}

$errors = [];
$success = "";

$congtrinhs = $pdo->query("SELECT Mact, Tenct FROM Congtrinh ORDER BY Tenct")->fetchAll();
$sanphams = $pdo->query("SELECT Masp, Tensp, Dvt FROM Sanpham ORDER BY Tensp")->fetchAll();
$khos = $pdo->query("SELECT Makho, Tenkho FROM Kho ORDER BY Tenkho")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mact = trim($_POST['mact'] ?? '');
    $masp = trim($_POST['masp'] ?? '');
    $makho = trim($_POST['makho'] ?? '');
    $ngaybaocao = $_POST['ngaybaocao'] ?? date('Y-m-d');
    $soluongkehoach = floatval($_POST['soluongkehoach'] ?? 0);
    $soluongthuc = floatval($_POST['soluongthuc'] ?? 0);
    $soluongkhongdung = floatval($_POST['soluongkhongdung'] ?? 0);
    $ghichu = trim($_POST['ghichu'] ?? '');

    if (!$mact || !$masp || !$makho) {
        $errors[] = "Vui lòng chọn công trình, sản phẩm và kho";
    }

    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            // Thêm báo cáo tiêu hao
            $stmt = $pdo->prepare("
                INSERT INTO Baocaotieuhao 
                (Mact, Masp, Makho, Ngaybaocao, Soluongkehoach, Soluongthuc, Soluongkhongdung, Ghichu)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$mact, $masp, $makho, $ngaybaocao, $soluongkehoach, $soluongthuc, $soluongkhongdung, $ghichu]);

            // Cập nhật tồn kho: chỉ trừ số lượng thực tiêu hao
            if ($soluongthuc > 0) {
                $stmtUpdate = $pdo->prepare("
                    UPDATE Tonkho 
                    SET Soluongton = Soluongton - ?
                    WHERE Makho = ? AND Masp = ?
                ");
                $stmtUpdate->execute([$soluongthuc, $makho, $masp]);
            }

            $pdo->commit();
            $success = "Thêm báo cáo tiêu hao thành công và cập nhật tồn kho!";
            
            // Reset form
            $mact = $masp = $makho = $ghichu = '';
            $soluongkehoach = $soluongthuc = $soluongkhongdung = 0;
            $ngaybaocao = date('Y-m-d');

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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm Báo Cáo Tiêu Hao</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .container { max-width: 600px; }
    </style>
</head>
<body>
  <div class="container mt-5">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0"><i class="fas fa-plus-circle me-2"></i>Thêm Báo Cáo Tiêu Hao</h4>
        </div>
        <div class="card-body">
            <?php if ($errors): ?>
            <div class="alert alert-danger">
                <?php foreach ($errors as $e): ?>
                <div>❌ <?= htmlspecialchars($e) ?></div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <?php if ($success): ?>
            <div class="alert alert-success">
                ✅ <?= htmlspecialchars($success) ?>
                <a href="baocaotieuhao.php" class="btn btn-sm btn-primary mt-2">Quay lại danh sách</a>
            </div>
            <?php endif; ?>

            <form method="post" class="space-y-4">
                <div class="mb-3">
                    <label class="form-label">Công Trình *</label>
                    <select name="mact" required class="form-select">
                        <option value="">-- Chọn công trình --</option>
                        <?php foreach ($congtrinhs as $ct): ?>
                        <option value="<?= htmlspecialchars($ct['Mact']) ?>" <?= ($mact === $ct['Mact']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($ct['Tenct']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Sản Phẩm *</label>
                    <select name="masp" required class="form-select">
                        <option value="">-- Chọn sản phẩm --</option>
                        <?php foreach ($sanphams as $sp): ?>
                        <option value="<?= htmlspecialchars($sp['Masp']) ?>" <?= ($masp === $sp['Masp']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($sp['Tensp'] . ' (' . $sp['Dvt'] . ')') ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Kho *</label>
                    <select name="makho" required class="form-select">
                        <option value="">-- Chọn kho --</option>
                        <?php foreach ($khos as $kho): ?>
                        <option value="<?= htmlspecialchars($kho['Makho']) ?>" <?= ($makho === $kho['Makho']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($kho['Tenkho']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Ngày Báo Cáo *</label>
                    <input type="date" name="ngaybaocao" required class="form-control" value="<?= htmlspecialchars($ngaybaocao) ?>">
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">SL Kế Hoạch</label>
                        <input type="number" name="soluongkehoach" step="0.01" class="form-control" value="<?= $soluongkehoach ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">SL Thực Tiêu Hao *</label>
                        <input type="number" name="soluongthuc" step="0.01" required class="form-control" value="<?= $soluongthuc ?>">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">SL Không Dùng</label>
                    <input type="number" name="soluongkhongdung" step="0.01" class="form-control" value="<?= $soluongkhongdung ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">Ghi Chú</label>
                    <textarea name="ghichu" class="form-control" rows="3"><?= htmlspecialchars($ghichu) ?></textarea>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save me-2"></i>Lưu báo cáo
                    </button>
                    <a href="baocaotieuhao.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Quay lại
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>