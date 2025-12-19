<?php
/**
 * Migration: Thêm cột Makho vào bảng Phieunhap
 * Chạy script này một lần để cập nhật database
 */
require_once __DIR__ . '/db.php';

try {
    // Kiểm tra xem cột đã tồn tại chưa
    $check = $pdo->query("SHOW COLUMNS FROM Phieunhap LIKE 'Makho'")->fetch();
    
    if ($check) {
        echo "Cột Makho đã tồn tại trong bảng Phieunhap.\n";
    } else {
        // Thêm cột Makho
        $pdo->exec("ALTER TABLE Phieunhap ADD COLUMN Makho VARCHAR(50) AFTER Mancc");
        $pdo->exec("ALTER TABLE Phieunhap ADD FOREIGN KEY (Makho) REFERENCES Kho(Makho)");
        echo "Đã thêm cột Makho vào bảng Phieunhap thành công.\n";
    }
    
    echo "Migration hoàn tất.\n";
} catch (Exception $e) {
    echo "Lỗi: " . $e->getMessage() . "\n";
}
