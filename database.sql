CREATE DATABASE QuanLyKho 
USE QuanLyKho;


CREATE TABLE Danhmucsp (
    Madm INT PRIMARY KEY AUTO_INCREMENT,
    Tendm VARCHAR(100) NOT NULL UNIQUE
);


CREATE TABLE Sanpham (
    Masp VARCHAR(50) PRIMARY KEY,
    Tensp VARCHAR(255) NOT NULL,
    Madm INT,
    Dvt VARCHAR(50) NOT NULL,
    Giaban DECIMAL(18, 2) DEFAULT 0,
    FOREIGN KEY (Madm) REFERENCES Danhmucsp(Madm)
);


CREATE TABLE Nhacungcap (
    Mancc VARCHAR(50) PRIMARY KEY,
    Tenncc VARCHAR(255) NOT NULL,
    Sdtncc VARCHAR(15),
    Diachincc VARCHAR(255)
);

CREATE TABLE Loaikhachhang (
    Maloaikh INT PRIMARY KEY AUTO_INCREMENT,
    Tenloaikh VARCHAR(100) NOT NULL,
    Motaloaikh TEXT
);


CREATE TABLE Khachhang (
    Makh VARCHAR(50) PRIMARY KEY,
    Tenkh VARCHAR(255) NOT NULL,
    Sdtkh VARCHAR(15),
    Diachikh VARCHAR(255),
    Maloaikh INT,
    FOREIGN KEY (Maloaikh) REFERENCES Loaikhachhang(Maloaikh)
);


CREATE TABLE Phieunhap (
    Manhaphang VARCHAR(50) PRIMARY KEY,
    Mancc VARCHAR(50),
    Makho VARCHAR(50),
    Ngaynhaphang DATE NOT NULL,
    Tongtiennhap DECIMAL(18, 2) DEFAULT 0,
    Ghichu TEXT,
    FOREIGN KEY (Mancc) REFERENCES Nhacungcap(Mancc),
    FOREIGN KEY (Makho) REFERENCES Kho(Makho)
);


CREATE TABLE Chitiet_Phieunhap (
    Id INT PRIMARY KEY AUTO_INCREMENT,
    Manhaphang VARCHAR(50),
    Masp VARCHAR(50),
    Soluong INT NOT NULL,
    Dongianhap DECIMAL(18, 2) NOT NULL,
    Thanhtien DECIMAL(18, 2) AS (Soluong * Dongianhap) STORED, -- Trường tính toán
    FOREIGN KEY (Manhaphang) REFERENCES Phieunhap(Manhaphang),
    FOREIGN KEY (Masp) REFERENCES Sanpham(Masp)
);


CREATE TABLE Phieuxuat (
    Maxuathang VARCHAR(50) PRIMARY KEY,
    Makh VARCHAR(50),
    Ngayxuat DATE NOT NULL,
    Tongtienxuat DECIMAL(18, 2) DEFAULT 0,
    Ghichu TEXT,
    FOREIGN KEY (Makh) REFERENCES Khachhang(Makh)
);


CREATE TABLE Chitiet_Phieuxuat (
    Id INT PRIMARY KEY AUTO_INCREMENT,
    Maxuathang VARCHAR(50),
    Masp VARCHAR(50),
    Soluong INT NOT NULL,
    Dongiaxuat DECIMAL(18, 2) NOT NULL,
    Thanhtien DECIMAL(18, 2) AS (Soluong * Dongiaxuat) STORED,
    FOREIGN KEY (Maxuathang) REFERENCES Phieuxuat(Maxuathang),
    FOREIGN KEY (Masp) REFERENCES Sanpham(Masp)
);

CREATE TABLE Kho (
    Makho VARCHAR(50) PRIMARY KEY,
    Tenkho VARCHAR(100) NOT NULL,
    Diachi TEXT
);


CREATE TABLE Tonkho (
    Makho VARCHAR(50),
    Masp VARCHAR(50),
    Soluongton INT DEFAULT 0,
    PRIMARY KEY (Makho, Masp), 
    FOREIGN KEY (Makho) REFERENCES Kho(Makho),
    FOREIGN KEY (Masp) REFERENCES Sanpham(Masp)
);
CREATE TABLE Nguoidung (
    Manv VARCHAR(50) PRIMARY KEY,
    Tendangnhap VARCHAR(100) NOT NULL UNIQUE,
    Matkhau VARCHAR(255) NOT NULL, 
    Hovaten VARCHAR(255),
    Email VARCHAR(100),
    Vaitro VARCHAR(50) NOT NULL
);