<?php
// Kết nối CSDL
$conn = mysqli_connect("localhost", "root", "", "quanlykho");
$ma = "";
$ten = "";
$where = " WHERE 1=1 ";

if (isset($_GET['timkiem'])) {
    if (!empty($_GET['tkma'])) {
        $ma = $_GET['tkma'];
        $where .= " AND MaNCC LIKE '%$ma%'";
    }
    if (!empty($_GET['tkten'])) {
        $ten = $_GET['tkten'];
        $where .= " AND TenNCC LIKE '%$ten%'";
    }
}
// Phân trang
$limit = 6; // 10 sản phẩm / trang
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;
$sqlCount = "SELECT COUNT(*) as total FROM Nhacungcap $where";

$totalRow = mysqli_fetch_assoc(mysqli_query($conn, $sqlCount));
$totalPage = ceil($totalRow['total'] / $limit);
$sql = "SELECT Mancc,Tenncc,Sdtncc,Diachincc FROM Nhacungcap $where LIMIT $limit OFFSET $offset";
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
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
/* ===================================================
   1. CÀI ĐẶT CHUNG
=================================================== */
body{
    background:#f4f6f9;
    font-family: "Segoe UI", sans-serif;
}

/* ===================================================
   2. SIDEBAR TRÁI
=================================================== */
.sidebar{
    position: fixed;
    top:0;
    left:0;
    width:250px;
    height:100vh;
    background: linear-gradient(180deg,#0d6efd,#084298);
    color:#fff;
    padding-top:20px;
    overflow-y:auto;
}

.sidebar h4{
    font-weight:600;
}

.sidebar .nav-link{
    color:#fff !important;
    padding:12px 20px;
    border-radius:6px;
    margin:4px 12px;
    transition:all .3s;
}

.sidebar .nav-link:hover{
    background:rgba(255,255,255,0.15);
    transform:translateX(8px);
    font-weight:600;
}

/* ===================================================
   3. NỘI DUNG CHÍNH
=================================================== */
.main-content{
    margin-left:250px;
    padding:25px;
}

/* ===================================================
   4. TIÊU ĐỀ TRANG
=================================================== */
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

/* ===================================================
   5. KHU VỰC TÌM KIẾM
=================================================== */
.chia2cot{
    display:grid;
    grid-template-columns: 1fr 1fr auto;
    gap:20px;
    background:#fff;
    padding:15px;
    border-radius:8px;
    box-shadow:0 4px 12px rgba(0,0,0,0.05);
    margin-bottom:15px;
}

.input-tim-kiem{
    width:100%;
    padding:10px 12px;
    border:1px solid #ddd;
    border-radius:6px;
    outline:none;
}

.input-tim-kiem:focus{
    border-color:#0d6efd;
}

/* ===================================================
   6. NÚT BẤM
=================================================== */
.nhom-nut{
    display:flex;
    align-items:center;
}

.nut{
    padding:10px 16px;
    border-radius:6px;
    text-decoration:none;
    font-size:14px;
    font-weight:600;
}

.nut-tao{
    background:#198754;
    color:#fff;
}

.nut-tao:hover{
    background:#157347;
}

/* ===================================================
   7. BẢNG DỮ LIỆU
=================================================== */
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

.bang-san-pham thead{
    background:#f1f3f5;
}

.bang-san-pham th{
    padding:14px;
    font-size:13px;
    text-transform:uppercase;
    color:#555;
}

.bang-san-pham td{
    padding:14px;
    border-bottom:1px solid #eee;
}

.bang-san-pham tbody tr:hover{
    background:#f8f9fa;
}

/* ===================================================
   8. NÚT THAO TÁC
=================================================== */
.nut-hanh-dong{
    padding:6px 10px;
    border-radius:6px;
    margin:0 3px;
}

.nut-sua{
    color:#0d6efd;
}

.nut-xoa{
    color:#dc3545;
}

/* ===================================================
   9. PHÂN TRANG CỐ ĐỊNH CUỐI
=================================================== */
.pagination-fixed{
    position:fixed;
    bottom:0;
    left:250px;
    right:0;
    background:#fff;
    padding:10px;
    border-top:1px solid #ddd;
}

.pagination{
    justify-content:center;
}

.pagination a{
    padding:6px 12px;
    border-radius:6px;
    background:#e9ecef;
    text-decoration:none;
    color:#333;
}

.pagination a.active{
    background:#0d6efd;
    color:#fff;
}

/* ===================================================
   10. RESPONSIVE
=================================================== */
@media(max-width:768px){
    .sidebar{
        position:relative;
        width:100%;
        height:auto;
    }
    .main-content{
        margin-left:0;
    }
    .pagination-fixed{
        left:0;
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
                            <i class="fas fa-cash-register"></i> Báo cáo bán hàng
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
        <h2 class="tieu-de-chinh">Danh sách nhà cung cấp</h2>
    </div>

    <form method="GET">
        <div class="chia2cot">
            <input type="text" name="tkma" value="<?= $ma ?>" class="input-tim-kiem" placeholder="Tìm theo mã NCC">
            <input type="text" name="tkten" value="<?= $ten ?>" class="input-tim-kiem" placeholder="Tìm theo tên NCC">
            <div class="nhom-nut">
                <a href="taoncc.php" class="nut nut-tao">
                    <i class="fas fa-plus"></i> Thêm NCC
                </a>
            </div>
        </div>
        <button class="btn btn-primary mb-3" name="timkiem">Tìm kiếm</button>
    </form>

    <div class="khung-bang-bao-quanh">
        <table class="bang-san-pham">
            <thead>
                <tr>
                    <th>Mã NCC</th>
                    <th>Tên NCC</th>
                    <th>SĐT</th>
                    <th>Địa chỉ</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
            <?php while($row = mysqli_fetch_assoc($list)) { ?>
                <tr>
                    <td><?= $row['Mancc'] ?></td>
                    <td><?= $row['Tenncc'] ?></td>
                    <td><?= $row['Sdtncc'] ?></td>
                    <td><?= $row['Diachincc'] ?></td>
                    <td>
                        <a class="nut-hanh-dong nut-sua" href="suancc.php?Mancc=<?= $row['Mancc'] ?>">
                            <i class="fas fa-edit"></i>
                        </a>
                        <a class="nut-hanh-dong nut-xoa"
                           onclick="return confirm('Bạn có chắc muốn xóa?');"
                           href="xoancc.php?Mancc=<?= $row['Mancc'] ?>">
                            <i class="fas fa-trash"></i>
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