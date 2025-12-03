<?php
session_start();
require 'inc/koneksi.php';
require 'inc/csrf.php';
require 'inc/utils.php'; // Load fungsi flash message
if (isset($_SESSION['id_pengguna'])) { header('Location: penghuni_dashboard.php'); exit; }
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <title>Login - SIKOS</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="assets/css/app.css"/>
</head>
<body class="bg-slate-50 flex items-center justify-center min-h-screen p-4">

<div class="bg-white p-8 rounded-2xl shadow-xl w-full max-w-md border border-slate-100">
    <div class="text-center mb-8">
        <div class="w-12 h-12 bg-blue-600 rounded-xl text-white flex items-center justify-center text-2xl font-bold mx-auto mb-4">S</div>
        <h1 class="text-2xl font-bold text-slate-800">Selamat Datang Kembali</h1>
        <p class="text-slate-500 text-sm mt-2">Masuk untuk mengelola akun kost Anda</p>
    </div>

    <?php display_flash_message(); ?>

    <form method="POST" action="proses_login.php" class="space-y-5">
        <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
        
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Email Address</label>
            <input type="email" name="email" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition" placeholder="nama@email.com" required>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Password</label>
            <input type="password" name="password" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition" placeholder="••••••••" required>
        </div>

        <div class="text-right">
            <a href="forgot.php" class="text-sm text-blue-600 hover:underline font-medium">Lupa Password?</a>
        </div>

        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-xl shadow-lg shadow-blue-200 transition transform hover:-translate-y-0.5">
            Masuk Sekarang
        </button>
    </form>

    <div class="relative my-8">
        <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-slate-200"></div></div>
        <div class="relative flex justify-center text-xs uppercase"><span class="bg-white px-2 text-slate-400">Atau masuk dengan</span></div>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <button onclick="alert('Fitur Google Login belum tersedia saat ini.')" class="btn-social">
            <img src="https://www.svgrepo.com/show/475656/google-color.svg" class="w-5 h-5"> 
            <span class="text-sm text-slate-600">Google</span>
        </button>
        <button onclick="alert('Fitur Facebook Login belum tersedia saat ini.')" class="btn-social">
            <img src="https://www.svgrepo.com/show/475647/facebook-color.svg" class="w-5 h-5"> 
            <span class="text-sm text-slate-600">Facebook</span>
        </button>
    </div>

    <div class="text-center mt-8 text-sm text-slate-500">
        Belum punya akun? <a href="register.php" class="text-blue-600 font-bold hover:underline">Daftar Disini</a>
    </div>
</div>

</body>
</html>