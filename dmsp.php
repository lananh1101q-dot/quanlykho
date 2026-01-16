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
        /* =========================
   NỘI DUNG BÊN PHẢI
========================= */

.main-content {
    background-color: #f1f3f6;
    min-height: 100vh;
}

/* ===== HEADER ===== */
.header-danh-sach {
    background: white;
    padding: 18px 24px;
    border-radius: 12px;
    box-shadow: 0 6px 16px rgba(0,0,0,0.08);
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.tieu-de-chinh {
    font-size: 22px;
    font-weight: 600;
    color: #333;
}

/* ===== NÚT TẠO DANH MỤC ===== */
.nhom-nut-chuc-nang a {
    text-decoration: none;
    color: white;
}

.nut-nhap-excel {
    background: linear-gradient(135deg, #28a745, #218838);
    padding: 10px 16px;
    border-radius: 8px;
    border: none;
    font-weight: 500;
    transition: all 0.3s ease;
}

.nut-nhap-excel:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 12px rgba(40,167,69,0.4);
}

/* ===== FORM TÌM KIẾM ===== */
form {
    background: white;
    padding: 18px;
    border-radius: 12px;
    box-shadow: 0 6px 16px rgba(0,0,0,0.08);
    margin-bottom: 20px;
}

.chia2cot {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
    margin-bottom: 12px;
}

.input-tim-kiem {
    width: 100%;
    padding: 10px 14px;
    border-radius: 8px;
    border: 1px solid #ddd;
    transition: all 0.3s;
}

.input-tim-kiem:focus {
    outline: none;
    border-color: #007bff;
    box-shadow: 0 0 0 2px rgba(0,123,255,0.15);
}

form button[type="submit"] {
    background: #007bff;
    color: white;
    border: none;
    padding: 10px 18px;
    border-radius: 8px;
    font-weight: 500;
    transition: all 0.3s;
}

form button[type="submit"]:hover {
    background: #0056b3;
}

/* ===== BẢNG ===== */
.khung-bang-bao-quanh {
    background: white;
    border-radius: 12px;
    box-shadow: 0 6px 16px rgba(0,0,0,0.08);
    overflow: hidden;
}

.bang-san-pham {
    width: 100%;
    border-collapse: collapse;
}

.bang-san-pham thead {
    background: #007bff;
    color: white;
}

.bang-san-pham th,
.bang-san-pham td {
    padding: 14px 16px;
    text-align: left;
}

.bang-san-pham tbody tr {
    transition: background 0.2s ease;
}

.bang-san-pham tbody tr:nth-child(even) {
    background: #f8f9fa;
}

.bang-san-pham tbody tr:hover {
    background: #eef4ff;
}

/* ===== NÚT THAO TÁC ===== */
.cot-hanh-dong-nut-td {
    display: flex;
    gap: 10px;
}

.nut-hanh-dong {
    padding: 8px 10px;
    border-radius: 6px;
    color: white;
    transition: all 0.3s ease;
}

.nut-sua {
    background: #ffc107;
}

.nut-sua:hover {
    background: #e0a800;
}

.nut-xoa {
    background: #dc3545;
}

.nut-xoa:hover {
    background: #b02a37;
}

/* ===== PHÂN TRANG ===== */
.pagination-fixed {
    margin-top: 20px;
    display: flex;
    justify-content: center;
}

.pagination a {
    padding: 8px 14px;
    margin: 0 4px;
    background: white;
    border-radius: 6px;
    text-decoration: none;
    color: #007bff;
    box-shadow: 0 3px 8px rgba(0,0,0,0.08);
    transition: all 0.3s;
}

.pagination a:hover {
    background: #007bff;
    color: white;
}

.pagination a.active {
    background: #007bff;
    color: white;
    font-weight: 600;
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