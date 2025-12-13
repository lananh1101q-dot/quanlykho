<?php
// Kết nối CSDL
$conn = mysqli_connect("localhost", "root", "", "quanlykho");


$list= mysqli_query($conn, "SELECT * FROM sanpham ORDER BY Masp ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SanPham</title>
    <link rel="stylesheet" href="sanpham.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    

</head>
<body>
  <div class="khung-chua-toan-trang">
        
        <div class="header-danh-sach">
            <h2 class="tieu-de-chinh">Sản Phẩm</h2>
            <div class="nhom-nut-chuc-nang">
                <button class="nut nut-nhap-excel"><i class="fas fa-file-import"></i> Nhập Excel</button>
                <a href="/api/Sanpham/export" class="nut nut-xuat-excel"><i class="fas fa-file-excel"></i> Xuất Excel</a>
                <a href="/quanlykho/taosanpham.php" class="nut nut-tao-moi"><i class="fas fa-plus"></i> Tạo sản phẩm</a>
            </div>
        </div>

        <div class="thanh-tim-kiem-loc">
            <div class="khung-tim-kiem">
                <i class="fas fa-search icon-tim-kiem"></i>
                <input type="text" name="tkma" placeholder="Tìm kiếm mã" class="input-tim-kiem">
                <i class="fas fa-filter icon-loc"></i>
            </div>
        </div>
                <div class="thanh-tim-kiem-loc">
            <div class="khung-tim-kiem">
                <i class="fas fa-search icon-tim-kiem"></i>
                <input type="text" name="tkten" placeholder="Tìm kiếm tên" class="input-tim-kiem">
                <i class="fas fa-filter icon-loc"></i>
            </div>
        </div>

 

        <div class="khung-bang-bao-quanh">
            <table class="bang-san-pham">
                <thead>
                    <tr>
                        
                        <th class="cot-anh">Ảnh</th>
                        <th class="cot-sap-xep" data-sort="Masp">Mã SP</th>
                        <th class="cot-sap-xep" data-sort="Tensp">Tên sản phẩm</th>
                        <th class="cot-sap-xep" data-sort="Madm">Danh mục</th> 
                        <th class="cot-sap-xep" data-sort="Dvt">ĐVT</th>
                        <th class="cot-sap-xep" data-sort="Giaban">Giá bán</th>
                        <th class="cot-sap-xep" data-sort="Soluongton">Tồn kho</th>
                        <th class="cot-sap-xep">Dạng bán</th>
                        <th class="cot-hanh-dong-nut">Thao tác</th> 
                    </tr>
                </thead>
                <tbody>
                    
                   
                  
                        
                       <?php while ($row = mysqli_fetch_assoc($list)) { ?>
            <tr>
                <td class="cot-anh">
                                <div class="khung-anh-san-pham">
                                    <img src="/images/sanpham/<?php echo htmlspecialchars($row['Anhsp']); ?>" alt="Ảnh sản phẩm">
                                </div>
                <td><?= $row['Masp'] ?></td>
                <td><?= $row['Tensp'] ?></td>
                <td><?= $row['Madm'] ?></td>
                <td><?= $row['Dvt'] ?></td>
                <td><?= $row['Giaban'] ?></td>
                <td><?= $row['Soluongton'] ?></td>
                 <td><?= $row['Dangban'] ?></td>
                           
                        <td class="cot-hanh-dong-nut-td">
                            <a href="/sua-sanpham/SP0026" class="nut-hanh-dong nut-sua" title="Sửa"><i class="fas fa-edit"></i></a>
                            <button class="nut-hanh-dong nut-xoa" data-masp="SP0026" title="Xóa"><i class="fas fa-trash-alt"></i></button>
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