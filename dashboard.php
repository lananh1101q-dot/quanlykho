<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: dangnhap.php");
    exit;
}
$user = $_SESSION['user'];
require_once __DIR__ . '/db.php';
// Tổng số sản phẩm
$totalProducts = $pdo->query("SELECT COUNT(*) FROM Sanpham")->fetchColumn();
// Tổng tồn kho (tổng số lượng tồn của tất cả sản phẩm ở tất cả kho)
$totalStock = $pdo->query("SELECT SUM(Soluongton) FROM Tonkho")->fetchColumn();
// Số đơn nhập hôm nay
$today = date('Y-m-d');
$totalOrdersToday = $pdo->prepare("SELECT COUNT(*) FROM Phieunhap WHERE Ngaynhaphang = ?");
$totalOrdersToday->execute([$today]);
$totalOrdersToday = $totalOrdersToday->fetchColumn();
?>
<!doctype html>
<html lang="vi">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>Dashboard — Quản lý kho</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-900 min-h-screen text-slate-100">
  <div class="max-w-6xl mx-auto p-6 space-y-8">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold">Bảng điều khiển</h1>
        <p class="text-slate-400 text-sm mt-1">Xin chào, <?= htmlspecialchars($user['fullname'] ?: $user['username']) ?></p>
      </div>
      <div class="flex items-center gap-3">
        <span class="px-3 py-1 rounded bg-slate-800 text-slate-200 text-sm"><?= htmlspecialchars($user['role'] ?? 'N/A') ?></span>
        <a href="logout.php" class="bg-red-600 px-4 py-2 rounded hover:bg-red-700 text-sm font-semibold">Đăng xuất</a>
      </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div class="bg-slate-800 rounded-lg p-4 shadow">
        <div class="text-slate-400 text-sm">Tổng sản phẩm</div>
        <div class="text-3xl font-semibold mt-2"><?= number_format($totalProducts) ?></div>
      </div>
      <div class="bg-slate-800 rounded-lg p-4 shadow">
        <div class="text-slate-400 text-sm">Tồn kho</div>
        <div class="text-3xl font-semibold mt-2"><?= number_format($totalStock) ?></div>
      </div>
      <div class="bg-slate-800 rounded-lg p-4 shadow">
        <div class="text-slate-400 text-sm">Đơn hàng hôm nay</div>
        <div class="text-3xl font-semibold mt-2"><?= number_format($totalOrdersToday) ?></div>
      </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div class="bg-slate-800 rounded-lg p-4 shadow">
        <div class="text-slate-200 font-semibold mb-3">Tác vụ nhanh</div>
        <div class="flex flex-col gap-3">
          <a href="phieu_nhap.php" class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded bg-sky-600 hover:bg-sky-700 text-sm font-semibold">+ Tạo phiếu nhập</a>
          <a href="danh_sach_phieu_nhap.php" class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded bg-blue-600 hover:bg-blue-700 text-sm font-semibold">Danh sách phiếu nhập</a>
          <a href="tonkho.php" class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded bg-emerald-600 hover:bg-emerald-700 text-sm font-semibold">Báo cáo tồn kho</a>
        </div>
      </div>
      <div class="bg-slate-800 rounded-lg p-4 shadow">
        <div class="text-slate-200 font-semibold mb-3">Thống kê thêm</div>
        <?php
        // Tổng số phiếu nhập
        $totalPhieuNhap = $pdo->query("SELECT COUNT(*) FROM Phieunhap")->fetchColumn();
        // Tổng giá trị nhập kho
        $totalValueNhap = $pdo->query("SELECT SUM(Tongtiennhap) FROM Phieunhap")->fetchColumn() ?: 0;
        // Số nhà cung cấp
        $totalNCC = $pdo->query("SELECT COUNT(*) FROM Nhacungcap")->fetchColumn();
        ?>
        <div class="space-y-2 text-sm">
          <div class="flex justify-between">
            <span class="text-slate-400">Tổng phiếu nhập:</span>
            <span class="font-semibold"><?= number_format($totalPhieuNhap) ?></span>
          </div>
          <div class="flex justify-between">
            <span class="text-slate-400">Tổng giá trị nhập:</span>
            <span class="font-semibold text-emerald-400"><?= number_format($totalValueNhap, 0, ',', '.') ?> đ</span>
          </div>
          <div class="flex justify-between">
            <span class="text-slate-400">Số nhà cung cấp:</span>
            <span class="font-semibold"><?= number_format($totalNCC) ?></span>
          </div>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
