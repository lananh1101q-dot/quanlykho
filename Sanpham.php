
    <?php
$conn = mysqli_connect("localhost", "root", "", "quanlykho");
require_once 'Classes/PHPExcel.php';  // đường dẫn tới thư viện
require_once 'Classes/PHPExcel/IOFactory.php';

// ===============================
// 3. HÀM INSERT DỮ LIỆU
// ===============================
function tao_ins($Masp, $Tensp, $Madm, $dvt, $giaban, $conn) {
    $sql = "INSERT INTO Sanpham
            VALUES ('$Masp', '$Tensp', $Madm, '$dvt', $giaban)";
    return mysqli_query($conn, $sql);
}

function kiemtra_masp($Masp, $conn) {
    $sql = "SELECT 1 FROM Sanpham WHERE Masp = '$Masp' LIMIT 1";
    $rs = mysqli_query($conn, $sql);
    return mysqli_num_rows($rs) > 0;
}


// ===============================
// 4. XỬ LÝ NHẬP EXCEL
// ===============================
if (isset($_POST['btnUpload'])) {

    $file = $_FILES['txtTenfile']['tmp_name'];

    // Đọc file Excel
    $objReader = PHPExcel_IOFactory::createReaderForFile($file);
    $objExcel  = $objReader->load($file);

    // Lấy sheet đầu tiên
    $sheet = $objExcel->getSheet(0);
    $sheetData = $sheet->toArray(null, true, true, true);

    // Duyệt dữ liệu (bỏ dòng tiêu đề)
    for ($i = 2; $i <= count($sheetData); $i++) {

        $Masp = $sheetData[$i]['A'];
        $Tensp = $sheetData[$i]['B'];
        $Madm = $sheetData[$i]['C'];
        $dvt   = $sheetData[$i]['D'];
      $giaban   = $sheetData[$i]['E'];

        // Không insert dòng trống
        if ($Masp != "") {

            // Nếu mã đã tồn tại → bỏ qua
            if (kiemtra_masp($Masp, $conn)) {
                continue; // nhảy sang dòng tiếp theo
            }

            // Nếu chưa tồn tại → insert
            if (!tao_ins($Masp, $Tensp, $Madm, $dvt, $giaban, $conn)) {
                echo "Lỗi insert dòng $i: " . mysqli_error($conn);
                exit;
            }
        }


    }

    echo "<script>alert('Nhập dữ liệu từ Excel thành công!'); window.location.href='Sanpham.php';</script>";
}
// ===============================
// 5. XỬ LÝ XUẤT EXCEL
if (isset($_GET['export'])) {

    require_once 'Classes/PHPExcel.php';

    // ===== CODE XUẤT EXCEL =====
    $objExcel = new PHPExcel();
    $objExcel->setActiveSheetIndex(0);
    $sheet = $objExcel->getActiveSheet()->setTitle('Danh sách sản phẩm');

    $rowCount = 1;

    // ===== TẠO TIÊU ĐỀ CỘT =====
    $sheet->setCellValue('A'.$rowCount, 'Mã');
    $sheet->setCellValue('B'.$rowCount, 'Tên');
    $sheet->setCellValue('C'.$rowCount, 'Tên danh mục');

    $sheet->setCellValue('D'.$rowCount, 'Đơn vị tính');
    $sheet->setCellValue('E'.$rowCount, 'Giá bán');



    // ===== ĐỊNH DẠNG CỘT =====
    $sheet->getColumnDimension('A')->setAutoSize(true);
    $sheet->getColumnDimension('B')->setAutoSize(true);
    $sheet->getColumnDimension('C')->setAutoSize(true);
    $sheet->getColumnDimension('D')->setAutoSize(true);
    $sheet->getColumnDimension('E')->setAutoSize(true);


    // ===== GÁN MÀU NỀN =====
    $sheet->getStyle('A1:E1')
          ->getFill()
          ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
          ->getStartColor()->setRGB('00FF00');

    // ===== CĂN GIỮA =====
    $sheet->getStyle('A1:E1')
          ->getAlignment()
          ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

    // ===== LẤY DỮ LIỆU TỪ FORM TÌM KIẾM =====
    $ma  = $_GET['tkma']  ?? '';
    $ten = $_GET['tkten'] ?? '';

    $where = " WHERE 1=1 ";
    if ($ma != '')  $where .= " AND t.Masp LIKE '%$ma%'";
    if ($ten != '') $where .= " AND t.Tensp LIKE '%$ten%'";

    $sql = "
        SELECT t.Masp, t.Tensp, dm.Tendm,  t.Dvt,t.Giaban 
        FROM Sanpham t
        LEFT JOIN Danhmucsp dm ON t.Madm = dm.Madm
        $where
        ORDER BY t.Masp ASC
    ";

    $data = mysqli_query($conn, $sql);

    // ===== ĐIỀN DỮ LIỆU =====
    while ($row = mysqli_fetch_assoc($data)) {
        $rowCount++;

        $sheet->setCellValue('A'.$rowCount, $row['Masp']);
        $sheet->setCellValue('B'.$rowCount, $row['Tensp']);
        $sheet->setCellValue('C'.$rowCount, $row['Tendm']);

        $sheet->setCellValue('E'.$rowCount, $row['Giaban']);
        $sheet->setCellValue('D'.$rowCount, $row['Dvt']);
       
    }

    // ===== KẺ BẢNG =====
    $styleArray = array(
        'borders' => array(
            'allborders' => array(
                'style' => PHPExcel_Style_Border::BORDER_THIN
            )
        )
    );
    $sheet->getStyle('A1:E'.$rowCount)->applyFromArray($styleArray);

    // ===== XUẤT FILE =====
    $filename = "ExportExcel.xlsx";

    // Xóa buffer tránh lỗi file hỏng
    if (ob_get_length()) {
        ob_end_clean();
    }

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="'.$filename.'"');

    $writer = PHPExcel_IOFactory::createWriter($objExcel, 'Excel2007');
    $writer->save('php://output');
    exit;
}
// ===============================
$ma = "";
$ten = "";
    // Lấy danh sách sản phẩm từ bảng 'tao'
