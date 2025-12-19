<?php
// KẾT NỐI DATABASE
$conn = mysqli_connect("localhost", "root", "", "quanlykho");

$sql_danhmuc = "SELECT  Tendm FROM danhmucsp  ORDER BY Tendm ASC";
$result_danhmuc = mysqli_query($conn, $sql_danhmuc);

// Kiểm tra lỗi truy vấn (Tùy chọn)
if (!$result_danhmuc) {
    die("Lỗi truy vấn danh mục: " . mysqli_error($conn));
}

// XỬ LÝ THÊM DỮ LIỆU
if (isset($_POST['btnluu'])) {
    // Đổi tên biến cho dễ quản lý
    $Masp   = $_POST['Masp'];
    $Tensp  = $_POST['Tensp'];
    $Madm   = $_POST['Madm'];
    $Dvt    = $_POST['Dvt'];
    
    // Xử lý Giaban: chuyển sang kiểu số thập phân, loại bỏ dấu phẩy (nếu có)
    $Giaban = str_replace(',', '', $_POST['Giaban']); 
    // Nếu dùng decimal, giá trị phải là số
    $Giaban = floatval($Giaban);
    $sql = "INSERT INTO sanpham(Masp, Tensp, Madm, Dvt, Giaban)
            VALUES ('$Masp', '$Tensp', '$Madm', '$Dvt', '$Giaban')";   
           

   if (mysqli_query($conn, $sql)) {
        // Thành công
        echo "luu thanh cong". $sql . "<br>"; 
        header("Location: taosanpham.php");
        
        exit;
    } else {
        // THẤT BẠI: In ra lỗi SQL
        echo "Lỗi: " . $sql . "<br>" . mysqli_error($conn);
    }
}
 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tạo Sản Phẩm</title>
    <link rel="stylesheet" href="taosanpham.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

</head>
<body>
   
<div class="khung-trang">
        
      
    
    <div class="khung-tieu-de-chinh">
        <h1 class="tieu-de-lon">Tạo Sản Phẩm mới</h1>
        <a href="/quanlykho/Sanpham.php" class="nut-quay-lai" title="Quay lại danh sách sản phẩm">
            <i class="fas fa-arrow-left"></i>
        </a>
        
        
        

        <div class="noi-dung-chinh-form">
            
            <form id="form-san-pham" action="/quanlykho/taosanpham.php" method="POST">
                
                <div class="khung-nhap-lieu thong-tin-co-ban">
                    <h3 class="tieu-de-khung">Thông tin cơ bản</h3>

                    <div class="nhom-truong hai-cot">
                        <div class="truong-nhap">
                            <label for="Masp">Mã sản phẩm*</label>
                            <input type="text" id="Masp" name="Masp" placeholder="VD: SP001" required>
                        </div>
                        <div class="truong-nhap">
                            <label for="Tensp">Tên sản phẩm*</label>
                             <input type="text" id="Tensp" name="Tensp" placeholder="Nhập tên sản phẩm" required>
                        </div>
                    </div>

                 
                    <div class="nhom-truong hai-cot">
                        <div class="truong-nhap lua-chon-va-them">
                            <label for="Madm">Danh mục</label>
                             <div class="khung-input-nhom">
                                <select id="Madm" name="Madm">
                                    <option value="">Chọn một tùy chọn</option>
                                     <?php
                                    // Kiểm tra xem có dữ liệu trả về không
                                    if (mysqli_num_rows($result_danhmuc) > 0) {
                                        // Lặp qua từng dòng dữ liệu để tạo thẻ <option>
                                        while ($row = mysqli_fetch_assoc($result_danhmuc)) {
                                            // Giá trị (value) là Madm (Mã danh mục)
                                            // Hiển thị cho người dùng là Tendm (Tên danh mục)
                                            echo '<option value="'  . $row['Tendm'] . '</option>';
                                        }
                                    } else {
                                        echo '<option value="" disabled>Chưa có danh mục nào</option>';
                                    }
                                    ?>
                                </select>
                                <button type="button" class="nut-them" id="nut-them-dm">+</button>
                            </div>
                        </div>
                        <div class="truong-nhap lua-chon-va-them">
                            <label for="Dvt">Đơn vị tính</label>
                            <div class="khung-input-nhom">
                                <input type="text" id="Dvt" name="Dvt" placeholder="Ví dụ: Chiếc, Kg, Hộp">
                                <button type="button" class="nut-them" id="nut-them-dvt">+</button>
                            </div>
                            <small class="chu-thich">Đơn vị tính mặc định</small>
                        </div>
                    </div>

                    <div class="truong-nhap mot-cot vung-tai-anh">
                        <label>Chọn ảnh định dạng png, jpg, jpeg</label>
                        <input type="file" id="anh_san_pham" name="anh_san_pham" style="display:none;">
                        <div class="khu-vuc-keo-tha" onclick="document.getElementById('anh_san_pham').click()">
                            <p>Kéo thả tệp của bạn hoặc <a href="#">Tìm kiếm</a></p>
                        </div>
                    </div>
                </div>

                <div class="khung-nhap-lieu gia-sp">
                    <h3 class="tieu-de-khung">Giá sản phẩm</h3>

                    
                        <div class="truong-nhap gia-co-dv">
                            <label for="Giaban">Giá bán*</label>
                            <div class="khung-gia-dv">
                                <input type="number" id="Giaban" name="Giaban" value="0" required>
                                <span class="don-vi">đ</span>
                            </div>
                        </div>
        
                   
                </div>

                

                <div class="vung-nut-luu">
                    <button type="submit" name="btnluu" class="nut-luu-chinh" id="nut-submit-sp">Lưu Sản Phẩm</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Tên biến JavaScript khớp với ID của form
        var inputGiaNhap = document.getElementById("gianhap");
        var inputGiaBanLe = document.getElementById("Giaban"); // ID khớp cột Giaban CSDL
        var inputLoiNhuan = document.getElementById("loi-nhuan");

        // Hàm tính lợi nhuận
        function tinhLoiNhuan() {
            var nhap = parseFloat(inputGiaNhap.value) || 0;
            var ban = parseFloat(inputGiaBanLe.value) || 0;
            
            if (ban > nhap && nhap > 0) {
                var tyLeLoiNhuan = ((ban - nhap) / nhap) * 100;
                inputLoiNhuan.value = tyLeLoiNhuan.toFixed(2);
            } else {
                inputLoiNhuan.value = "0.00";
            }
        }

        // Gắn sự kiện (Giống như trong code mẫu bạn cung cấp)
        inputGiaNhap.addEventListener("input", tinhLoiNhuan);
        inputGiaBanLe.addEventListener("input", tinhLoiNhuan);
        
        // Sự kiện khi click vào nút thêm Danh mục
        document.getElementById("nut-them-dm").onclick = function() {
            alert("Mở pop-up để tạo mới Danh mục (Madm) và cập nhật dropdown!");
        };
        
        // Sự kiện khi click vào nút thêm Đơn vị tính
        document.getElementById("nut-them-dvt").onclick = function() {
            alert("Mở pop-up để tạo mới Đơn vị tính (Dvt)!");
        };

    </script>

</body>
</html>