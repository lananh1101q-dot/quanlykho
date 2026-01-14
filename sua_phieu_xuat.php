<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: dangnhap.php');
    exit;
}
require_once __DIR__ . '/db.php';

$errors = [];
$success = '';
$maxuat = $_GET['id'] ?? '';

if (empty($maxuat)) {
    header('Location: danh_sach_phieu_xuat.php');
    exit;
}

// Lấy dữ liệu dropdown cho PHIẾU XUẤT
$khachhangs = $pdo->query("
    SELECT Makh, Tenkh 
    FROM Khachhang 
    ORDER BY Tenkh
")->fetchAll();

$sanphams = $pdo->query("
    SELECT Masp, Tensp, Dvt, Giaban 
    FROM Sanpham 
    ORDER BY Tensp
")->fetchAll();

$khos = $pdo->query("
    SELECT Makho, Tenkho 
    FROM Kho 
    ORDER BY Tenkho
")->fetchAll();
$phieuXuat = $pdo->prepare("
    SELECT * 
    FROM Phieuxuat 
    WHERE Maxuathang = ?
");
$phieuXuat->execute([$maxuat]);
$phieuXuat = $phieuXuat->fetch();

if (!$phieuXuat) {
    header('Location: danh_sach_phieu_xuat.php?error=Phiếu xuất không tồn tại');
    exit;
}
$chiTiet = $pdo->prepare("
    SELECT ct.*, sp.Tensp, sp.Dvt
    FROM Chitiet_Phieuxuat ct
    JOIN Sanpham sp ON ct.Masp = sp.Masp
    WHERE ct.Maxuathang = ?
");
$chiTiet->execute([$maxuat]);
$chiTietPhieu = $chiTiet->fetchAll();




if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ====== THÔNG TIN PHIẾU XUẤT ======
    $maxuat = trim($_POST['maxuathang'] ?? '');
    $makh = trim($_POST['makh'] ?? '');
    $ngayxuat = $_POST['ngayxuat'] ?? '';
    $ghichu = trim($_POST['ghichu'] ?? '');

    // ====== CHI TIẾT ======
    $maspArr = $_POST['masp'] ?? [];
    $soluongArr = $_POST['soluong'] ?? [];
    $dongiaArr = $_POST['dongia'] ?? [];

    // ====== KIỂM TRA BẮT BUỘC ======
    if ($maxuat === '' || $makh === '' || $ngayxuat === '') {
        $errors[] = 'Vui lòng nhập đầy đủ Mã xuất, Khách hàng, Ngày xuất.';
    }

    // ====== CHUẨN HÓA CHI TIẾT ======
    $items = [];
    for ($i = 0; $i < count($maspArr); $i++) {
        $masp = trim($maspArr[$i] ?? '');
        $soluong = (int)($soluongArr[$i] ?? 0);
        $dongia = (float)($dongiaArr[$i] ?? 0);

        if ($masp === '' || $soluong <= 0 || $dongia <= 0) {
            continue;
        }

        $items[] = [
            'masp'    => $masp,
            'soluong' => $soluong,
            'dongia'  => $dongia,
        ];
    }

    if (empty($items)) {
        $errors[] = 'Cần ít nhất một dòng sản phẩm hợp lệ.';
    }

    // === PHẦN XỬ LÝ DB (BEGIN TRANSACTION) VIẾT TIẾP Ở DƯỚI ===


    if (!$errors) {
    try {
        $pdo->beginTransaction();

        /* ===== 1. HOÀN TRẢ TỒN KHO CŨ ===== */
        foreach ($chiTietPhieu as $ctCu) {
            $stmtHoan = $pdo->prepare("
                UPDATE Tonkho
                SET Soluongton = Soluongton + :sl
                WHERE Masp = :masp
            ");
            $stmtHoan->execute([
                ':masp' => $ctCu['Masp'],
                ':sl'   => $ctCu['Soluong'],
            ]);
        }

        /* ===== 2. XÓA CHI TIẾT PHIẾU XUẤT CŨ ===== */
        $pdo->prepare("
            DELETE FROM Chitiet_Phieuxuat 
            WHERE Maxuathang = ?
        ")->execute([$maxuat]);

        /* ===== 3. TÍNH TỔNG TIỀN MỚI ===== */
        $tong = 0;
        foreach ($items as $it) {
            $tong += $it['soluong'] * $it['dongia'];
        }

        /* ===== 4. CẬP NHẬT PHIẾU XUẤT ===== */
        $stmtPhieu = $pdo->prepare("
            UPDATE Phieuxuat
            SET Maxuathang = :manew,
                Makh = :makh,
                Ngayxuat = :ngay,
                Tongtienxuat = :tong,
                Ghichu = :ghichu
            WHERE Maxuathang = :macu
        ");
        $stmtPhieu->execute([
            ':manew' => $maxuat,
            ':makh'  => $makh,
            ':ngay'  => $ngayxuat,
            ':tong'  => $tong,
            ':ghichu'=> $ghichu,
            ':macu'  => $maxuat,
        ]);

        /* ===== 5. THÊM CHI TIẾT MỚI + TRỪ TỒN ===== */
        $stmtCt = $pdo->prepare("
            INSERT INTO Chitiet_Phieuxuat
                (Maxuathang, Masp, Soluong, Dongiaxuat)
            VALUES
                (:ma, :masp, :sl, :dg)
        ");

        $stmtTru = $pdo->prepare("
            UPDATE Tonkho
            SET Soluongton = Soluongton - :sl
            WHERE Masp = :masp AND Soluongton >= :sl
        ");

        foreach ($items as $it) {
            // thêm chi tiết
            $stmtCt->execute([
                ':ma'   => $maxuat,
                ':masp' => $it['masp'],
                ':sl'   => $it['soluong'],
                ':dg'   => $it['dongia'],
            ]);

            // trừ tồn kho
            $stmtTru->execute([
                ':masp' => $it['masp'],
                ':sl'   => $it['soluong'],
            ]);

            if ($stmtTru->rowCount() === 0) {
                throw new Exception("Không đủ tồn kho cho sản phẩm {$it['masp']}");
            }
        }

        $pdo->commit();
        header("Location: danh_sach_phieu_xuat.php?success=sua");
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        $errors[] = 'Lỗi khi cập nhật phiếu xuất: ' . $e->getMessage();
    }
}

} else {
    // Hiển thị dữ liệu hiện tại của PHIẾU XUẤT
    $_POST['maxuathang'] = $phieuXuat['Maxuathang'];
    $_POST['makh']       = $phieuXuat['Makh'];
    $_POST['ngayxuat']   = $phieuXuat['Ngayxuat'];
    $_POST['ghichu']     = $phieuXuat['Ghichu'];

    $_POST['masp']    = array_column($chiTietPhieu, 'Masp');
    $_POST['soluong'] = array_column($chiTietPhieu, 'Soluong');
    $_POST['dongia']  = array_column($chiTietPhieu, 'Dongiaxuat');
}

?>
<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Sửa phiếu xuất kho</title>
  <script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
         /* tránh ghi đè */
        .d-none {
            display: none !important;
        }
        #submenuSanPham {
            transition: all 0.3s ease;
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
                        <a class="nav-link" href="Sanpham.php">
                            <i class="fas fa-cube"></i> Sản phẩm
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="dmsp.php">
                            <i class="fas fa-tags"></i> Danh mục sản phẩm
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="Nhacungcap.php">
                            <i class="fas fa-truck"></i> Nhà cung cấp
                        </a>
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
                      <a class="nav-link" href="danh_sach_phieu_nhap.php">
                          <i class="fas fa-list"></i> Danh sách phiếu nhập
                      </a>
                  </li>
                  <li class="nav-item">
                      <a class="nav-link" href="phieu_nhap.php">
                          <i class="fas fa-plus-circle"></i> Tạo phiếu nhập
                      </a>
                  </li>
              </ul>
          </li>
          <li class="nav-item">
              <a class="nav-link" href="javascript:void(0)" id="btnPhieuXuat">
                  <i class="fas fa-file-import"></i> Phiếu xuất
                  <i class="fas fa-chevron-down float-end"></i>
              </a>

              <ul class="nav flex-column ms-3 d-none" id="submenuPhieuXuat">
                  <li class="nav-item">
                      <a class="nav-link" href="danh_sach_phieu_xuat.php">
                          <i class="fas fa-list"></i> Danh sách phiếu xuất
                      </a>
                  </li>
                  <li class="nav-item">
                      <a class="nav-link" href="phieu_xuat.php">
                          <i class="fas fa-plus-circle"></i> Tạo phiếu xuất
                      </a>
                  </li>
              </ul>
          </li>
            <li class="nav-item">
                <a class="nav-link" href="javascript:void(0)" id="btnBaoCao">
                    <i class="fas fa-chart-bar"></i> Báo cáo & Thống kê
                    <i class="fas fa-chevron-down float-end"></i>
                </a>

            
                    <li class="nav-item">
                        <a class="nav-link" href="tonkho.php">
                            <i class="fas fa-warehouse"></i> Báo cáo tồn kho
                        </a>
                    </li>
                  
                </ul>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="khachhang.php"><i class="fas fa-users"></i> Khách hàng</a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-danger" href="logout.php"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a>
            </li>
        </ul>
    </nav>
    

    <div class="main-content">
  <div class="max-w-5xl mx-auto p-6 space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold">Sửa phiếu xuất kho</h1>
        <p class="text-slate-400 text-sm mt-1">Cập nhật thông tin phiếu xuất</p>
      </div>
      <div class="flex gap-2 text-sm">
   <a href="danh_sach_phieu_xuat.php" class="px-3 py-2 rounded bg-white-800 hover:bg-white-700">← Danh sách</a>
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

    <form method="post" class="bg-white-800 rounded-lg p-5 space-y-4">
      <div class="grid md:grid-cols-3 gap-4">
        <div>
          <label class="block text-sm text-black-300 mb-2">Mã xuất hàng *</label>
          <input name="maxuathang" required class="w-full px-3 py-2 rounded bg-white-900 border border-white-700" value="<?= htmlspecialchars($_POST['maxuathang'] ?? '') ?>" />
        </div>
        <div>
          <label class="block text-sm text-black-300 mb-2">Khách hàng *</label>
          <select name="makh" required class="w-full px-3 py-2 rounded bg-white-900 border border-white-700">
            <option value="">-- Chọn --</option>
            <?php foreach ($khachhangs as $kh): ?>
              <option value="<?= htmlspecialchars($kh['Makh']) ?>" <?= (($_POST['makh'] ?? '') === $kh['Makh']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($kh['Tenkh']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        
      </div>
      <div class="grid md:grid-cols-1 gap-4">
        <div>
          <label class="block text-sm text-black-300 mb-2">Ngày xuất *</label>
          <input type="date" name="ngayxuat" required class="w-full px-3 py-2 rounded bg-white-900 border border-white-700" value="<?= htmlspecialchars($_POST['ngayxuat'] ?? date('Y-m-d')) ?>" />
        </div>
      </div>

      <div>
        <label class="block text-sm text-black-300 mb-2">Ghi chú</label>
        <textarea name="ghichu" rows="3" class="w-full px-3 py-2 rounded bg-white-900 border border-white-700"><?= htmlspecialchars($_POST['ghichu'] ?? '') ?></textarea>
      </div>

      <div class="space-y-2">
        <div class="flex items-center justify-between">
          <div class="text-black-200 font-semibold">Chi tiết sản phẩm</div>
          <button type="button" onclick="addRow()" class="px-3 py-1 rounded bg-sky-600 hover:bg-sky-700 text-sm font-semibold">+ Thêm dòng</button>
        </div>
        <div class="overflow-auto border border-white-700 rounded">
          <table class="min-w-full text-sm">
            <thead class="bg-white-900 text-white-300">
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
              <tr class="border-t border-white-800">
                <td class="px-3 py-2">
                  <select name="masp[]" class="w-full px-3 py-2 rounded bg-white-900 border border-white-700">
                    <option value="">-- Chọn --</option>
                    <?php foreach ($sanphams as $sp): ?>
                      <option value="<?= htmlspecialchars($sp['Masp']) ?>" <?= ($maspVal === $sp['Masp']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($sp['Tensp']) ?> (<?= htmlspecialchars($sp['Dvt']) ?>)
                      </option>
                    <?php endforeach; ?>
                  </select>
                </td>
                <td class="px-3 py-2"><input name="soluong[]" type="number" min="1" class="w-full px-3 py-2 rounded bg-white-900 border border-white-700" value="<?= htmlspecialchars($slVal) ?>" /></td>
                <td class="px-3 py-2"><input name="dongia[]" type="number" min="0" step="0.01" class="w-full px-3 py-2 rounded bg-white-900 border border-white-700" value="<?= htmlspecialchars($dgVal) ?>" /></td>
                <td class="px-3 py-2 text-right"><button type="button" onclick="removeRow(this)" class="text-red-400 hover:text-red-200">Xóa</button></td>
              </tr>
              <?php endfor; ?>
            </tbody>
          </table>
        </div>
      </div>

      <div class="pt-2">
        <button type="submit" class="w-full md:w-auto inline-flex items-center justify-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-slate-900 font-semibold px-5 py-3 rounded">
          Cập nhật phiếu xuất
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
    tr.className = 'border-t border-white-800';
    tr.innerHTML = `
      <td class="px-3 py-2">
        <select name="masp[]" class="w-full px-3 py-2 rounded bg-white-900 border border-slate-700">
          <option value="">-- Chọn --</option>
          ${optionTemplate}
        </select>
      </td>
      <td class="px-3 py-2"><input name="soluong[]" type="number" min="1" class="w-full px-3 py-2 rounded bg-white-900 border border-slate-700" /></td>
      <td class="px-3 py-2"><input name="dongia[]" type="number" min="0" step="0.01" class="w-full px-3 py-2 rounded bg-white-900 border border-slate-700" /></td>
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
 
document.getElementById("btnSanPham").addEventListener("click", function () {
    const menu = document.getElementById("submenuSanPham");
    menu.classList.toggle("d-none");
    
});
document.getElementById("btnBaoCao").addEventListener("click", function () {
    document.getElementById("submenuBaoCao").classList.toggle("d-none");
});
const btnPhieuNhap = document.getElementById("btnPhieuNhap");
const submenuPhieuNhap = document.getElementById("submenuPhieuNhap");

if (btnPhieuNhap) {
    btnPhieuNhap.addEventListener("click", function () {
        submenuPhieuNhap.classList.toggle("d-none");
    });
}
const btnPhieuXuat = document.getElementById("btnPhieuXuat");
const submenuPhieuXuat = document.getElementById("submenuPhieuXuat");

if (btnPhieuXuat) {
    btnPhieuXuat.addEventListener("click", function () {
        submenuPhieuXuat.classList.toggle("d-none");
    });
}

</script>
</body>
</html>
