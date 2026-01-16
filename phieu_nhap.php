<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: dangnhap.php');
    exit;
}
require_once "db.php";

$errors = [];
$success = "";

// Lấy dữ liệu dropdown
$nhacungcaps = $pdo->query("SELECT Mancc, Tenncc FROM Nhacungcap ORDER BY Tenncc")->fetchAll();
$sanphams = $pdo->query("SELECT Masp, Tensp, Dvt FROM Sanpham ORDER BY Tensp")->fetchAll();
$khos = $pdo->query("SELECT Makho, Tenkho FROM Kho ORDER BY Tenkho")->fetchAll();

/* ====== LOAD DROPDOWN ====== */
$nhacungcaps = $pdo->query("SELECT Mancc, Tenncc FROM Nhacungcap")->fetchAll();
$sanphams = $pdo->query("SELECT Masp, Tensp, Dvt FROM Sanpham")->fetchAll();

/* ====== XÓA PHIẾU ====== */
if ($action === 'delete' && $id) {
    try {
        $pdo->beginTransaction();
        $pdo->prepare("DELETE FROM Chitiet_Phieunhap WHERE Manhaphang=?")->execute([$id]);
        $pdo->prepare("DELETE FROM Phieunhap WHERE Manhaphang=?")->execute([$id]);
        $pdo->commit();
        header("Location: phieunhap.php?msg=deleted");
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        $errors[] = "Không thể xóa phiếu nhập";
    }
}

