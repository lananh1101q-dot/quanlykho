<?php
// ===============================
// KẾT NỐI DATABASE
// ===============================
$conn = mysqli_connect("localhost", "root", "", "quanlykho");
mysqli_set_charset($conn, "utf8");

if (!$conn) {
    die("Lỗi kết nối CSDL: " . mysqli_connect_error());
}

// ===============================
// LẤY DANH MỤC
// ===============================
$sql_danhmuc = "SELECT Madm, Tendm FROM danhmucsp ORDER BY Tendm ASC";
$result_danhmuc = mysqli_query($conn, $sql_danhmuc);

// ===============================
// XỬ LÝ LƯU (BACKEND)
// ===============================
if (isset($_POST['btnluu'])) {

    $Masp   = trim($_POST['Masp']);
    $Tensp  = trim($_POST['Tensp']);
    $Madm   = $_POST['Madm'];
    $Dvt    = trim($_POST['Dvt']);
    $Giaban = str_replace(',', '', $_POST['Giaban']);

    // --- Validate backend ---
    if ($Masp == "" || $Tensp == "" || $Madm == "" || $Dvt == "") {
        echo "<script>alert('Vui lòng nhập đầy đủ thông tin!');</script>";
    } elseif (!is_numeric($Giaban)) {
        echo "<script>alert('Giá bán phải là số!');</script>";
    } else {

        // --- Check trùng mã ---
        $check = mysqli_query($conn, "SELECT 1 FROM sanpham WHERE Masp='$Masp'");
        if (mysqli_num_rows($check) > 0) {
            echo "<script>alert('Mã sản phẩm đã tồn tại!');</script>";
        } else {

            // --- Insert ---
            $sql = "INSERT INTO sanpham (Masp, Tensp, Madm, Dvt, Giaban)
                    VALUES ('$Masp', '$Tensp', '$Madm', '$Dvt', $Giaban)";

            if (mysqli_query($conn, $sql)) {
                header("Location: Sanpham.php");
                exit;
            } else {
                echo "<script>alert('Lỗi thêm sản phẩm!');</script>";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Tạo Sản Phẩm</title>
    <link rel="stylesheet" href="taosanpham.css">
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
    <div class="khung-tieu-de-chinh">
        <h1 class="tieu-de-lon">Tạo Sản Phẩm Mới</h1>

        <div class="noi-dung-chinh-form">

            <form id="form-san-pham" method="POST">

                <!-- THÔNG TIN CƠ BẢN -->
                <div class="khung-nhap-lieu">
                    <h3>Thông tin cơ bản</h3>

                    <div class="nhom-truong hai-cot">
                        <div class="truong-nhap">
                            <label>Mã sản phẩm *</label>
                            <input type="text" name="Masp">
                        </div>

                        <div class="truong-nhap">
                            <label>Tên sản phẩm *</label>
                            <input type="text" name="Tensp">
                        </div>
                    </div>

                    <div class="nhom-truong hai-cot">
                        <div class="truong-nhap">
                            <label>Danh mục *</label>
                            <select name="Madm">
                                <option value="">-- Chọn danh mục --</option>
                                <?php while ($row = mysqli_fetch_assoc($result_danhmuc)): ?>
                                    <option value="<?= $row['Madm'] ?>">
                                        <?= $row['Tendm'] ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="truong-nhap">
                            <label>Đơn vị tính *</label>
                            <input type="text" name="Dvt">
                        </div>
                    </div>
                </div>

                <!-- GIÁ -->
                <div class="khung-nhap-lieu">
                    <h3>Giá sản phẩm</h3>
                    <div class="truong-nhap">
                        <label>Giá bán *</label>
                        <input type="text" name="Giaban" value="0">
                    </div>
                </div>

                <!-- NÚT -->
                <div class="nhom-nut-chuc-nang">
                    <a href="Sanpham.php" class="nut nut-trove">
                        <i class="fas fa-arrow-left"></i> Quay lại
                    </a>

                    <button type="submit" name="btnluu" class="nut nut-them-moi">
                        <i class="fas fa-save"></i> Lưu Sản Phẩm
                    </button>
                </div>

            </form>
        </div>
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
