<?php
// Kết nối CSDL
$conn = mysqli_connect("localhost", "root", "", "quanlykho");

$ma = "";
$ten = "";

// --- Cải thiện: Xử lý tìm kiếm ---
$where = "WHERE 1=1";
if (isset($_GET['timkiem'])) {
    if (!empty($_GET['tkma'])) {
        $ma = mysqli_real_escape_string($conn, $_GET['tkma']);
        $where  .= " AND sp.Masp LIKE '%$ma%'";
    }
    if (!empty($_GET['tkten'])) {
        $ten = mysqli_real_escape_string($conn, $_GET['tkten']);
        $where .= " AND sp.Tensp LIKE '%$ten%'";
    }
}

$sql = "SELECT sp.Masp, sp.Tensp, dm.Tendm, sp.Dvt, sp.Giaban, tk.soluongton
FROM sanpham sp
LEFT JOIN tonkho tk ON sp.Masp = tk.Masp
LEFT JOIN danhmucsp dm ON sp.Madm = dm.Madm
$where
ORDER BY sp.Masp ASC
";

$list = mysqli_query($conn, $sql);
if (!$list) {
    die("Lỗi truy vấn: " . mysqli_error($conn));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SanPham</title>
    <link rel="stylesheet" href="sanpham.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="khung-chua-toan-trang">
        
        <div class="header-danh-sach">
            <h2 class="tieu-de-chinh">Sản Phẩm</h2>
            <div class="nhom-nut-chuc-nang">
                <button class="nut nut-nhap-excel"><i class="fas fa-file-import"></i> Nhập Excel</button>
                <a href="/api/Sanpham/export" class="nut nut-xuat-excel"><i class="fas fa-file-excel"></i> Xuất Excel</a>
                <a href="/quanlykho/taosanpham.php" class="nut nut-tao-moi"><i class="fas fa-plus"></i> Tạo sản phẩm</a>
            </div>
        </div>
        
        <form method="GET">
        <div class="chia2cot">
            <div class="cot">
                <i class="fas fa-search icon-tim-kiem"></i>
                <input type="text" name="tkma" placeholder="Tìm kiếm mã" class="input-tim-kiem" value="<?= htmlspecialchars($ma) ?>">
            </div>
            <div class="cot">
                <i class="fas fa-search icon-tim-kiem"></i>
                <input type="text" name="tkten" placeholder="Tìm kiếm tên" class="input-tim-kiem" value="<?= htmlspecialchars($ten) ?>">
            </div>
        <button type="submit" name="timkiem">Tìm kiếm</button>
        </div>
        </form>

        <div class="khung-bang-bao-quanh">
            <table class="bang-san-pham">
                <thead>
                    <tr>
                        
                        <th class="cot-sap-xep" data-sort="Masp">Mã SP</th>
                        <th class="cot-sap-xep" data-sort="Tensp">Tên sản phẩm</th>
                        <th class="cot-sap-xep" data-sort="Madm">Danh mục</th> 
                        <th class="cot-sap-xep" data-sort="Dvt">ĐVT</th>
                        <th class="cot-sap-xep" data-sort="Giaban">Giá bán</th>
                        <th class="cot-sap-xep" data-sort="Soluongton">Tồn kho</th>
                       
                        <th class="cot-hanh-dong-nut">Thao tác</th> 
                    </tr>
                </thead>
                <tbody>
                    
                    <?php while ($row = mysqli_fetch_assoc($list)) { ?>
                    <tr>
                       
                        <td><?= htmlspecialchars($row['Masp']) ?></td>
                        <td><?= htmlspecialchars($row['Tensp']) ?></td>
                        <td><?= htmlspecialchars($row['Tendm']) ?>
                        
                    
                    </td>
                        <td><?= htmlspecialchars($row['Dvt']) ?></td>
                        <td><?= number_format($row['Giaban'], 0, ',', '.') ?></td> 
                        
                        <td><?= htmlspecialchars($row['soluongton'] ?? 0) ?></td> 
                        
                       
                        
                        <td class="cot-hanh-dong-nut">
                            <a href="/quanlykho/chinhsuasanpham.php?id=<?= $row['Masp'] ?>" class="nut-hanh-dong nut-sua"><i class="fas fa-edit"></i></a>
                            <a href="/quanlykho/xoasanpham.php?id=<?= $row['Masp'] ?>" class="nut-hanh-dong nut-xoa" onclick="return confirm('Bạn có chắc chắn muốn xóa sản phẩm này?')"><i class="fas fa-trash-alt"></i></a>
                        </td>
                    </tr>
                    <?php } ?>
                    
                </tbody>
            </table>
        </div>

        <div class="footer-phan-trang">
            <div class="phan-trang-chi-tiet">
                <button disabled><i class="fas fa-chevron-left"></i></button>
                <span>Trang 1/5</span> <button><i class="fas fa-chevron-right"></i></button>
            </div>
        </div>
    </div>
</body>
</html>