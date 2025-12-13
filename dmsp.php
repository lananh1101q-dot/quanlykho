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
$sql = "SELECT * FROM Danhmucsp $where";

$list= mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh mục sản phẩm</title>
    <link rel="stylesheet" href="sanpham.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    

</head>
<body>
  <div class="khung-chua-toan-trang">
        
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

        <div class="footer-phan-trang">
         
            <div class="phan-trang-chi-tiet">
                <button disabled><i class="fas fa-chevron-left"></i></button>
                <span>Trang 1/5</span>
                <button><i class="fas fa-chevron-right"></i></button>
            </div>
        </div>
    </div>
</body>
</html>