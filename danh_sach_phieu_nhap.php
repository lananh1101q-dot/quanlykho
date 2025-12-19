<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: dangnhap.php');
    exit;
}
require_once __DIR__ . '/db.php';

// =========================
// Xử lý xóa phiếu nhập
// =========================
if (isset($_GET['xoa']) && !empty($_GET['xoa'])) {
    $manhap = trim($_GET['xoa']);
    try {
        $pdo->beginTransaction();
        
        // Lấy thông tin phiếu nhập để lấy Makho
        $phieuNhap = $pdo->prepare("SELECT Makho FROM Phieunhap WHERE Manhaphang = ?");
        $phieuNhap->execute([$manhap]);
        $phieuNhap = $phieuNhap->fetch();
        
        if ($phieuNhap && $phieuNhap['Makho']) {
            $makho = $phieuNhap['Makho'];
            
            // Lấy chi tiết phiếu nhập để cập nhật lại tồn kho
            $chiTiet = $pdo->prepare("SELECT Masp, Soluong FROM Chitiet_Phieunhap WHERE Manhaphang = ?");
            $chiTiet->execute([$manhap]);
            $chiTietRows = $chiTiet->fetchAll();
            
            // Giảm số lượng tồn kho
            foreach ($chiTietRows as $ct) {
                $stmtTonkho = $pdo->prepare("
                    UPDATE Tonkho 
                    SET Soluongton = Soluongton - :sl
                    WHERE Makho = :makho AND Masp = :masp AND Soluongton >= :sl_check
                ");
                $stmtTonkho->execute([
                    ':makho' => $makho,
                    ':masp' => $ct['Masp'],
                    ':sl' => $ct['Soluong'],
                    ':sl_check' => $ct['Soluong'],
                ]);
            }
        }
        
        // Xóa chi tiết phiếu nhập
        $pdo->prepare("DELETE FROM Chitiet_Phieunhap WHERE Manhaphang = ?")->execute([$manhap]);
        // Xóa phiếu nhập
        $pdo->prepare("DELETE FROM Phieunhap WHERE Manhaphang = ?")->execute([$manhap]);
        
        $pdo->commit();
        header("Location: danh_sach_phieu_nhap.php?success=xoa");
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        header("Location: danh_sach_phieu_nhap.php?error=" . urlencode($e->getMessage()));
        exit;
    }
}

// =========================
// Bộ lọc tìm kiếm
// =========================
$maSearch     = trim($_GET['ma'] ?? '');
$nccSearch    = trim($_GET['mancc'] ?? '');
$khoSearch    = trim($_GET['makho'] ?? '');
$dateFrom     = trim($_GET['from'] ?? '');
$dateTo       = trim($_GET['to'] ?? '');

// Lấy danh sách NCC & Kho cho dropdown lọc
$nhacungcaps = $pdo->query("SELECT Mancc, Tenncc FROM Nhacungcap ORDER BY Tenncc")->fetchAll();
$khos        = $pdo->query("SELECT Makho, Tenkho FROM Kho ORDER BY Tenkho")->fetchAll();

// Xây dựng SQL với điều kiện lọc
$sql = "SELECT pn.*, ncc.Tenncc, k.Tenkho,
        (SELECT COUNT(*) FROM Chitiet_Phieunhap WHERE Manhaphang = pn.Manhaphang) as SoMatHang
        FROM Phieunhap pn
        LEFT JOIN Nhacungcap ncc ON pn.Mancc = ncc.Mancc
        LEFT JOIN Kho k ON pn.Makho = k.Makho
        WHERE 1=1";

$params = [];

if ($maSearch !== '') {
    $sql .= " AND pn.Manhaphang LIKE :ma";
    $params[':ma'] = '%' . $maSearch . '%';
}

if ($nccSearch !== '') {
    $sql .= " AND pn.Mancc = :mancc";
    $params[':mancc'] = $nccSearch;
}

if ($khoSearch !== '') {
    $sql .= " AND pn.Makho = :makho";
    $params[':makho'] = $khoSearch;
}

if ($dateFrom !== '') {
    $sql .= " AND pn.Ngaynhaphang >= :from";
    $params[':from'] = $dateFrom;
}

if ($dateTo !== '') {
    $sql .= " AND pn.Ngaynhaphang <= :to";
    $params[':to'] = $dateTo;
}

$sql .= " ORDER BY pn.Ngaynhaphang DESC, pn.Manhaphang DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$phieuNhaps = $stmt->fetchAll();

$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';
?>
<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Danh sách phiếu nhập</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-900 min-h-screen text-slate-100">
  <div class="max-w-7xl mx-auto p-6 space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold">Danh sách phiếu nhập</h1>
        <p class="text-slate-400 text-sm mt-1">Quản lý các phiếu nhập kho</p>
      </div>
      <div class="flex gap-2 text-sm">
        <a href="phieu_nhap.php" class="px-4 py-2 rounded bg-sky-600 hover:bg-sky-700 font-semibold">+ Tạo phiếu nhập</a>
        <a href="dashboard.php" class="px-3 py-2 rounded bg-slate-800 hover:bg-slate-700">← Dashboard</a>
        <a href="logout.php" class="px-3 py-2 rounded bg-red-600 hover:bg-red-700">Đăng xuất</a>
      </div>
    </div>

    <?php if ($success === 'xoa'): ?>
      <div class="bg-emerald-900/60 border border-emerald-700 text-emerald-100 px-4 py-3 rounded">
        Đã xóa phiếu nhập thành công.
      </div>
    <?php endif; ?>

    <?php if ($error): ?>
      <div class="bg-red-900/60 border border-red-700 text-red-200 px-4 py-3 rounded">
        Lỗi: <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <!-- Form tìm kiếm -->
    <form method="get" class="bg-slate-800 border border-slate-700 rounded-lg p-4 space-y-3 text-sm">
      <div class="grid md:grid-cols-5 gap-3">
        <div>
          <label class="block text-slate-300 mb-1">Mã phiếu</label>
          <input name="ma" value="<?= htmlspecialchars($maSearch) ?>" class="w-full px-3 py-2 rounded bg-slate-900 border border-slate-700" placeholder="Nhập mã phiếu..." />
        </div>
        <div>
          <label class="block text-slate-300 mb-1">Nhà cung cấp</label>
          <select name="mancc" class="w-full px-3 py-2 rounded bg-slate-900 border border-slate-700">
            <option value="">-- Tất cả --</option>
            <?php foreach ($nhacungcaps as $ncc): ?>
              <option value="<?= htmlspecialchars($ncc['Mancc']) ?>" <?= $nccSearch === $ncc['Mancc'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($ncc['Tenncc']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="block text-slate-300 mb-1">Kho</label>
          <select name="makho" class="w-full px-3 py-2 rounded bg-slate-900 border border-slate-700">
            <option value="">-- Tất cả --</option>
            <?php foreach ($khos as $kho): ?>
              <option value="<?= htmlspecialchars($kho['Makho']) ?>" <?= $khoSearch === $kho['Makho'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($kho['Tenkho']) ?> [<?= htmlspecialchars($kho['Makho']) ?>]
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="block text-slate-300 mb-1">Từ ngày</label>
          <input type="date" name="from" value="<?= htmlspecialchars($dateFrom) ?>" class="w-full px-3 py-2 rounded bg-slate-900 border border-slate-700" />
        </div>
        <div>
          <label class="block text-slate-300 mb-1">Đến ngày</label>
          <input type="date" name="to" value="<?= htmlspecialchars($dateTo) ?>" class="w-full px-3 py-2 rounded bg-slate-900 border border-slate-700" />
        </div>
      </div>
      <div class="flex items-center gap-2 pt-1">
        <button type="submit" class="px-4 py-2 rounded bg-emerald-600 hover:bg-emerald-700 text-slate-900 font-semibold">
          Tìm kiếm
        </button>
        <a href="danh_sach_phieu_nhap.php" class="px-3 py-2 rounded bg-slate-700 hover:bg-slate-600 text-slate-100">
          Xóa lọc
        </a>
      </div>
    </form>

    <div class="bg-slate-800 rounded-lg border border-slate-700 overflow-auto">
      <table class="min-w-full text-sm">
        <thead class="bg-slate-900 text-slate-300">
          <tr>
            <th class="px-4 py-3 text-left">Mã phiếu</th>
            <th class="px-4 py-3 text-left">Nhà cung cấp</th>
            <th class="px-4 py-3 text-left">Kho</th>
            <th class="px-4 py-3 text-left">Ngày nhập</th>
            <th class="px-4 py-3 text-right">Số mặt hàng</th>
            <th class="px-4 py-3 text-right">Tổng tiền</th>
            <th class="px-4 py-3 text-left">Ghi chú</th>
            <th class="px-4 py-3 text-center">Thao tác</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($phieuNhaps)): ?>
            <tr><td colspan="8" class="px-4 py-4 text-center text-slate-400">Chưa có phiếu nhập nào.</td></tr>
          <?php else: ?>
            <?php foreach ($phieuNhaps as $pn): ?>
              <tr class="border-t border-slate-800 hover:bg-slate-700/50">
                <td class="px-4 py-2 font-semibold"><?= htmlspecialchars($pn['Manhaphang']) ?></td>
                <td class="px-4 py-2"><?= htmlspecialchars($pn['Tenncc'] ?? 'N/A') ?></td>
                <td class="px-4 py-2"><?= htmlspecialchars($pn['Tenkho'] ?? 'N/A') ?></td>
                <td class="px-4 py-2"><?= date('d/m/Y', strtotime($pn['Ngaynhaphang'])) ?></td>
                <td class="px-4 py-2 text-right"><?= number_format($pn['SoMatHang']) ?></td>
                <td class="px-4 py-2 text-right font-semibold"><?= number_format($pn['Tongtiennhap'], 0, ',', '.') ?> đ</td>
                <td class="px-4 py-2 text-slate-400"><?= htmlspecialchars(mb_substr($pn['Ghichu'] ?? '', 0, 50)) ?><?= mb_strlen($pn['Ghichu'] ?? '') > 50 ? '...' : '' ?></td>
                <td class="px-4 py-2">
                  <div class="flex items-center justify-center gap-2">
                    <a href="sua_phieu_nhap.php?id=<?= urlencode($pn['Manhaphang']) ?>" class="px-3 py-1 rounded bg-blue-600 hover:bg-blue-700 text-xs font-semibold">Sửa</a>
                    <a href="chi_tiet_phieu_nhap.php?id=<?= urlencode($pn['Manhaphang']) ?>" class="px-3 py-1 rounded bg-emerald-600 hover:bg-emerald-700 text-xs font-semibold">Chi tiết</a>
                    <a href="?xoa=<?= urlencode($pn['Manhaphang']) ?>" 
                       onclick="return confirm('Bạn có chắc chắn muốn xóa phiếu nhập này? Hành động này sẽ giảm số lượng tồn kho.')" 
                       class="px-3 py-1 rounded bg-red-600 hover:bg-red-700 text-xs font-semibold">Xóa</a>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</body>
</html>
