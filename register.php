<?php
session_start();
require 'inc/koneksi.php';
require 'inc/csrf.php';
if (isset($_SESSION['id_pengguna'])) { header('Location: penghuni_dashboard.php'); exit; }
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <title>Daftar Akun - SIKOS</title>
  <link rel="stylesheet" href="assets/css/app.css"/>
</head>
<body class="auth-container">
<div class="auth-box">
    <div class="text-center mb-8">
        <div class="w-12 h-12 bg-blue-600 rounded-xl text-white flex items-center justify-center text-2xl font-bold mx-auto mb-4">S</div>
        <h1 class="text-2xl font-bold text-slate-800">Buat Akun Baru</h1>
        <p class="text-slate-500 text-sm mt-2">Bergabunglah untuk mulai menyewa kamar kost</p>
    </div>

    <?php if(!empty($_GET['error'])): ?>
        <div class="bg-red-50 text-red-600 p-3 rounded-lg text-sm mb-6 text-center border border-red-100">
            <?php 
                if($_GET['error'] == 'duplikat') echo "Email sudah terdaftar!";
                elseif($_GET['error'] == 'invalid') echo "Data tidak valid!";
                else echo "Terjadi kesalahan sistem.";
            ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="proses_register.php" class="space-y-4">
        <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
        
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Nama Lengkap</label>
            <input type="text" name="nama" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition" placeholder="Contoh: Budi Santoso" required>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                <input type="email" name="email" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition" placeholder="email@anda.com" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">No. WhatsApp</label>
                <input type="text" name="no_hp" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition" placeholder="0812..." required>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Password</label>
            <input type="password" name="password" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition" placeholder="Minimal 8 karakter" required>
        </div>

        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-xl shadow-lg shadow-blue-200 transition transform hover:-translate-y-0.5">
            Daftar Sekarang
        </button>
    </form>

    <div class="relative my-8">
        <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-slate-200"></div></div>
        <div class="relative flex justify-center text-xs uppercase"><span class="bg-white px-2 text-slate-400">Atau daftar dengan</span></div>
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
        Sudah punya akun? <a href="login.php" class="text-blue-600 font-bold hover:underline">Masuk Disini</a>
    </div>
</div>

</body>
</html>