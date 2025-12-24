<?php
// Kết nối CSDL
$conn = mysqli_connect("localhost", "root", "", "quanlykho");
$ma = "";
$ten = "";
$where = " WHERE 1=1 ";

if (isset($_GET['timkiem'])) {
    if (!empty($_GET['tkma'])) {
        $ma = $_GET['tkma'];
        $where .= " AND Madm  LIKE '%$ma%'";
    }
    if (!empty($_GET['tkten'])) {
        $ten = $_GET['tkten'];
        $where .= " AND Tendm LIKE '%$ten%'";
    }
}
// Phân trang
$limit = 10; // 10 sản phẩm / trang
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;
$sqlCount = "SELECT COUNT(*) as total FROM Danhmucsp $where";

$totalRow = mysqli_fetch_assoc(mysqli_query($conn, $sqlCount));
$totalPage = ceil($totalRow['total'] / $limit);
$sql = "SELECT * FROM Danhmucsp $where LIMIT $limit OFFSET $offset";
$list= mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh mục sản phẩm</title>
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
        /* --- 1. Header và Nút chức năng --- */
.header-danh-sach {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.tieu-de-chinh {
    font-size: 28px;
    font-weight: 700;
    color: #333;
}

.nhom-nut-chuc-nang .nut {
    padding: 10px 18px;
    margin-left: 8px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 600;
    color: white;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: background-color 0.2s;
}

.nut-nhap-excel {
    background-color: #4CAF50; /* Xanh lá */
}

.nut-xuat-excel {
    background-color: #3498db; /* Xanh dương */
}

.nut-tao-moi {
    background-color: #007bff; /* Xanh đậm */
}

.nut:hover {
    filter: brightness(1.1);
}

/* --- 2. Thanh tìm kiếm và Vùng Thao tác --- */
.thanh-tim-kiem-loc {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.khung-tim-kiem {
    display: flex;
    align-items: center;
    border: 1px solid #ddd;
    border-radius: 6px;
    padding: 8px 12px;
    width: 300px;
    background-color: #f9f9f9;
}
.chia2cot{
    display: flex; /* Sử dụng flexbox để tạo hai cột */
    gap: 30px;
    margin-bottom: 20px;
     align-items: center;
}
.cot{
    flex: 1; /* Mỗi cột chiếm một nửa không gian */
}

.input-tim-kiem {
    border: none;
    outline: none;
    flex-grow: 1;
    padding: 0 10px;
    background-color: transparent;
}

.icon-tim-kiem, .icon-loc {
    color: #999;
}

.vung-chon-thao-tac {
    display: flex;
    align-items: center;
    gap: 20px;
    margin-bottom: 15px;
    padding: 10px 0;
    border-bottom: 1px solid #eee;
}

.nut-thao-tac-chung {
    background-color: #f0f0f0;
    border: 1px solid #ccc;
    padding: 6px 12px;
    border-radius: 4px;
    font-size: 14px;
    cursor: pointer;
}

/* --- 3. Bảng Sản Phẩm --- */
.khung-bang-bao-quanh {
    overflow-x: auto; /* Đảm bảo cuộn nếu bảng quá rộng */
}

.bang-san-pham {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    text-align: left;
    font-size: 14px;
}

.bang-san-pham th, .bang-san-pham td {
    padding: 14px 15px;
    border-bottom: 1px solid #f0f0f0;
    vertical-align: middle;
}

.bang-san-pham thead th {
    background-color: #f8f9fa; 
    font-weight: 600;
    color: #666;
    cursor: pointer;
    text-transform: uppercase;
    font-size: 12px;
}

/* Định dạng từng cột */
.cot-checkbox { width: 30px; text-align: center; }
.cot-anh { width: 60px; }
.cot-thao-tac { width: 40px; text-align: center; }
.anh-sp {
    width: 40px;
    height: 40px;
    object-fit: cover;
    border-radius: 4px;
    border: 1px solid #eee;
}

/* Chip Danh mục */
.chip {
    padding: 5px 10px;
    border-radius: 15px;
    font-size: 12px;
    font-weight: 500;
    color: white;
    display: inline-block;
    min-width: 80px;
    text-align: center;
}

.chip-dien-tu {
    background-color: #3498db; 
}

.chip-gia-dung {
    background-color: #9b59b6; /* Màu tím */
}

/* Giá và Tồn kho */
.gia-ban, .ton-kho {
    font-weight: 600;
    color: #333;
}

/* Trạng thái Bán (Icon) */
.trang-thai {
    font-size: 18px;
    display: block;
    text-align: center;
}

.tt-active {
    color: #2ecc71; /* Xanh lá */
}

.tt-ban {
    color: #e74c3c; /* Đỏ */
}

/* Nút 3 chấm (Menu hành động) */
.nut-ba-cham {
    background: none;
    border: none;
    color: #999;
    padding: 5px;
    cursor: pointer;
    transition: color 0.2s;
}

.nut-ba-cham:hover {
    color: #333;
    background-color: #f0f0f0;
    border-radius: 50%;
}

/* --- 4. Footer và Phân trang --- */
.footer-phan-trang {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 15px;
    margin-top: 15px;
    border-top: 1px solid #eee;
    font-size: 14px;
    color: #666;
}

.phan-trang-chi-tiet button {
    background-color: #f0f0f0;
    border: 1px solid #ddd;
    padding: 8px 12px;
    margin: 0 5px;
    border-radius: 4px;
    cursor: pointer;
    color: #333;
}

.phan-trang-chi-tiet button:disabled {
    cursor: not-allowed;
    background-color: #f9f9f9;
    color: #ccc;
}
/* --- Sửa đổi phần Cột Thao tác (Thay thế Nút 3 chấm) --- */

/* Cột Thao tác (Header) */
.cot-hanh-dong-nut {
    width: 100px; /* Định nghĩa độ rộng rõ ràng hơn */
    text-align: center;
}

/* Cell dữ liệu (Body) */
.cot-hanh-dong-nut-td {
    text-align: center;
    white-space: nowrap; /* Ngăn cách nút bị xuống dòng */
}

/* Định dạng chung cho nút Sửa/Xóa */
.nut-hanh-dong {
    background: none;
    border: none;
    cursor: pointer;
    margin: 0 4px;
    padding: 6px;
    display: inline-block;
    border-radius: 4px;
    transition: background-color 0.2s;
}

.nut-hanh-dong:hover {
    background-color: #f0f0f0;
}

/* Màu sắc cho từng loại nút */
.nut-sua i {
    color: #3498db; /* Sửa màu xanh dương */
}

.nut-xoa i {
    color: #e74c3c; /* Xóa màu đỏ */
}
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

.pagination {
    display: flex;
    justify-content: flex-end;
    gap: 8px;
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
                        <a class="nav-link" href="baocao_banhang.php">
                            <i class="fas fa-cash-register"></i> Báo cáo bán hàng
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="tonkho.php">
                            <i class="fas fa-warehouse"></i> Báo cáo tồn kho
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="baocao_khachhang.php">
                            <i class="fas fa-users"></i> Báo cáo khách hàng
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
        
        <div class="header-danh-sach">
            <h2 class="tieu-de-chinh">Danh mục sản phẩm</h2>
            <div class="nhom-nut-chuc-nang">
                <button class="nut nut-nhap-excel"><a href="/quanlykho/taodmsp.php" ><i class="fas fa-plus"></i> Tạo danh mục</a>
            </div>
        </div>
        <form method="GET">


        <div class="chia2cot">
            <div >
                <i class="fas fa-search icon-tim-kiem"></i>
                <input type="text" name="tkma" value="<?= $ma ?>"placeholder="Tìm kiếm mã" class="input-tim-kiem">
                
            </div>
             <div >
                <i class="fas fa-search icon-tim-kiem"></i>
                <input type="text" name="tkten" value="<?= $ten ?>" placeholder="Tìm kiếm tên" class="input-tim-kiem">
                
            </div>
            
        </div>
             
        <button type="submit" name="timkiem">Tìm kiếm</button>
</form>

      
        <div class="khung-bang-bao-quanh">
            <table class="bang-san-pham"> 
                <thead>
                    <tr>
                        
                        
                        <th class="cot-sap-xep" data-sort="Madm">Mã DM</th>
                        <th class="cot-sap-xep" data-sort="Tendm">Tên danh mục</th> 
                        <th class="cot-sap-xep" data-sort="mota">Mô tả</th>
                        <th class="cot-hanh-dong-nut">Thao tác</th> 
                       
                    </tr>
                </thead>
                <tbody>
                
                       <?php while ($row = mysqli_fetch_assoc($list)) { ?>
             <tr>
                <td><?= $row['Madm'] ?></td>
                <td><?= $row['Tendm'] ?></td>
                <td><?= $row['mota'] ?></td>
                <td class="cot-hanh-dong-nut-td">
                    <a href="/quanlykho/suadmsp.php?Madm=<?= $row['Madm'] ?>" class="nut-hanh-dong nut-sua" title="Sửa"><i class="fas fa-edit"></i></a>
                    <a onclick="return confirm('Bạn có chắc muốn xóa danh mục này?');" 
                    href="/quanlykho/xoadmsp.php?Madm=<?= $row['Madm'] ?>" 
                    class="nut-hanh-dong nut-xoa" title="Xóa">
                    <i class="fas fa-trash-alt"></i>
                    </a>
                </td>
            </tr>
            <?php } ?>
             
           
                    
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