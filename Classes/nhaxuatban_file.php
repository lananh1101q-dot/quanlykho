<?php

function nhaxuatban_ins($mnxb,$tnxb,$dt,$mail,$dc,$con){
        $sql="Insert into Nhaxuatban Values('$mnxb','$tnxb','$dt','$mail','$dc')";
        $kq= mysqli_query($con,$sql);
        return $kq;
    }
    if(isset($_POST['btnUpload'])){
            $file=$_FILES['txtTenfile']['tmp_name'];
            $objReader=PHPExcel_IOFactory::createReaderForFile($file);
            $objExcel=$objReader->load($file);
            //Lấy sheet hiện tại
            $sheet=$objExcel->getSheet(0);
            $sheetData=$sheet->toArray(null,true,true,true);
            for($i=2;$i<=count($sheetData);$i++){
                $mnxb=$sheetData[$i]["A"];
                $tnxb=$sheetData[$i]["B"];
                $dt=$sheetData[$i]["C"];
                $mail=$sheetData[$i]["D"];
                $dc=$sheetData[$i]["E"];
                nhaxuatban_ins($mnxb,$tnxb,$dt,$mail,$dc,$con);
            }
            echo "<script>alert('Upload file thành công!')</script>";
    }
    //Đóng kết nối
    mysqli_close($con);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    
</body>
</html>
