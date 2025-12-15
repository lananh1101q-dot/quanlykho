<?php
$dsn = 'mysql:host=127.0.0.1';
$user = 'root';
$pass = '';
$options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_EMULATE_PREPARES => true];

$sqlFile = __DIR__ . '/../database.sql';
if (!file_exists($sqlFile)) {
    exit("File database.sql không tồn tại\n");
}
$sql = file_get_contents($sqlFile);

try {
    $pdo = new PDO($dsn, $user, $pass, $options);

    // Tạo DB + chọn DB thủ công để tránh lỗi USE trong script
    $pdo->exec("CREATE DATABASE IF NOT EXISTS QuanLyKho CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE QuanLyKho");

    // Drop tất cả các bảng nếu đã tồn tại
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    $tables = ['Chitiet_Phieuxuat', 'Chitiet_Phieunhap', 'Tonkho', 'Phieuxuat', 'Phieunhap', 'Nguoidung', 'Kho', 'Khachhang', 'Loaikhachhang', 'Nhacungcap', 'Sanpham', 'Danhmucsp'];
    foreach ($tables as $table) {
        $pdo->exec("DROP TABLE IF EXISTS $table");
    }
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

    // Bỏ các dòng CREATE DATABASE / USE, chỉ giữ phần tạo bảng
    $lines = array_filter(array_map('trim', explode("\n", $sql)));
    $filteredLines = [];
    foreach ($lines as $line) {
        $upper = strtoupper($line);
        if (strpos($upper, 'CREATE DATABASE') === 0) continue;
        if (strpos($upper, 'USE ') === 0) continue;
        $filteredLines[] = $line;
    }
    $filteredSql = implode("\n", $filteredLines);

    // Tách statement theo dấu ; trên nhiều dòng
    $statements = array_filter(array_map('trim', explode(';', $filteredSql)));
    foreach ($statements as $stmtSql) {
        if ($stmtSql === '') continue;
        $pdo->exec($stmtSql);
    }

    $hash = '$2y$12$7DPocUcEd0OICy7K8AStwO5zF8tWpax/tM5tkmRVhthfBmbjCt0bu';
    $stmt = $pdo->prepare("INSERT INTO QuanLyKho.Nguoidung (Manv, Tendangnhap, Matkhau, Hovaten, Email, Vaitro)
        VALUES ('NV001','admin',:hash,'Quản trị viên','admin@example.com','admin')
        ON DUPLICATE KEY UPDATE Tendangnhap=VALUES(Tendangnhap), Matkhau=VALUES(Matkhau), Hovaten=VALUES(Hovaten), Email=VALUES(Email), Vaitro=VALUES(Vaitro)");
    $stmt->execute([':hash' => $hash]);
    echo "Import OK\n";
} catch (Exception $e) {
    echo 'Lỗi: ' . $e->getMessage() . "\n";
    exit(1);
}
