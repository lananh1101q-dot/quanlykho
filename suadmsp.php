<?php
$conn = mysqli_connect("localhost", "root", "", "quanlykho");
if (!$conn) die("Lỗi kết nối: " . mysqli_connect_error());

// ======================
// LẤY DỮ LIỆU CŨ
// ======================
$Madm = isset($_GET['Madm']) ? $_GET['Madm'] : '';

$sql = "SELECT * FROM danhmucsp WHERE Madm = '$Madm'";
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($result);

if (!$row) {
    die("Không tìm thấy danh mục!");
}

// ======================
// CẬP NHẬT DỮ LIỆU
// ======================
if (isset($_POST['btnLuuSua'])) {

    $Tendm  = $_POST['Tendm'];
    $Mota   = $_POST['mota'];


    $sql_update = "UPDATE danhmucsp 
                   SET Tendm='$Tendm', mota='$Mota'
                   WHERE Madm='$Madm'";

    if (mysqli_query($conn, $sql_update)) {
        header("Location: dmsp.php");
        exit;
    } else {
        echo "Lỗi cập nhật: " . mysqli_error($conn);
    }
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>tạo danh mục sản phẩm</title>
    <link rel="stylesheet" href="taodmsp.css">
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
        
        <div class="dau-trang-form">
            <div class="khung-tieu-de-chinh">
               
                <h1 class="tieu-de-lon">Sửa danh mục</h1>
            </div>
            <p class="duong-dan">Danh Mục Sản Phẩm &gt; Sửa</p>
        </div>

        <div class="noi-dung-chinh-form">
            <form action="suadmsp.php?Madm=<?php echo $row['Madm']; ?>" method="POST">

                
                <div class="khung-nhap-lieu thong-tin-co-ban">
                    <h3 class="tieu-de-khung">Thông tin danh mục</h3>

                    <div class="nhom-truong hai-cot">
                        <div class="truong-nhap">
                            <label for="Madm">Mã danh mục*</label>
                            <input type="text" id="Madm" name="Madm" placeholder="tạo tự động" required value="<?php echo $row['Madm'] ?>">
                        </div>
                        <div class="truong-nhap">
                            <label for="Tendm">Tên danh mục*</label>
                            <input type="text" id="Tendm" name="Tendm" placeholder="Ví dụ: Điện tử" required value="<?php echo $row['Tendm'] ?>">
                        </div>
                    </div>

                    <div class="truong-nhap mot-cot">
                        <label for="mota">Mô tả</label>
                         <textarea id="mota" name="mota" rows="4" placeholder="Nhập mô tả chi tiết cho danh mục..." required><?php echo $row['mota'] ?></textarea>
                    </div>
                    
                   

                </div>

                <div class="vung-nut-hanh-dong">
                    <button type="submit" name="btnLuuSua" class="btn btn-save">Lưu thay đổi</button>
       
                  
                    <a href="/quanlykho/dmsp.php" class="nut nut-phu">Quay lại</a>
                </div>
            </form>
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