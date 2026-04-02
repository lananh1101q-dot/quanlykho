<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: dangnhap.php');
    exit;
}
require_once __DIR__ . '/db.php';

$errors = [];
$success = '';

// Lấy dữ liệu dropdown
$congtrinhs = $pdo->query("
    SELECT Mact, Tenct 
    FROM Congtrinh 
    ORDER BY Tenct
")->fetchAll();

$sanphams = $pdo->query("
    SELECT Masp, Tensp, Dvt 
    FROM Sanpham 
    ORDER BY Tensp
")->fetchAll();

$khos = $pdo->query("
    SELECT Makho, Tenkho 
    FROM Kho 
    ORDER BY Tenkho
")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $maxuat = trim($_POST['maxuathang'] ?? '');
    $mact = trim($_POST['mact'] ?? '');
    $makho = trim($_POST['makho'] ?? '');
    $ngayxuat = $_POST['ngayxuat'] ?? '';
    $ghichu = trim($_POST['ghichu'] ?? '');

    $maspArr = $_POST['masp'] ?? [];
    $soluongArr = $_POST['soluong'] ?? [];
    $dongiaArr = $_POST['dongia'] ?? [];

    // Kiểm tra dữ liệu chính
    if ($maxuat === '' || $mact === '' || $makho === '' || $ngayxuat === '') {
        $errors[] = 'Vui lòng nhập đầy đủ Mã xuất, Công trình, Kho, Ngày xuất.';
    }

    // Chuẩn hóa chi tiết sản phẩm
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
        $errors[] = 'Cần ít nhất một dòng chi tiết sản phẩm hợp lệ.';
    }

    if (!$errors) {
        try {
            $pdo->beginTransaction();

            // Tính tổng tiền xuất
            $tong = 0;
            foreach ($items as $it) {
                $tong += $it['soluong'] * $it['dongia'];
            }

            // Lưu phiếu xuất
            $stmtPhieu = $pdo->prepare("
                INSERT INTO Phieuxuat 
                (Maxuathang, Mact, Ngayxuat, Tongtienxuat, Ghichu)
                VALUES (:ma, :mact, :ngay, :tong, :ghichu)
            ");
            $stmtPhieu->execute([
                ':ma'    => $maxuat,
                ':mact'  => $mact,
                ':ngay'  => $ngayxuat,
                ':tong'  => $tong,
                ':ghichu'=> $ghichu,
            ]);

            // Lưu chi tiết phiếu xuất + trừ tồn kho
            $stmtCt = $pdo->prepare("
                INSERT INTO Chitiet_Phieuxuat 
                (Maxuathang, Masp, Soluong, Dongiaxuat)
                VALUES (:ma, :masp, :sl, :dg)
            ");

            $stmtTonkho = $pdo->prepare("
                UPDATE Tonkho
                SET Soluongton = Soluongton - :sl
                WHERE Makho = :makho AND Masp = :masp
            ");

            foreach ($items as $it) {
                // Chi tiết phiếu xuất
                $stmtCt->execute([
                    ':ma'   => $maxuat,
                    ':masp' => $it['masp'],
                    ':sl'   => $it['soluong'],
                    ':dg'   => $it['dongia'],
                ]);

                // Trừ tồn kho theo kho cụ thể
                $stmtTonkho->execute([
                    ':makho' => $makho,
                    ':masp'  => $it['masp'],
                    ':sl'    => $it['soluong'],
                ]);
            }

            $pdo->commit();
            $success = 'Tạo phiếu xuất thành công và đã cập nhật tồn kho.';
            
            // Reset form
            $maxuat = $mact = $makho = $ngayxuat = $ghichu = '';
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
  <title>Phiếu xuất kho</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
                <li class="nav-item"><a class="nav-link" href="Sanpham.php"><i class="fas fa-cube"></i> Sản phẩm</a></li>
                <li class="nav-item"><a class="nav-link" href="dmsp.php"><i class="fas fa-tags"></i> Danh mục sản phẩm</a></li>
                <li class="nav-item"><a class="nav-link" href="Nhacungcap.php"><i class="fas fa-truck"></i> Nhà cung cấp</a></li>
            </ul>
        </li>

        <li class="nav-item">
            <a class="nav-link" href="javascript:void(0)" id="btnPhieuNhap">
                <i class="fas fa-file-import"></i> Phiếu nhập kho
                <i class="fas fa-chevron-down float-end"></i>
            </a>
            <ul class="nav flex-column ms-3 d-none" id="submenuPhieuNhap">
                <li class="nav-item"><a class="nav-link" href="danh_sach_phieu_nhap.php"><i class="fas fa-list"></i> Danh sách phiếu nhập</a></li>
                <li class="nav-item"><a class="nav-link" href="phieu_nhap.php"><i class="fas fa-plus-circle"></i> Tạo phiếu nhập</a></li>
            </ul>
        </li>

        <li class="nav-item">
            <a class="nav-link" href="javascript:void(0)" id="btnPhieuXuat">
                <i class="fas fa-file-export"></i> Phiếu xuất
                <i class="fas fa-chevron-down float-end"></i>
            </a>
            <ul class="nav flex-column ms-3 d-none" id="submenuPhieuXuat">
                <li class="nav-item"><a class="nav-link" href="danh_sach_phieu_xuat.php"><i class="fas fa-list"></i> Danh sách phiếu xuất</a></li>
                <li class="nav-item"><a class="nav-link" href="phieu_xuat.php"><i class="fas fa-plus-circle"></i> Tạo phiếu xuất</a></li>
            </ul>
        </li>

        <li class="nav-item">
            <a class="nav-link" href="javascript:void(0)" id="btnBaoCao">
                <i class="fas fa-chart-bar"></i> Báo cáo & Thống kê
                <i class="fas fa-chevron-down float-end"></i>
            </a>
            <ul class="nav flex-column ms-3 d-none" id="submenuBaoCao">
                <li class="nav-item"><a class="nav-link" href="tonkho.php"><i class="fas fa-warehouse"></i> Báo cáo tồn kho</a></li>
                <li class="nav-item"><a class="nav-link" href="baocaotieuhao.php"><i class="fas fa-chart-line"></i> Báo cáo tiêu hao</a></li>
            </ul>
        </li>

        <li class="nav-item">
            <a class="nav-link" href="javascript:void(0)" id="btnCongtrinh">
                <i class="fas fa-building"></i> Quản lý công trình
                <i class="fas fa-chevron-down float-end"></i>
            </a>
            <ul class="nav flex-column ms-3 d-none" id="submenuCongtrinh">
                <li class="nav-item"><a class="nav-link" href="congtrinh.php"><i class="fas fa-list"></i> Danh sách công trình</a></li>
                <li class="nav-item"><a class="nav-link" href="them_congtrinh.php"><i class="fas fa-plus-circle"></i> Thêm công trình</a></li>
            </ul>
        </li>

        <li class="nav-item">
            <a class="nav-link text-danger" href="logout.php"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a>
        </li>
    </ul>
  </nav>

  <div class="main-content">
    <div class="card shadow">
      <div class="card-header bg-primary text-white">
        <h4 class="mb-0"><i class="fas fa-file-export me-2"></i>Tạo Phiếu Xuất Kho</h4>
        <p class="mb-0 small">Ghi nhận hàng xuất ra công trình</p>
      </div>
      <div class="card-body">
        <?php if ($errors): ?>
          <div class="alert alert-danger">
            <ul class="mb-0">
              <?php foreach ($errors as $er): ?>
                <li><?= htmlspecialchars($er) ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <?php if ($success): ?>
          <div class="alert alert-success">
            ✅ <?= htmlspecialchars($success) ?>
            <a href="danh_sach_phieu_xuat.php" class="btn btn-sm btn-primary mt-2">Xem danh sách</a>
          </div>
        <?php endif; ?>

        <form method="post">
          <div class="row mb-3">
            <div class="col-md-3">
              <label class="form-label">Mã xuất hàng *</label>
              <input type="text" name="maxuathang" required class="form-control" 
                     value="<?= htmlspecialchars($_POST['maxuathang'] ?? '') ?>" />
            </div>

            <div class="col-md-3">
              <label class="form-label">Công Trình *</label>
              <select name="mact" required class="form-select">
                <option value="">-- Chọn công trình --</option>
                <?php foreach ($congtrinhs as $ct): ?>
                  <option value="<?= htmlspecialchars($ct['Mact']) ?>"
                    <?= (($_POST['mact'] ?? '') === $ct['Mact']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($ct['Tenct']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="col-md-3">
              <label class="form-label">Kho Xuất *</label>
              <select name="makho" required class="form-select">
                <option value="">-- Chọn kho --</option>
                <?php foreach ($khos as $kho): ?>
                  <option value="<?= htmlspecialchars($kho['Makho']) ?>"
                    <?= (($_POST['makho'] ?? '') === $kho['Makho']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($kho['Tenkho']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="col-md-3">
              <label class="form-label">Ngày xuất *</label>
              <input type="date" name="ngayxuat" required class="form-control"
                value="<?= htmlspecialchars($_POST['ngayxuat'] ?? date('Y-m-d')) ?>" />
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label">Ghi chú</label>
            <textarea name="ghichu" rows="2" class="form-control"><?= htmlspecialchars($_POST['ghichu'] ?? '') ?></textarea>
          </div>

          <hr>
          <h5 class="mb-3">Chi tiết sản phẩm</h5>

          <div class="table-responsive mb-3">
            <table class="table table-sm table-bordered">
              <thead class="table-light">
                <tr>
                  <th>Sản phẩm</th>
                  <th style="width: 120px;">Số lượng</th>
                  <th style="width: 150px;">Đơn giá</th>
                  <th style="width: 180px;">Thành tiền</th>
                  <th style="width: 80px;">Thao tác</th>
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
                <tr>
                  <td>
                    <select name="masp[]" class="form-select form-select-sm">
                      <option value="">-- Chọn --</option>
                      <?php foreach ($sanphams as $sp): ?>
                        <option value="<?= htmlspecialchars($sp['Masp']) ?>" 
                                <?= ($maspVal === $sp['Masp']) ? 'selected' : '' ?>>
                          <?= htmlspecialchars($sp['Tensp']) ?> (<?= htmlspecialchars($sp['Dvt']) ?>)
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </td>
                  <td><input name="soluong[]" type="number" min="1" step="0.01" class="form-control form-control-sm" 
                             value="<?= htmlspecialchars($slVal) ?>" /></td>
                  <td><input name="dongia[]" type="number" min="0" step="0.01" class="form-control form-control-sm" 
                             value="<?= htmlspecialchars($dgVal) ?>" /></td>
                  <td>
                    <span class="badge bg-info">
                      <?= number_format(($slVal ?? 0) * ($dgVal ?? 0), 2) ?>
                    </span>
                  </td>
                  <td><button type="button" onclick="removeRow(this)" class="btn btn-sm btn-danger">
                      <i class="fas fa-trash"></i>
                    </button></td>
                </tr>
                <?php endfor; ?>
              </tbody>
            </table>
          </div>

          <button type="button" onclick="addRow()" class="btn btn-outline-primary btn-sm mb-3">
            <i class="fas fa-plus me-2"></i>Thêm dòng
          </button>

          <div class="d-flex gap-2">
            <button type="submit" class="btn btn-success">
              <i class="fas fa-save me-2"></i>Lưu phiếu xuất
            </button>
            <a href="danh_sach_phieu_xuat.php" class="btn btn-secondary">
              <i class="fas fa-arrow-left me-2"></i>Quay lại
            </a>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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
      tr.innerHTML = `
        <td>
          <select name="masp[]" class="form-select form-select-sm">
            <option value="">-- Chọn --</option>
            ${optionTemplate}
          </select>
        </td>
        <td><input name="soluong[]" type="number" min="1" step="0.01" class="form-control form-control-sm" /></td>
        <td><input name="dongia[]" type="number" min="0" step="0.01" class="form-control form-control-sm" /></td>
        <td>
          <span class="badge bg-info">0.00</span>
        </td>
        <td><button type="button" onclick="removeRow(this)" class="btn btn-sm btn-danger">
            <i class="fas fa-trash"></i>
          </button></td>
      `;
      tbody.appendChild(tr);
    }

    function removeRow(btn) {
      const tbody = btn.closest('tr').parentElement;
      btn.closest('tr').remove();
      if (tbody.children.length === 0) {
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

    document.getElementById("btnCongtrinh").addEventListener("click", function () {
      document.getElementById("submenuCongtrinh").classList.toggle("d-none");
    });
  </script>
</body>
</html>
