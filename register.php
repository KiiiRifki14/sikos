<?php
session_start();
require 'inc/koneksi.php';
require 'inc/csrf.php';

if (isset($_SESSION['id_pengguna'])) {
    header('Location: penghuni_dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <title>Daftar - SIKOS</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link rel="stylesheet" href="assets/css/app.css"/>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>

<div class="login-page">
  <div class="login-container">
    
    <div class="login-left">
      <svg class="login-illustration" viewBox="0 0 400 400" xmlns="http://www.w3.org/2000/svg">
        <rect x="50" y="100" width="300" height="250" rx="10" fill="rgba(255,255,255,0.2)" />
        <rect x="50" y="100" width="300" height="60" rx="10" fill="rgba(255,255,255,0.3)" />
        <rect x="80" y="180" width="80" height="60" rx="5" fill="rgba(255,255,255,0.25)" />
        <rect x="180" y="180" width="80" height="60" rx="5" fill="rgba(255,255,255,0.25)" />
        <rect x="280" y="180" width="50" height="120" rx="5" fill="rgba(255,255,255,0.25)" />
        <rect x="80" y="250" width="80" height="50" rx="5" fill="rgba(255,255,255,0.25)" />
        <rect x="180" y="250" width="80" height="50" rx="5" fill="rgba(255,255,255,0.25)" />
        <circle cx="200" cy="80" r="25" fill="#f59e0b" />
        <path d="M100,315 L300,315" stroke="rgba(255,255,255,0.4)" stroke-width="4" stroke-dasharray="10,10" />
        <text x="200" y="340" text-anchor="middle" fill="white" font-size="18" font-weight="bold" font-family="Poppins">SIKOS</text>
      </svg>
      <h1>Buat Akun Baru</h1>
      <p>Bergabunglah dengan SIKOS untuk pengalaman nge-kost yang lebih baik.</p>
    </div>

    <div class="login-right">
      <div class="login-header">
        <h2>Registrasi</h2>
        <p>Isi data diri Anda dengan lengkap</p>
      </div>

      <?php 
      if (!empty($_GET['error'])) {
        $pesan = "Terjadi kesalahan.";
        if ($_GET['error'] == 'duplikat') $pesan = "Email sudah terdaftar!";
        elseif ($_GET['error'] == 'invalid') $pesan = "Data tidak valid!";
        echo '<div class="alert-box alert-red">'.$pesan.'</div>';
      } 
      ?>

      <form method="POST" action="proses_register.php">
        <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
        
        <div class="form-group">
          <label class="form-label">Nama Lengkap</label>
          <input type="text" name="nama" class="form-input" placeholder="Nama Lengkap" required>
        </div>

        <div class="form-group">
          <label class="form-label">Email</label>
          <input type="email" name="email" class="form-input" placeholder="Email Aktif" required>
        </div>

        <div class="form-group">
          <label class="form-label">No HP</label>
          <input type="text" name="no_hp" class="form-input" placeholder="Nomor WhatsApp" required>
        </div>

        <div class="form-group">
          <label class="form-label">Password</label>
          <input type="password" name="password" class="form-input" placeholder="Min. 8 Karakter" minlength="8" required>
        </div>

        <button type="submit" class="btn-login-full">Daftar</button>
      </form>

      <div style="text-align:center; margin-top:20px; font-size:14px; color:#64748b;">
        Sudah punya akun? <a href="login.php" style="color:var(--primary); font-weight:600;">Login sekarang</a>
      </div>
    </div>

  </div>
</div>
</body>
</html>