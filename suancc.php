<?php
$conn = mysqli_connect("localhost", "root", "", "quanlykho");
$sql_danhmuc = "SELECT Madm, Tendm FROM Danhmucsp ORDER BY Tendm ASC";
$result_danhmuc = mysqli_query($conn, $sql_danhmuc); // Lấy danh mục để hiển thị trong dropdown

$Mancc = isset($_GET['Mancc']) ? $_GET['Mancc'] : '';

$sql = "SELECT * FROM Nhacungcap WHERE Mancc = '$Mancc'";
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($result); // Lấy dữ liệu nhà cung cấp
if (!$row) {
    die("Không tìm thấy nhà cung cấp!");
}


// ===============================
// XỬ LÝ LƯU (BACKEND)
// ===============================
if (isset($_POST['btnluu'])) {

    $Mancc   = trim($_POST['Mancc']);
    $Tenncc  = trim($_POST['Tenncc']);
  
    $Sdt   = $_POST['Sdtncc'];
    $Diachi   = $_POST['Diachincc'];
   

    // --- Validate backend ---
    if ($Mancc == "" || $Tenncc == "" || $Sdt == "" || $Diachi == "") {
        echo "<script>alert('Vui lòng nhập đầy đủ thông tin!');</script>";
    } 
    // Kiểm tra số điện thoại có phải là số không 
    elseif (!is_numeric($Sdt)) {
        echo "<script>alert('Số điện thoại phải là số!');</script>";
    } else {

     

            // --- Insert ---
            $sql = " UPDATE Nhacungcap SET  Tenncc='$Tenncc', Sdtncc='$Sdt', Diachincc='$Diachi'
                    WHERE Mancc='$Mancc'";

            if (mysqli_query($conn, $sql)) {
                echo "<script>alert('thông cong!'); window.location.href='Nhacungcap.php';</script>";
                
            } else {
                echo "<script>alert('Lỗi thêm sản phẩm!');</script>";
            }
        
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Kho Đơn Giản</title>
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
        

        /* 4. Style cho nội dung bên trong */
        ul { list-style: none; }
        li a { color: #bdc3c7; text-decoration: none; line-height: 2.5; display: block; }
        li a:hover { color: #ff4d4d; }

        .row { margin-bottom: 15px; }
        .row label { display: block; font-weight: bold; margin-bottom: 5px; }
        .row input, .row select { width: 100%; padding: 10px; border: 1px solid #ccc; }
        .nut {
    padding: 12px 20px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 600;
    text-decoration: none;
    transition: background-color 0.2s;
}

        .btn { background: #ff4d4d; color: white; border: none; padding: 10px 20px; cursor: pointer; width: 10%; font-weight: bold; }
        .btn:hover { background: #333; }
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
            <h3 style="margin-bottom: 20px;">NHẬP THÔNG TIN VẬT TƯ</h3>
            <form method="POST">
            <div class="row">
                <label>Mã số:</label>
                <input type="text" name="Mancc" placeholder="Ví dụ: MS01" value="<?php echo $row['Mancc'] ?>" readonly>
            </div>

            <div class="row">
                <label>Tên vật tư:</label>
                <input type="text" name="Tenncc" placeholder="Thép, xi măng..." value="<?php echo $row['Tenncc'] ?>">
            </div>
        

            <div class="row">
                <label>Số điện thoại:</label>
                <input type="text" name="Sdtncc" value="<?php echo $row['Sdtncc'] ?>">
            </div>
              <div class="row">
                <label>Địa chỉ:</label>
                <input type="text" name="Diachincc" value="<?php echo $row['Diachincc'] ?>">
            </div>
            
           <div class="layout-chia-doi">
             <a class="nut btn" href="Nhacungcap.php">hủy</a>
            <button type="submit" name="btnluu" class="nut btn">thay đổi</button>
            </div>
            </form>
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