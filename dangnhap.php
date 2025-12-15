<?php
session_start();
// Prevent browser caching
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
require_once __DIR__ . '/db.php';

$error = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = "Vui lòng nhập đầy đủ email và mật khẩu.";
    } else {
        $stmt = $pdo->prepare(
            "SELECT Manv, Tendangnhap, Matkhau, Hovaten, Email, Vaitro
             FROM Nguoidung
             WHERE Tendangnhap = ? OR Email = ?
             LIMIT 1"
        );
        $stmt->execute([$email, $email]);
        $user = $stmt->fetch();

        if ($user) {
            $hash = $user['Matkhau'];
            $isValid = password_verify($password, $hash) || $password === $hash; // hỗ trợ mật khẩu chưa băm

            if ($isValid) {
                $_SESSION['user'] = [
                    'id' => $user['Manv'],
                    'username' => $user['Tendangnhap'],
                    'email' => $user['Email'],
                    'fullname' => $user['Hovaten'],
                    'role' => $user['Vaitro'],
                ];
                header("Location: dashboard.php");
                exit;
            }
        }
        $error = "Sai email hoặc mật khẩu. Vui lòng thử lại.";
    }
}
?>
<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Đăng nhập — Quản lý kho</title>

  <!-- Tailwind CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- Heroicons CDN (cho icon) -->
  <script src="https://unpkg.com/feather-icons"></script>

  <style>
    /* Gradient background subtle like ảnh gốc */
    body {
      background: radial-gradient(ellipse at center, rgba(17,24,39,0.95) 0%, #080808 60%);
      min-height: 100vh;
    }
  </style>
</head>
<body class="antialiased text-gray-100">

  <div class="min-h-screen flex items-center justify-center px-4">
    <div class="w-full max-w-xl">
      <div class="bg-slate-900/60 backdrop-blur-md border border-slate-800 rounded-2xl shadow-2xl overflow-hidden">
        <div class="md:flex">
          <!-- Form container, full width now -->
          <div class="w-full p-8 md:p-10">
            <h1 class="text-center text-2xl font-semibold text-white">Quản Lý Kho Hàng</h1>
            <p class="text-center text-slate-300 mt-2 font-medium text-lg">Đăng nhập vào tài khoản của bạn</p>

            <?php if ($error): ?>
              <div class="mt-4 bg-red-900/60 border border-red-700 text-red-200 px-4 py-3 rounded">
                <?= htmlspecialchars($error) ?>
              </div>
            <?php endif; ?>

            <form method="post" action="dangnhap.php" class="mt-6 space-y-4">
              <div>
                <label class="block text-sm text-slate-300 mb-2">Email <span class="text-red-400">*</span></label>
                <input required name="email" type="email" placeholder="you@example.com"
                  class="w-full px-4 py-3 rounded-lg bg-slate-800 border border-slate-700 text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-sky-500" />
              </div>

              <div>
                <label class="block text-sm text-slate-300 mb-2">Mật khẩu <span class="text-red-400">*</span></label>
                <div class="relative">
                  <input required name="password" type="password" placeholder="••••••••"
                    class="w-full px-4 py-3 rounded-lg bg-slate-800 border border-slate-700 text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-sky-500 pr-12" />
                  <button type="button" onclick="togglePassword(this)" class="absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-200">
                    <!-- simple eye icon -->
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                  </button>
                </div>
              </div>

              <div class="flex items-center justify-between text-sm text-slate-300">
                <label class="inline-flex items-center gap-2">
                  <input type="checkbox" class="rounded border-slate-600 bg-slate-800 text-sky-500 focus:ring-sky-500" />
                  Ghi nhớ đăng nhập
                </label>
                <a href="#" class="text-sky-400 hover:underline">Quên mật khẩu?</a>
              </div>

              <div class="pt-4">
                <button type="submit"
                        class="w-full inline-flex items-center justify-center gap-2 bg-sky-500 hover:bg-sky-600 focus:ring-2 focus:ring-sky-400 text-slate-900 font-semibold px-4 py-3 rounded-lg shadow">
                  <!-- icon -->
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                  </svg>
                  Đăng nhập
                </button>
              </div>
            </form>

            <p class="mt-6 text-center text-xs text-slate-500">© <?= date('Y') ?> Quản lý kho — Bản demo</p>
          </div>
        </div>
      </div>
    </div>
  </div>

<script>
  function togglePassword(btn){
    const input = btn.parentElement.querySelector('input');
    if (!input) return;
    input.type = input.type === 'password' ? 'text' : 'password';
  }
  // feather icons init if used
  if (typeof feather !== 'undefined') feather.replace();
</script>
</body>
</html>
