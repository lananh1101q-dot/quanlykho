<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: dangnhap.php');
    exit;
}
require_once __DIR__ . '/db.php';

$manhap = $_GET['id'] ?? '';

if (empty($manhap)) {
    header('Location: danh_sach_phieu_nhap.php');
    exit;
}

// Lấy thông tin phiếu nhập
$phieuNhap = $pdo->prepare("
    SELECT pn.*, ncc.Tenncc, ncc.Sdtncc, ncc.Diachincc
    FROM Phieunhap pn
    LEFT JOIN Nhacungcap ncc ON pn.Mancc = ncc.Mancc
    WHERE pn.Manhaphang = ?
");
$phieuNhap->execute([$manhap]);
$phieuNhap = $phieuNhap->fetch();

if (!$phieuNhap) {
    header('Location: danh_sach_phieu_nhap.php?error=Phiếu nhập không tồn tại');
    exit;
}

// Lấy chi tiết phiếu nhập
$chiTiet = $pdo->prepare("
    SELECT ct.*, sp.Tensp, sp.Dvt
    FROM Chitiet_Phieunhap ct
    JOIN Sanpham sp ON ct.Masp = sp.Masp
    WHERE ct.Manhaphang = ?
    ORDER BY sp.Tensp
");
$chiTiet->execute([$manhap]);
$chiTiet = $chiTiet->fetchAll();
?>
<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Chi tiết phiếu nhập</title>
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
                <a class="nav-link" href="javascript:void(0)" id="btnBaoCao">
                    <i class="fas fa-chart-bar"></i> Báo cáo & Thống kê
                    <i class="fas fa-chevron-down float-end"></i>
                </a>

               <ul class="nav flex-column ms-3 d-none" id="submenuBaoCao">
                    <li class="nav-item">
                        <a class="nav-link" href="quanly_banhang.php">
                            <i class="fas fa-cash-register"></i> Quản lý bán hàng

                        </a>
                    </li>
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
        <h1 class="text-2xl font-bold">Chi tiết phiếu nhập</h1>
        <p class="text-slate-400 text-sm mt-1">Mã phiếu: <?= htmlspecialchars($phieuNhap['Manhaphang']) ?></p>
      </div>
      <div class="flex gap-2 text-sm">
        <a href="sua_phieu_nhap.php?id=<?= urlencode($phieuNhap['Manhaphang']) ?>" class="px-3 py-2 rounded bg-blue-600 hover:bg-blue-700 font-semibold">Sửa</a>
        <a href="danh_sach_phieu_nhap.php" class="px-3 py-2 rounded bg-slate-800 hover:bg-slate-700">← Danh sách</a>
        <a href="logout.php" class="px-3 py-2 rounded bg-red-600 hover:bg-red-700">Đăng xuất</a>
      </div>
    </div>

    <div class="bg-slate-800 rounded-lg p-5 space-y-4">
      <div class="grid md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm text-slate-400 mb-1">Mã phiếu nhập</label>
          <div class="text-lg font-semibold"><?= htmlspecialchars($phieuNhap['Manhaphang']) ?></div>
        </div>
        <div>
          <label class="block text-sm text-slate-400 mb-1">Ngày nhập</label>
          <div class="text-lg"><?= date('d/m/Y', strtotime($phieuNhap['Ngaynhaphang'])) ?></div>
        </div>
        <div>
          <label class="block text-sm text-slate-400 mb-1">Nhà cung cấp</label>
          <div class="text-lg"><?= htmlspecialchars($phieuNhap['Tenncc'] ?? 'N/A') ?></div>
          <?php if ($phieuNhap['Sdtncc']): ?>
            <div class="text-sm text-slate-400">ĐT: <?= htmlspecialchars($phieuNhap['Sdtncc']) ?></div>
          <?php endif; ?>
          <?php if ($phieuNhap['Diachincc']): ?>
            <div class="text-sm text-slate-400"><?= htmlspecialchars($phieuNhap['Diachincc']) ?></div>
          <?php endif; ?>
        </div>
        <div>
          <label class="block text-sm text-slate-400 mb-1">Tổng tiền</label>
          <div class="text-2xl font-bold text-emerald-400"><?= number_format($phieuNhap['Tongtiennhap'], 0, ',', '.') ?> đ</div>
        </div>
        <?php if ($phieuNhap['Ghichu']): ?>
          <div class="md:col-span-2">
            <label class="block text-sm text-slate-400 mb-1">Ghi chú</label>
            <div class="text-slate-300"><?= nl2br(htmlspecialchars($phieuNhap['Ghichu'])) ?></div>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <div class="bg-slate-800 rounded-lg border border-slate-700 overflow-auto">
      <div class="p-4 border-b border-slate-700">
        <h2 class="text-lg font-semibold">Chi tiết sản phẩm</h2>
      </div>
      <table class="min-w-full text-sm">
        <thead class="bg-slate-900 text-slate-300">
          <tr>
            <th class="px-4 py-3 text-left">STT</th>
            <th class="px-4 py-3 text-left">Mã SP</th>
            <th class="px-4 py-3 text-left">Tên sản phẩm</th>
            <th class="px-4 py-3 text-left">ĐVT</th>
            <th class="px-4 py-3 text-right">Số lượng</th>
            <th class="px-4 py-3 text-right">Đơn giá</th>
            <th class="px-4 py-3 text-right">Thành tiền</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($chiTiet)): ?>
            <tr><td colspan="7" class="px-4 py-4 text-center text-slate-400">Không có chi tiết.</td></tr>
          <?php else: ?>
            <?php $stt = 1; foreach ($chiTiet as $ct): ?>
              <tr class="border-t border-slate-800">
                <td class="px-4 py-2"><?= $stt++ ?></td>
                <td class="px-4 py-2 font-semibold"><?= htmlspecialchars($ct['Masp']) ?></td>
                <td class="px-4 py-2"><?= htmlspecialchars($ct['Tensp']) ?></td>
                <td class="px-4 py-2"><?= htmlspecialchars($ct['Dvt']) ?></td>
                <td class="px-4 py-2 text-right"><?= number_format($ct['Soluong']) ?></td>
                <td class="px-4 py-2 text-right"><?= number_format($ct['Dongianhap'], 0, ',', '.') ?> đ</td>
                <td class="px-4 py-2 text-right font-semibold"><?= number_format($ct['Thanhtien'], 0, ',', '.') ?> đ</td>
              </tr>
            <?php endforeach; ?>
            <tr class="border-t-2 border-slate-700 bg-slate-900">
              <td colspan="6" class="px-4 py-3 text-right font-semibold">Tổng cộng:</td>
              <td class="px-4 py-3 text-right font-bold text-lg text-emerald-400"><?= number_format($phieuNhap['Tongtiennhap'], 0, ',', '.') ?> đ</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
  <script>
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

</script>
</body>
</html>
