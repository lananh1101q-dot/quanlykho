<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}
require_once "db.php";

$errors = [];
$success = "";

$action = $_GET['action'] ?? 'add';
$id = $_GET['id'] ?? '';

/* ====== LOAD DROPDOWN ====== */
$nhacungcaps = $pdo->query("SELECT Mancc, Tenncc FROM Nhacungcap")->fetchAll();
$sanphams = $pdo->query("SELECT Masp, Tensp, Dvt FROM Sanpham")->fetchAll();

/* ====== XÓA PHIẾU ====== */
if ($action === 'delete' && $id) {
    try {
        $pdo->beginTransaction();
        $pdo->prepare("DELETE FROM Chitiet_Phieunhap WHERE Manhaphang=?")->execute([$id]);
        $pdo->prepare("DELETE FROM Phieunhap WHERE Manhaphang=?")->execute([$id]);
        $pdo->commit();
        header("Location: phieunhap.php?msg=deleted");
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        $errors[] = "Không thể xóa phiếu nhập";
    }
}

/* ====== LOAD DỮ LIỆU SỬA ====== */
$data = [
    'Manhaphang'=>'',
    'Mancc'=>'',
    'Ngaynhaphang'=>date('Y-m-d'),
    'Ghichu'=>'',
    'items'=>[]
];

if ($action === 'edit' && $id) {
    $stmt = $pdo->prepare("SELECT * FROM Phieunhap WHERE Manhaphang=?");
    $stmt->execute([$id]);
    $data = $stmt->fetch();

    $stmt = $pdo->prepare("SELECT * FROM Chitiet_Phieunhap WHERE Manhaphang=?");
    $stmt->execute([$id]);
    $data['items'] = $stmt->fetchAll();
}

/* ====== SUBMIT ====== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $manhap = $_POST['manhaphang'];
    $mancc  = $_POST['mancc'];
    $ngay   = $_POST['ngaynhap'];
    $ghichu = $_POST['ghichu'];

    $masp = $_POST['masp'];
    $sl   = $_POST['soluong'];
    $dg   = $_POST['dongia'];

    if (!$manhap || !$mancc || !$ngay) {
        $errors[] = "Vui lòng nhập đầy đủ thông tin";
    }

    $items = [];
    $tong = 0;
    for ($i=0; $i<count($masp); $i++) {
        if ($masp[$i] && $sl[$i]>0 && $dg[$i]>0) {
            $items[] = [$masp[$i], $sl[$i], $dg[$i]];
            $tong += $sl[$i] * $dg[$i];
        }
    }

    if (empty($items)) {
        $errors[] = "Phải có ít nhất 1 sản phẩm";
    }

    if (!$errors) {
        try {
            $pdo->beginTransaction();

            if ($action === 'edit') {
                $pdo->prepare("
                    UPDATE Phieunhap
                    SET Mancc=?, Ngaynhaphang=?, Tongtiennhap=?, Ghichu=?
                    WHERE Manhaphang=?
                ")->execute([$mancc,$ngay,$tong,$ghichu,$manhap]);

                $pdo->prepare("DELETE FROM Chitiet_Phieunhap WHERE Manhaphang=?")
                    ->execute([$manhap]);
            } else {
                $pdo->prepare("
                    INSERT INTO Phieunhap(Manhaphang, Mancc, Ngaynhaphang, Tongtiennhap, Ghichu)
                    VALUES (?,?,?,?,?)
                ")->execute([$manhap,$mancc,$ngay,$tong,$ghichu]);
            }

            $stmt = $pdo->prepare("INSERT INTO Chitiet_Phieunhap(Manhaphang, Masp, Soluong, Dongianhap)
                VALUES (?,?,?,?)
            ");
            foreach ($items as $it) {
                $stmt->execute([$manhap,$it[0],$it[1],$it[2]]);
            }

            $pdo->commit();
            $success = ($action==='edit') ? "Cập nhật thành công" : "Thêm phiếu nhập thành công";
        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = "Lỗi xử lý dữ liệu";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Phiếu nhập kho</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-900 text-white p-6">

<h1 class="text-2xl font-bold mb-4">
<?= $action==='edit' ? 'SỬA PHIẾU NHẬP' : 'THÊM PHIẾU NHẬP' ?>
</h1>

<?php if($errors): ?>
<div class="bg-red-800 p-3 mb-4 rounded">
<?php foreach($errors as $e): ?>
<div><?= $e ?></div>
<?php endforeach; ?>
</div>
<?php endif; ?>

<?php if($success): ?>
<div class="bg-green-700 p-3 mb-4 rounded"><?= $success ?></div>
<?php endif; ?>

<form method="post" class="space-y-4">
<div class="grid grid-cols-3 gap-4">
    <input name="manhaphang" placeholder="Mã nhập"
        value="<?= htmlspecialchars($data['Manhaphang']) ?>"
        <?= $action==='edit'?'readonly':'' ?>
        class="p-2 bg-slate-800 rounded">

    <select name="mancc" class="p-2 bg-slate-800 rounded">
        <option value="">Nhà cung cấp</option>
        <?php foreach($nhacungcaps as $n): ?>
        <option value="<?= $n['Mancc'] ?>"
            <?= ($data['Mancc']==$n['Mancc'])?'selected':'' ?>>
            <?= $n['Tenncc'] ?>
        </option>
        <?php endforeach; ?>
    </select>

    <input type="date" name="ngaynhap"
        value="<?= $data['Ngaynhaphang'] ?>"
        class="p-2 bg-slate-800 rounded">
</div>

<textarea name="ghichu" class="w-full p-2 bg-slate-800 rounded"
placeholder="Ghi chú"><?= htmlspecialchars($data['Ghichu']) ?></textarea>

<table class="w-full mt-4 text-sm">
<thead>
<tr class="bg-slate-700">
<th class="p-2">Sản phẩm</th>
<th>Số lượng</th>
<th>Đơn giá</th>
<th></th>
</tr>
</thead>
<tbody id="rows">
<?php
$rows = $data['items'] ?: [[]];
foreach ($rows as $r):
?>
<tr>
<td>
<select name="masp[]" class="bg-slate-800 p-2 w-full">
<option value="">--Chọn--</option>
<?php foreach($sanphams as $sp): ?>
<option value="<?= $sp['Masp'] ?>"
<?= (($r['Masp'] ?? '')==$sp['Masp'])?'selected':'' ?>>
<?= $sp['Tensp'] ?> (<?= $sp['Dvt'] ?>)
</option>
<?php endforeach; ?>
</select>
</td>
<td><input name="soluong[]" type="number" min="1"
value="<?= $r['Soluong'] ?? '' ?>"
class="bg-slate-800 p-2 w-full"></td>
<td><input name="dongia[]" type="number" step="0.01"
value="<?= $r['Dongianhap'] ?? '' ?>"
class="bg-slate-800 p-2 w-full"></td>
<td><button type="button" onclick="this.parentNode.parentNode.remove()">X</button></td>
</tr>
<?php endforeach; ?>
</tbody>
</table><button type="button" onclick="addRow()" class="mt-2 bg-blue-600 px-3 py-1 rounded">
+ Thêm dòng
</button>

<button class="block mt-4 bg-green-600 px-6 py-2 rounded">
<?= $action==='edit'?'Cập nhật':'Lưu phiếu' ?>
</button>
</form>

<script>
function addRow(){
    document.getElementById('rows').insertAdjacentHTML('beforeend',
    document.querySelector('#rows tr').outerHTML);
}
</script>

</body>
</html> 