/* ====== LOAD DỮ LIỆU SỬA ====== */
$data = [
    'Manhaphang'=>'',
    'Mancc'=>'',
    'Ngaynhaphang'=>date('Y-m-d'),
    'Ghichu'=>'',
    'items'=>[]
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
    for ($i=0; $i<count($masp); $i++) {
        if ($masp[$i] && $sl[$i]>0 && $dg[$i]>0) {
            $items[] = [$masp[$i], $sl[$i], $dg[$i]];
            $tong += $sl[$i] * $dg[$i];
        }
    }

    if (empty($items)) {
        $errors[] = "Phải có ít nhất 1 sản phẩm";
    }

    if (!$errors) {
        try {
            $pdo->beginTransaction();

            if ($action === 'edit') {
                $pdo->prepare("
                    UPDATE Phieunhap
                    SET Mancc=?, Ngaynhaphang=?, Tongtiennhap=?, Ghichu=?
                    WHERE Manhaphang=?
                ")->execute([$mancc,$ngay,$tong,$ghichu,$manhap]);

                $pdo->prepare("DELETE FROM Chitiet_Phieunhap WHERE Manhaphang=?")
                    ->execute([$manhap]);
            } else {
                $pdo->prepare("
                    INSERT INTO Phieunhap(Manhaphang, Mancc, Ngaynhaphang, Tongtiennhap, Ghichu)
                    VALUES (?,?,?,?,?)
                ")->execute([$manhap,$mancc,$ngay,$tong,$ghichu]);
            }

            $stmtPhieu = $pdo->prepare("INSERT INTO Phieunhap (Manhaphang, Mancc, Makho, Ngaynhaphang, Tongtiennhap, Ghichu) VALUES (:ma, :ncc, :makho, :ngay, :tong, :ghichu)");
            $stmtPhieu->execute([
                ':ma' => $manhap,
                ':ncc' => $mancc,
                ':makho' => $makho,
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
                
                // Cập nhật tồn kho: INSERT ... ON DUPLICATE KEY UPDATE
                $stmtTonkho = $pdo->prepare("
                    INSERT INTO Tonkho (Makho, Masp, Soluongton) 
                    VALUES (:makho, :masp, :sl)
                    ON DUPLICATE KEY UPDATE Soluongton = Soluongton + :sl_update
                ");
                $stmtTonkho->execute([
                    ':makho' => $makho,
                    ':masp' => $it['masp'],
                    ':sl' => $it['soluong'],
                    ':sl_update' => $it['soluong'],
                ]);
            }

            $pdo->commit();
            $success = 'Tạo phiếu nhập thành công và đã cập nhật tồn kho.';
        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = "Lỗi xử lý dữ liệu";
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
        /* ===== ÉP DARK (TAILWIND) -> LIGHT (NỀN TRẮNG) ===== */

/* Nền tổng */
body {
    background-color: #f8f9fa !important;
}

/* Nội dung chính */
.main-content {
    background-color: #f8f9fa !important;
    color: #212529 !important;
}

/* Card / form / box */
.bg-slate-800,
.bg-slate-900 {
    background-color: #ffffff !important;
}

/* Border */
.border-slate-700,
.border-slate-800 {
    border-color: #dee2e6 !important;
}

/* Text Tailwind */
.text-slate-200,
.text-slate-300,
.text-slate-400 {
    color: #495057 !important;
}

/* Tiêu đề */
h1, h2, h3, h4, h5 {
    color: #212529 !important;
}

/* Input / select / textarea */
input,
select,
textarea {
    background-color: #ffffff !important;
    color: #212529 !important;
}

/* Placeholder */
input::placeholder,
textarea::placeholder {
    color: #6c757d !important;
}

/* Table */
thead.bg-slate-900 {
    background-color: #f1f3f5 !important;
    color: #212529 !important;
}

tbody tr {
    color: #212529 !important;
}

tbody tr:hover {
    background-color: #f8f9fa !important;
}

/* Ghi chú */
td.text-slate-400,
p.text-slate-400 {
    color: #6c757d !important;
}

/* Nút dashboard (nền slate) */
.bg-slate-800.hover\:bg-slate-700:hover {
    background-color: #e9ecef !important;
    color: #212529 !important;
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
        <h1 class="text-2xl font-bold">Phiếu nhập kho</h1>
        <p class="text-slate-400 text-sm mt-1">Ghi nhận hàng nhập và chi tiết sản phẩm</p>
      </div>
      <div class="flex gap-2 text-sm">
        <a href="dashboard.php" class="px-3 py-2 rounded bg-slate-800 hover:bg-slate-700">← Dashboard</a>
        <a href="logout.php" class="px-3 py-2 rounded bg-red-600 hover:bg-red-700">Đăng xuất</a>
      </div>
    </div>

<h1 class="text-2xl font-bold mb-4">
<?= $action==='edit' ? 'SỬA PHIẾU NHẬP' : 'THÊM PHIẾU NHẬP' ?>
</h1>

<?php if($errors): ?>
<div class="bg-red-800 p-3 mb-4 rounded">
<?php foreach($errors as $e): ?>
<div><?= $e ?></div>
<?php endforeach; ?>
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
          <label class="block text-sm text-slate-300 mb-2">Kho nhập *</label>
          <select name="makho" required class="w-full px-3 py-2 rounded bg-slate-900 border border-slate-700">
            <option value="">-- Chọn kho --</option>
            <?php foreach ($khos as $kho): ?>
              <option value="<?= htmlspecialchars($kho['Makho']) ?>" <?= (($_POST['makho'] ?? '') === $kho['Makho']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($kho['Tenkho']) ?> [<?= htmlspecialchars($kho['Makho']) ?>]
              </option>
            <?php endforeach; ?>
          </select>
          <?php if (empty($khos)): ?>
            <p class="text-xs text-yellow-400 mt-1">Chưa có kho nào. Vui lòng tạo kho trước.</p>
          <?php endif; ?>
        </div>
      </div>
      <div class="grid md:grid-cols-1 gap-4">
        <div>
          <label class="block text-sm text-slate-300 mb-2">Ngày nhập *</label>
          <input type="date" name="ngaynhap" required class="w-full px-3 py-2 rounded bg-slate-900 border border-slate-700" value="<?= htmlspecialchars($_POST['ngaynhap'] ?? date('Y-m-d')) ?>" />
        </div>
      </div>

<form method="post" class="space-y-4">
<div class="grid grid-cols-3 gap-4">
    <input name="manhaphang" placeholder="Mã nhập"
        value="<?= htmlspecialchars($data['Manhaphang']) ?>"
        <?= $action==='edit'?'readonly':'' ?>
        class="p-2 bg-slate-800 rounded">

    <select name="mancc" class="p-2 bg-slate-800 rounded">
        <option value="">Nhà cung cấp</option>
        <?php foreach($nhacungcaps as $n): ?>
        <option value="<?= $n['Mancc'] ?>"
            <?= ($data['Mancc']==$n['Mancc'])?'selected':'' ?>>
            <?= $n['Tenncc'] ?>
        </option>
        <?php endforeach; ?>
    </select>

      <div class="pt-2">
        <button type="submit" class="w-full md:w-auto inline-flex items-center justify-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-slate-900 font-semibold px-5 py-3 rounded">
          Lưu phiếu nhập
        </button>
      </div>
    </form>
  </div>
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