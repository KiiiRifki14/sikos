<?php
session_start();
require 'inc/koneksi.php';
require 'inc/csrf.php';

// Jika sudah login, lempar ke dashboard
if (isset($_SESSION['id_pengguna'])) {
    header('Location: penghuni_dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Reset Password - SIKOS</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/app.css"/>
  <style>
    /* Style konsisten dengan Login & Register */
    body { font-family: 'Poppins', sans-serif; background: #f8fafc; }
    .login-page { display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 40px 20px; background: linear-gradient(135deg, #f8fafc 0%, #e0e7ff 100%); }
    .login-container { display: grid; grid-template-columns: 1fr 1fr; max-width: 1000px; width: 100%; background: white; border-radius: 24px; overflow: hidden; box-shadow: 0 20px 60px rgba(0,0,0,0.15); }
    
    /* Bagian Kiri */
    .login-left { background: linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%); padding: 60px 40px; display: flex; flex-direction: column; justify-content: center; align-items: center; color: white; text-align: center; }
    .login-illustration { width: 100%; max-width: 280px; margin-bottom: 30px; }
    .login-left h1 { font-size: 36px; font-weight: 700; margin-bottom: 10px; }
    .login-left p { font-size: 16px; opacity: 0.9; }

    /* Bagian Kanan */
    .login-right { padding: 60px 50px; display: flex; flex-direction: column; justify-content: center; }
    .login-header { margin-bottom: 30px; }
    .login-header h2 { font-size: 32px; font-weight: 700; color: #1e293b; margin-bottom: 8px; }
    .login-header p { font-size: 16px; color: #64748b; }

    .form-group { margin-bottom: 20px; }
    .form-label { display: block; font-size: 14px; font-weight: 600; color: #1e293b; margin-bottom: 8px; }
    .form-input { width: 100%; padding: 14px 16px; font-size: 15px; border: 2px solid #e2e8f0; border-radius: 10px; font-family: 'Poppins', sans-serif; transition: all 0.3s ease; }
    .form-input:focus { outline: none; border-color: #1e40af; box-shadow: 0 0 0 4px rgba(30, 64, 175, 0.1); }
    
    .btn-login { width: 100%; padding: 16px; background: linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%); color: white; border: none; border-radius: 10px; font-size: 16px; font-weight: 600; cursor: pointer; transition: transform 0.3s ease; font-family: 'Poppins', sans-serif; box-shadow: 0 4px 12px rgba(30, 64, 175, 0.2); }
    .btn-login:hover { transform: translateY(-2px); }

    .auth-footer { margin-top: 30px; text-align: center; font-size: 14px; color: #64748b; }
    .auth-footer a { color: #1e40af; font-weight: 600; text-decoration: none; }
    .auth-footer a:hover { text-decoration: underline; }

    /* Responsif */
    @media (max-width: 900px) {
      .login-container { grid-template-columns: 1fr; max-width: 500px; }
      .login-left { display: none; }
      .login-right { padding: 40px 30px; }
    }
  </style>
</head>
<body>

<div class="login-page">
  <div class="login-container">
    
    <div class="login-left">
      <svg class="login-illustration" viewBox="0 0 400 400" xmlns="http://www.w3.org/2000/svg">
        <rect x="50" y="100" width="300" height="250" rx="10" fill="rgba(255,255,255,0.2)" />
        <rect x="50" y="100" width="300" height="60" rx="10" fill="rgba(255,255,255,0.3)" />
        
        <rect x="120" y="180" width="160" height="120" rx="10" fill="rgba(255,255,255,0.9)" />
        <path d="M150,180 L150,140 A50,50 0 0,1 250,140 L250,180" fill="none" stroke="rgba(255,255,255,0.9)" stroke-width="15" />
        <circle cx="200" cy="240" r="15" fill="#f59e0b" />
        <path d="M200,240 L200,270" stroke="#f59e0b" stroke-width="8" stroke-linecap="round" />

        <text x="200" y="340" text-anchor="middle" fill="white" font-size="18" font-weight="bold" style="font-family: 'Poppins', sans-serif;">RECOVERY</text>
      </svg>
      <h1>Lupa Password?</h1>
      <p>Jangan khawatir. Reset password Anda<br>dengan mudah dan aman di sini.</p>
    </div>

    <div class="login-right">
      <div class="login-header">
        <h2>Reset Password</h2>
        <p>Masukkan email dan password baru Anda</p>
      </div>

      <?php 
      if (!empty($_GET['info'])) {
          $msg = htmlspecialchars($_GET['info']);
          // Styling alert sederhana
          $bg = (strpos(strtolower($msg), 'gagal') !== false || strpos(strtolower($msg), 'tidak') !== false) ? '#fef2f2' : '#ecfdf5';
          $color = (strpos(strtolower($msg), 'gagal') !== false || strpos(strtolower($msg), 'tidak') !== false) ? '#991b1b' : '#065f46';
          $border = (strpos(strtolower($msg), 'gagal') !== false || strpos(strtolower($msg), 'tidak') !== false) ? '#fecaca' : '#a7f3d0';
          
          echo '<div style="background:'.$bg.'; color:'.$color.'; border:1px solid '.$border.'; padding:12px; border-radius:8px; font-size:14px; margin-bottom:20px; text-align:center;">'.$msg.'</div>';
      } 
      ?>

      <form method="POST" action="forgot_proses.php">
        <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
        
        <div class="form-group">
          <label class="form-label">Email Terdaftar</label>
          <input type="email" name="email" class="form-input" placeholder="nama@email.com" required>
        </div>

        <div class="form-group">
          <label class="form-label">Password Baru</label>
          <input type="password" name="password" class="form-input" placeholder="Minimal 8 karakter" minlength="8" required>
        </div>

        <button type="submit" class="btn-login">Reset Password</button>
      </form>

      <div class="auth-footer">
        Ingat password Anda? <a href="login.php">Kembali Login</a>
      </div>
    </div>

  </div>
</div>

</body>
</html>