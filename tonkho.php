<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: dangnhap.php');
    exit;
}
require_once __DIR__ . '/db.php';

$sql = "SELECT tk.Makho, k.Tenkho, tk.Masp, sp.Tensp, sp.Dvt, tk.Soluongton
        FROM Tonkho tk
        JOIN Kho k ON tk.Makho = k.Makho
        JOIN Sanpham sp ON tk.Masp = sp.Masp
        ORDER BY k.Tenkho, sp.Tensp";
$rows = $pdo->query($sql)->fetchAll();
?>
<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Báo cáo tồn kho</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-900 min-h-screen text-slate-100">
  <div class="max-w-6xl mx-auto p-6 space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold">Báo cáo tồn kho</h1>
        <p class="text-slate-400 text-sm mt-1">Danh sách tồn kho theo kho và sản phẩm</p>
      </div>
      <div class="flex gap-2 text-sm">
        <a href="dashboard.php" class="px-3 py-2 rounded bg-slate-800 hover:bg-slate-700">← Dashboard</a>
        <a href="logout.php" class="px-3 py-2 rounded bg-red-600 hover:bg-red-700">Đăng xuất</a>
      </div>
    </div>

    <div class="bg-slate-800 rounded-lg border border-slate-700 overflow-auto">
      <table class="min-w-full text-sm">
        <thead class="bg-slate-900 text-slate-300">
          <tr>
            <th class="px-4 py-3 text-left">Kho</th>
            <th class="px-4 py-3 text-left">Mã SP</th>
            <th class="px-4 py-3 text-left">Tên sản phẩm</th>
            <th class="px-4 py-3 text-left">ĐVT</th>
            <th class="px-4 py-3 text-right">Số lượng tồn</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($rows)): ?>
            <tr><td colspan="5" class="px-4 py-4 text-center text-slate-400">Chưa có dữ liệu tồn kho.</td></tr>
          <?php else: ?>
            <?php foreach ($rows as $r): ?>
              <tr class="border-t border-slate-800">
                <td class="px-4 py-2">[<?= htmlspecialchars($r['Makho']) ?>] <?= htmlspecialchars($r['Tenkho']) ?></td>
                <td class="px-4 py-2"><?= htmlspecialchars($r['Masp']) ?></td>
                <td class="px-4 py-2"><?= htmlspecialchars($r['Tensp']) ?></td>
                <td class="px-4 py-2"><?= htmlspecialchars($r['Dvt']) ?></td>
                <td class="px-4 py-2 text-right font-semibold"><?= number_format($r['Soluongton']) ?></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</body>
</html>
