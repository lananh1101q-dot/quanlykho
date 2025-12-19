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
</head>
<body>
    <div class="khung-chua-toan-trang">
        
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
</body>
</html>