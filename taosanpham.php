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
</head>
<body>

<div class="khung-trang">
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

<!-- ===============================
     JAVASCRIPT VALIDATE + DIALOG
================================ 
<script>
document.getElementById("form-san-pham").addEventListener("submit", function (e) {

    const masp   = document.querySelector("[name='Masp']").value.trim();
    const tensp  = document.querySelector("[name='Tensp']").value.trim();
    const madm   = document.querySelector("[name='Madm']").value;
    const dvt    = document.querySelector("[name='Dvt']").value.trim();
    const giaban = document.querySelector("[name='Giaban']").value.trim();

    if (masp === "" || tensp === "" || madm === "" || dvt === "") {
        alert("❌ Vui lòng nhập đầy đủ thông tin!");
        e.preventDefault();
        return;
    }

    if (giaban === "" || isNaN(giaban)) {
        alert("❌ Giá bán phải là số!");
        e.preventDefault();
        return;
    }

    if (Number(giaban) < 0) {
        alert("❌ Giá bán không được nhỏ hơn 0!");
        e.preventDefault();
        return;
    }
});
</script>-->

</body>
</html>
