<?php
session_start();
require 'inc/koneksi.php';
require 'inc/csrf.php';

// Redirect jika user sudah login
if (isset($_SESSION['id_pengguna'])) {
  if (isset($_SESSION['next_booking_kamar'])) {
    $idk = $_SESSION['next_booking_kamar'];
    unset($_SESSION['next_booking_kamar']);
    header("Location: booking.php?id_kamar=$idk");
    exit;
  }
  if ($_SESSION['peran']=='OWNER') header('Location: owner_dashboard.php');
  elseif ($_SESSION['peran']=='ADMIN') header('Location: admin/index.php');
  else header('Location: penghuni_dashboard.php');
  exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <title>Login - SIKOS</title>
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
      <h1>Selamat Datang</h1>
      <p>Sistem Informasi Kost Terintegrasi<br>Login untuk mengelola akun Anda.</p>
    </div>

    <div class="login-right">
      <div class="login-header">
        <h2>Login Akun</h2>
        <p>Silakan masuk menggunakan email dan password</p>
      </div>

      <?php
      if (!empty($_GET['error'])) echo '<div class="alert-box alert-red">Login gagal! Periksa email & password.</div>';
      if (!empty($_GET['info'])) echo '<div class="alert-box alert-green">'.htmlspecialchars($_GET['info']).'</div>';
      ?>

      <form method="POST" action="proses_login.php">
        <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
        
        <div class="form-group">
          <label class="form-label">Email</label>
          <input type="email" name="email" class="form-input" placeholder="nama@email.com" required>
        </div>

        <div class="form-group">
          <label class="form-label">Password</label>
          <input type="password" name="password" class="form-input" placeholder="Masukkan password" required>
        </div>

        <div class="forgot-password">
            <a href="forgot.php">Lupa Password?</a>
        </div>

        <button type="submit" class="btn-login-full">Masuk</button>
      </form>

      <div style="text-align:center; margin-top:20px; font-size:14px; color:#64748b;">
        Belum punya akun? <a href="register.php" style="color:var(--primary); font-weight:600;">Daftar disini</a>
      </div>
    </div>

  </div>
</div>
</body>
</html>