$where = "WHERE 1=1";
if (isset($_GET['timkiem'])) {
    if (!empty($_GET['tkma'])) {
        $ma = mysqli_real_escape_string($conn, $_GET['tkma']);
        $where .= " AND Masp LIKE '%$ma%'";
    }
    if (!empty($_GET['tkten'])) {
        $ten = mysqli_real_escape_string($conn, $_GET['tkten']);
        $where .= " AND Tensp LIKE '%$ten%'";
    }
}
$limit = 10; // 10 sản phẩm / trang
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;
$sqlCount = "SELECT COUNT(*) as total FROM Sanpham t 
LEFT JOIN Danhmucsp dm ON t.Madm = dm.Madm $where";
$totalRow = mysqli_fetch_assoc(mysqli_query($conn, $sqlCount));
$totalPage = ceil($totalRow['total'] / $limit);

$sql = "SELECT t.Masp, t.Tensp, dm.Tendm, t.Dvt, t.Giaban 
        FROM Sanpham t
        LEFT JOIN Danhmucsp dm ON t.Madm = dm.Madm
        $where
        LIMIT $limit OFFSET $offset";
$list = mysqli_query($conn, $sql);



if (!$list) {
    die("Lỗi truy vấn: " . mysqli_error($conn));
}

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh Sách Sản Phẩm - Slick</title>
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
        
       
        
        
         /* 4. DESIGN BẢNG GIỐNG MẪU BẠN GỬI */
        .header-table { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .nhom-nut { display: flex; gap: 10px; }
        .nut { padding: 8px 15px; border-radius: 4px; text-decoration: none; font-size: 13px; font-weight: bold; border: none; cursor: pointer; }
        .nut-tao { background: #27ae60; color: white; }
        .nut-xuat { background: #eee; color: #333; }

        .thanh-tim-kiem { display: flex;gap: 20px;background: #f9f9f9; padding: 10px; border-radius: 4px; margin-bottom: 15px; align-items: center; border: 1px solid #ddd; }
        .thanh-tim-kiem input { border: none; background: transparent; outline: none; padding-left: 10px; width: 100%; }

        table { width: 100%; border-collapse: collapse; font-size: 14px; }
        table thead { background: #f8f9fa; border-bottom: 2px solid #dee2e6; }
        table th { padding: 12px; text-align: left; color: #495057; }
        table td { padding: 12px; border-bottom: 1px solid #eee; vertical-align: middle; }
        
        .chip { padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: bold; background: #e8f0fe; color: #1967d2; }
        .nut-hanh-dong { color: #888; margin: 0 5px; cursor: pointer; text-decoration: none; }
        .nut-hanh-dong:hover { color: #ff4d4d; }
        
        .pagination-fixed {
            position: fixed;
            bottom: 0;
            left: 250px; /* đúng bằng width sidebar */
            right: 0;
            background: #fff;
            padding: 10px 20px;
            border-top: 1px solid #ddd;
            z-index: 999;
        }

        .pagination{
    justify-content:center;
}

        .pagination a {
            padding: 6px 12px;
            border-radius: 4px;
            background: #f1f1f1;
            text-decoration: none;
            color: #333;
            font-size: 13px;
        }

        .pagination a.active {
            background: #007bff;
            color: #fff;
        }

        .pagination a:hover {
            background: #0056b3;
            color: #fff;
        }

        .header-danh-sach{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:20px;
}

.tieu-de-chinh{
    font-weight:700;
    color:#333;
}

.chia2cot{
    display:grid;
    grid-template-columns:1fr 1fr auto;
    gap:20px;
    background:#fff;
    padding:15px;
    border-radius:8px;
    box-shadow:0 4px 12px rgba(0,0,0,0.05);
    margin-bottom:15px;
}

.input-tim-kiem{
    padding:10px 12px;
    border:1px solid #ddd;
    border-radius:6px;
    width:100%;
}

.khung-bang-bao-quanh{
    background:#fff;
    border-radius:10px;
    box-shadow:0 6px 18px rgba(0,0,0,0.06);
    overflow:hidden;
}

.bang-san-pham{
    width:100%;
    border-collapse:collapse;
}

.bang-san-pham th{
    background:#f1f3f5;
    text-transform:uppercase;
    font-size:13px;
}

.bang-san-pham td,
.bang-san-pham th{
    padding:14px;
    border-bottom:1px solid #eee;
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
            <a class="nav-link" href="javascript:void(0)" id="btnBaoCao">
                <i class="fas fa-chart-bar"></i> Báo cáo & Thống kê
                <i class="fas fa-chevron-down float-end"></i>
            </a>
            <ul class="nav flex-column ms-3 d-none" id="submenuBaoCao">
                <li class="nav-item"><a class="nav-link" href="baocaotieuhao.php"><i class="fas fa-chart-line"></i> Báo cáo tiêu hao</a></li>
                <li class="nav-item"><a class="nav-link" href="tonkho.php"><i class="fas fa-chart-pie"></i> Báo cáo tồn kho</a></li>
            </ul>
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
                <i class="fas fa-file-export"></i> Phiếu xuất <!-- Đã sửa icon đúng -->
                <i class="fas fa-chevron-down float-end"></i>
            </a>
            <ul class="nav flex-column ms-3 d-none" id="submenuPhieuXuat">
                <li class="nav-item"><a class="nav-link" href="danh_sach_phieu_xuat.php"><i class="fas fa-list"></i> Danh sách phiếu xuất</a></li>
                <li class="nav-item"><a class="nav-link" href="phieu_xuat.php"><i class="fas fa-plus-circle"></i> Tạo phiếu xuất</a></li>
            </ul>
        </li>

        <li class="nav-item">
            <a class="nav-link" href="javascript:void(0)" id="btnCongTrinh">
                <i class="fas fa-briefcase"></i> Quản lý công trình
                <i class="fas fa-chevron-down float-end"></i>
            </a>
            <ul class="nav flex-column ms-3 d-none" id="submenuCongTrinh">
                <li class="nav-item"><a class="nav-link" href="congtrinh.php"><i class="fas fa-folder-open"></i> Công trình</a></li>
            </ul>
        </li>

        <li class="nav-item">
            <a class="nav-link text-danger" href="logout.php"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a>
        </li>
    </ul>
</nav>

<div class="main-content">

    <!-- HEADER -->
    <div class="header-danh-sach">
        <h2 class="tieu-de-chinh">Danh sách sản phẩm</h2>
    </div>

    <!-- TÌM KIẾM + NÚT -->
    <form method="GET">
        <div class="chia2cot">
            <input type="text" name="tkma" value="<?= htmlspecialchars($ma) ?>"
                   class="input-tim-kiem" placeholder="Tìm theo mã sản phẩm">

            <input type="text" name="tkten" value="<?= htmlspecialchars($ten) ?>"
                   class="input-tim-kiem" placeholder="Tìm theo tên sản phẩm">

            <div class="nhom-nut">
                <button class="btn btn-primary" name="timkiem">
                    <i class="fas fa-search"></i> Tìm
                </button>
            </div>
        </div>
    </form>

    <!-- NHÓM NÚT CHỨC NĂNG -->
    <div class="d-flex gap-2 mb-3">
        <form method="POST" enctype="multipart/form-data">
            <input type="file" name="txtTenfile" accept=".xls,.xlsx" required>
            <button type="submit" name="btnUpload" class="btn btn-secondary">
                <i class="fas fa-file-import"></i> Nhập Excel
            </button>
        </form>

        <a href="Sanpham.php?export=1&tkma=<?= urlencode($ma) ?>&tkten=<?= urlencode($ten) ?>"
           class="btn btn-secondary">
            <i class="fas fa-file-export"></i> Xuất Excel
        </a>

        <a href="taosanpham.php" class="btn btn-success">
            <i class="fas fa-plus"></i> Thêm sản phẩm
        </a>
    </div>

    <!-- BẢNG -->
    <div class="khung-bang-bao-quanh">
        <table class="bang-san-pham">
            <thead>
                <tr>
                    <th>Mã SP</th>
                    <th>Tên sản phẩm</th>
                    <th>Danh mục</th>
                    <th>Đơn vị</th>
                    <th>Giá bán</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
            <?php while($row = mysqli_fetch_assoc($list)): ?>
                <tr>
                    <td><?= $row['Masp'] ?></td>
                    <td><?= $row['Tensp'] ?></td>
                    <td><span class="chip"><?= $row['Tendm'] ?></span></td>
                    <td><?= $row['Dvt'] ?></td>
                    <td><?= number_format($row['Giaban']) ?></td>
                    <td>
                        <a class="nut-hanh-dong nut-sua"
                           href="suasp.php?Masp=<?= $row['Masp'] ?>">
                           <i class="fas fa-edit"></i>
                        </a>
                        <a class="nut-hanh-dong nut-xoa"
                           onclick="return confirm('Bạn có chắc muốn xóa?');"
                           href="xoasp.php?Masp=<?= $row['Masp'] ?>">
                           <i class="fas fa-trash"></i>
                        </a>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>

</div>


           <div class="pagination-fixed">
                <div class="pagination">
                    <?php for ($i = 1; $i <= $totalPage; $i++): ?>
                        <a class="<?= ($i == $page) ? 'active' : '' ?>"
                        href="?page=<?= $i ?>&tkma=<?= urlencode($ma) ?>&tkten=<?= urlencode($ten) ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>
                </div>
            </div>



        
    </div>
<script>
document.addEventListener("DOMContentLoaded", function () {

    // ===== TOGGLE MENU KHI CLICK =====
    document.getElementById("btnSanPham")?.addEventListener("click", function () {
        document.getElementById("submenuSanPham")?.classList.toggle("d-none");
    });

    document.getElementById("btnPhieuNhap")?.addEventListener("click", function () {
        document.getElementById("submenuPhieuNhap")?.classList.toggle("d-none");
    });

    document.getElementById("btnPhieuXuat")?.addEventListener("click", function () {
        document.getElementById("submenuPhieuXuat")?.classList.toggle("d-none");
    });

    document.getElementById("btnBaoCao")?.addEventListener("click", function () {
        document.getElementById("submenuBaoCao")?.classList.toggle("d-none");
    });

    document.getElementById("btnKhachHang")?.addEventListener("click", function () {
        document.getElementById("submenuKhachHang")?.classList.toggle("d-none");
    });

    // ===== TỰ ĐỘNG MỞ MENU QUẢN LÝ SẢN PHẨM KHI Ở TRANG CON =====
    const path = window.location.pathname;

    const sanPhamPages = [
        "Sanpham.php",
        "dmsp.php",
        "Nhacungcap.php",
        "taosanpham.php",
        "taodmsp.php",
        "suasp.php",
        "suadmsp.php",
        "taoncc.php",
        "suancc.php"
    ];

    sanPhamPages.forEach(page => {
        if (path.includes(page)) {
            document.getElementById("submenuSanPham")?.classList.remove("d-none");
        }
    });

});
</script>


</body>
</html>