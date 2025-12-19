<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}
require_once __DIR__ . '/db.php';

$errors = [];
$success = '';

// Lấy dữ liệu dropdown
$nhacungcaps = $pdo->query("SELECT Mancc, Tenncc FROM Nhacungcap ORDER BY Tenncc")->fetchAll();
$sanphams = $pdo->query("SELECT Masp, Tensp, Dvt FROM Sanpham ORDER BY Tensp")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $manhap = trim($_POST['manhaphang'] ?? '');
    $mancc = trim($_POST['mancc'] ?? '');
    $ngaynhap = $_POST['ngaynhap'] ?? '';
    $ghichu = trim($_POST['ghichu'] ?? '');
    $maspArr = $_POST['masp'] ?? [];
    $soluongArr = $_POST['soluong'] ?? [];
    $dongiaArr = $_POST['dongia'] ?? [];

    if ($manhap === '' || $mancc === '' || $ngaynhap === '') {
        $errors[] = 'Vui lòng nhập đầy đủ Mã nhập, Nhà cung cấp, Ngày nhập.';
    }

    // Chuẩn hóa item
    $items = [];
    for ($i = 0; $i < count($maspArr); $i++) {
        $masp = trim($maspArr[$i] ?? '');
        $soluong = (int)($soluongArr[$i] ?? 0);
        $dongia = (float)($dongiaArr[$i] ?? 0);
        if ($masp === '' || $soluong <= 0 || $dongia <= 0) {
            continue;
        }
        $items[] = [
            'masp' => $masp,
            'soluong' => $soluong,
            'dongia' => $dongia,
        ];
    }

    if (empty($items)) {
        $errors[] = 'Cần ít nhất một dòng chi tiết hợp lệ.';
    }

    if (!$errors) {
        try {
            $pdo->beginTransaction();

            // Tính tổng
            $tong = 0;
            foreach ($items as $it) {
                $tong += $it['soluong'] * $it['dongia'];
            }

            $stmtPhieu = $pdo->prepare("INSERT INTO Phieunhap (Manhaphang, Mancc, Ngaynhaphang, Tongtiennhap, Ghichu) VALUES (:ma, :ncc, :ngay, :tong, :ghichu)");
            $stmtPhieu->execute([
                ':ma' => $manhap,
                ':ncc' => $mancc,
                ':ngay' => $ngaynhap,
                ':tong' => $tong,
                ':ghichu' => $ghichu,
            ]);

            $stmtCt = $pdo->prepare("INSERT INTO Chitiet_Phieunhap (Manhaphang, Masp, Soluong, Dongianhap) VALUES (:ma, :masp, :sl, :dg)");
            foreach ($items as $it) {
                $stmtCt->execute([
                    ':ma' => $manhap,
                    ':masp' => $it['masp'],
                    ':sl' => $it['soluong'],
                    ':dg' => $it['dongia'],
                ]);
            }

            $pdo->commit();
            $success = 'Tạo phiếu nhập thành công.';
        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = 'Lỗi khi lưu phiếu: ' . htmlspecialchars($e->getMessage());
        }
    }
}
?>
<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Phiếu nhập kho</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-900 min-h-screen text-slate-100">
  <div class="max-w-5xl mx-auto p-6 space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold">Phiếu nhập kho</h1>
        <p class="text-slate-400 text-sm mt-1">Ghi nhận hàng nhập và chi tiết sản phẩm</p>
      </div>
      <div class="flex gap-2 text-sm">
        <a href="dashboard.php" class="px-3 py-2 rounded bg-slate-800 hover:bg-slate-700">← Dashboard</a>
        <a href="logout.php" class="px-3 py-2 rounded bg-red-600 hover:bg-red-700">Đăng xuất</a>
      </div>
    </div>

    <?php if ($errors): ?>
      <div class="bg-red-900/60 border border-red-700 text-red-200 px-4 py-3 rounded">
        <ul class="list-disc list-inside space-y-1">
          <?php foreach ($errors as $er): ?>
            <li><?= htmlspecialchars($er) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <?php if ($success): ?>
      <div class="bg-emerald-900/60 border border-emerald-700 text-emerald-100 px-4 py-3 rounded">
        <?= htmlspecialchars($success) ?>
      </div>
    <?php endif; ?>

    <form method="post" class="bg-slate-800 rounded-lg p-5 space-y-4">
      <div class="grid md:grid-cols-3 gap-4">
        <div>
          <label class="block text-sm text-slate-300 mb-2">Mã nhập hàng *</label>
          <input name="manhaphang" required class="w-full px-3 py-2 rounded bg-slate-900 border border-slate-700" value="<?= htmlspecialchars($_POST['manhaphang'] ?? '') ?>" />
        </div>
        <div>
          <label class="block text-sm text-slate-300 mb-2">Nhà cung cấp *</label>
          <select name="mancc" required class="w-full px-3 py-2 rounded bg-slate-900 border border-slate-700">
            <option value="">-- Chọn --</option>
            <?php foreach ($nhacungcaps as $ncc): ?>
              <option value="<?= htmlspecialchars($ncc['Mancc']) ?>" <?= (($_POST['mancc'] ?? '') === $ncc['Mancc']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($ncc['Tenncc']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="block text-sm text-slate-300 mb-2">Ngày nhập *</label>
          <input type="date" name="ngaynhap" required class="w-full px-3 py-2 rounded bg-slate-900 border border-slate-700" value="<?= htmlspecialchars($_POST['ngaynhap'] ?? date('Y-m-d')) ?>" />
        </div>
      </div>

      <div>
        <label class="block text-sm text-slate-300 mb-2">Ghi chú</label>
        <textarea name="ghichu" rows="3" class="w-full px-3 py-2 rounded bg-slate-900 border border-slate-700"><?= htmlspecialchars($_POST['ghichu'] ?? '') ?></textarea>
      </div>

      <div class="space-y-2">
        <div class="flex items-center justify-between">
          <div class="text-slate-200 font-semibold">Chi tiết sản phẩm</div>
          <button type="button" onclick="addRow()" class="px-3 py-1 rounded bg-sky-600 hover:bg-sky-700 text-sm font-semibold">+ Thêm dòng</button>
        </div>
        <div class="overflow-auto border border-slate-700 rounded">
          <table class="min-w-full text-sm">
            <thead class="bg-slate-900 text-slate-300">
              <tr>
                <th class="px-3 py-2 text-left">Sản phẩm</th>
                <th class="px-3 py-2 text-left">Số lượng</th>
                <th class="px-3 py-2 text-left">Đơn giá</th>
                <th class="px-3 py-2"></th>
              </tr>
            </thead>
            <tbody id="detail-rows">
              <?php
              $posted = isset($_POST['masp']) ? count($_POST['masp']) : 0;
              $rowCount = max($posted, 1);
              for ($i = 0; $i < $rowCount; $i++):
                  $maspVal = $_POST['masp'][$i] ?? '';
                  $slVal = $_POST['soluong'][$i] ?? '';
                  $dgVal = $_POST['dongia'][$i] ?? '';
              ?>
              <tr class="border-t border-slate-800">
                <td class="px-3 py-2">
                  <select name="masp[]" class="w-full px-3 py-2 rounded bg-slate-900 border border-slate-700">
                    <option value="">-- Chọn --</option>
                    <?php foreach ($sanphams as $sp): ?>
                      <option value="<?= htmlspecialchars($sp['Masp']) ?>" <?= ($maspVal === $sp['Masp']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($sp['Tensp']) ?> (<?= htmlspecialchars($sp['Dvt']) ?>)
                      </option>
                    <?php endforeach; ?>
                  </select>
                </td>
                <td class="px-3 py-2"><input name="soluong[]" type="number" min="1" class="w-full px-3 py-2 rounded bg-slate-900 border border-slate-700" value="<?= htmlspecialchars($slVal) ?>" /></td>
                <td class="px-3 py-2"><input name="dongia[]" type="number" min="0" step="0.01" class="w-full px-3 py-2 rounded bg-slate-900 border border-slate-700" value="<?= htmlspecialchars($dgVal) ?>" /></td>
                <td class="px-3 py-2 text-right"><button type="button" onclick="removeRow(this)" class="text-red-400 hover:text-red-200">Xóa</button></td>
              </tr>
              <?php endfor; ?>
            </tbody>
          </table>
        </div>
      </div>

      <div class="pt-2">
        <button type="submit" class="w-full md:w-auto inline-flex items-center justify-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-slate-900 font-semibold px-5 py-3 rounded">
          Lưu phiếu nhập
        </button>
      </div>
    </form>
  </div>

<script>
  const optionTemplate = <?php
    $options = '';
    foreach ($sanphams as $sp) {
      $label = htmlspecialchars($sp['Tensp'] . ' (' . $sp['Dvt'] . ')', ENT_QUOTES);
      $val = htmlspecialchars($sp['Masp'], ENT_QUOTES);
      $options .= "<option value=\\\"{$val}\\\">{$label}</option>";
    }
    echo json_encode($options);
  ?>;

  function addRow() {
    const tbody = document.getElementById('detail-rows');
    const tr = document.createElement('tr');
    tr.className = 'border-t border-slate-800';
    tr.innerHTML = `
      <td class="px-3 py-2">
        <select name="masp[]" class="w-full px-3 py-2 rounded bg-slate-900 border border-slate-700">
          <option value="">-- Chọn --</option>
          ${optionTemplate}
        </select>
      </td>
      <td class="px-3 py-2"><input name="soluong[]" type="number" min="1" class="w-full px-3 py-2 rounded bg-slate-900 border border-slate-700" /></td>
      <td class="px-3 py-2"><input name="dongia[]" type="number" min="0" step="0.01" class="w-full px-3 py-2 rounded bg-slate-900 border border-slate-700" /></td>
      <td class="px-3 py-2 text-right"><button type="button" onclick="removeRow(this)" class="text-red-400 hover:text-red-200">Xóa</button></td>
    `;
    tbody.appendChild(tr);
  }

  function removeRow(btn) {
    const tr = btn.closest('tr');
    const tbody = tr.parentElement;
    tbody.removeChild(tr);
    if (tbody.children.length === 0) {
      addRow();
    }
  }
</script>
</body>
</html>
