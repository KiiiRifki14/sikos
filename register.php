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
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Daftar Akun - SIKOS</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/app.css" />
  <style>
    /* Menggunakan style yang sama dengan login untuk konsistensi */
    body {
      font-family: 'Poppins', sans-serif;
      background: #f8fafc;
    }

    .login-page {
      display: flex;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
      padding: 40px 20px;
      background: linear-gradient(135deg, #f8fafc 0%, #e0e7ff 100%);
    }

    .login-container {
      display: grid;
      grid-template-columns: 1fr 1fr;
      max-width: 1000px;
      width: 100%;
      background: white;
      border-radius: 24px;
      overflow: hidden;
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
    }

    .login-left {
      background: linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%);
      padding: 60px 40px;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      color: white;
      text-align: center;
    }

    .login-illustration {
      width: 100%;
      max-width: 280px;
      margin-bottom: 30px;
    }

    .login-left h1 {
      font-size: 36px;
      font-weight: 700;
      margin-bottom: 10px;
    }

    .login-left p {
      font-size: 16px;
      opacity: 0.9;
    }

    .login-right {
      padding: 60px 50px;
      display: flex;
      flex-direction: column;
      justify-content: center;
    }

    .login-header {
      margin-bottom: 30px;
    }

    .login-header h2 {
      font-size: 32px;
      font-weight: 700;
      color: #1e293b;
      margin-bottom: 8px;
    }

    .login-header p {
      font-size: 16px;
      color: #64748b;
    }

    .form-group {
      margin-bottom: 20px;
    }

    .form-label {
      display: block;
      font-size: 14px;
      font-weight: 600;
      color: #1e293b;
      margin-bottom: 8px;
    }

    .form-input {
      width: 100%;
      padding: 14px 16px;
      font-size: 15px;
      border: 2px solid #e2e8f0;
      border-radius: 10px;
      font-family: 'Poppins', sans-serif;
      transition: all 0.3s ease;
    }

    .form-input:focus {
      outline: none;
      border-color: #1e40af;
      box-shadow: 0 0 0 4px rgba(30, 64, 175, 0.1);
    }

    .btn-login {
      width: 100%;
      padding: 16px;
      background: linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%);
      color: white;
      border: none;
      border-radius: 10px;
      font-size: 16px;
      font-weight: 600;
      cursor: pointer;
      transition: transform 0.3s ease;
      font-family: 'Poppins', sans-serif;
      box-shadow: 0 4px 12px rgba(30, 64, 175, 0.2);
    }

    .btn-login:hover {
      transform: translateY(-2px);
    }

    .divider {
      position: relative;
      text-align: center;
      margin: 24px 0;
    }

    .divider::before {
      content: '';
      position: absolute;
      top: 50%;
      left: 0;
      right: 0;
      border-top: 1px solid #e2e8f0;
    }

    .divider span {
      position: relative;
      background: white;
      padding: 0 10px;
      color: #64748b;
      font-size: 12px;
      text-transform: uppercase;
      font-weight: 500;
    }

    .social-buttons {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 16px;
    }

    .btn-social {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      padding: 12px;
      border: 1px solid #e2e8f0;
      border-radius: 10px;
      background: white;
      color: #64748b;
      font-weight: 500;
      cursor: pointer;
      transition: all 0.2s;
    }

    .btn-social:hover {
      background: #f8fafc;
      border-color: #cbd5e1;
    }

    .btn-social img {
      width: 20px;
      height: 20px;
    }

    .auth-footer {
      margin-top: 30px;
      text-align: center;
      font-size: 14px;
      color: #64748b;
    }

    .auth-footer a {
      color: #1e40af;
      font-weight: 600;
      text-decoration: none;
    }

    .auth-footer a:hover {
      text-decoration: underline;
    }

    .grid-2 {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 20px;
    }

    @media (max-width: 900px) {
      .login-container {
        grid-template-columns: 1fr;
        max-width: 500px;
      }

      .login-left {
        display: none;
      }

      .login-right {
        padding: 40px 30px;
      }

      .grid-2 {
        grid-template-columns: 1fr;
        gap: 0;
      }
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
          <rect x="80" y="180" width="80" height="80" rx="5" fill="rgba(255,255,255,0.25)" />
          <rect x="180" y="180" width="80" height="80" rx="5" fill="rgba(255,255,255,0.25)" />
          <rect x="280" y="180" width="50" height="80" rx="5" fill="rgba(255,255,255,0.25)" />
          <rect x="80" y="280" width="80" height="50" rx="5" fill="rgba(255,255,255,0.25)" />
          <rect x="180" y="280" width="80" height="50" rx="5" fill="rgba(255,255,255,0.25)" />
          <circle cx="200" cy="80" r="25" fill="#f59e0b" />
          <rect x="140" y="320" width="120" height="30" rx="5" fill="#f59e0b" />
          <text x="200" y="340" text-anchor="middle" fill="white" font-size="18" font-weight="bold" style="font-family: 'Poppins', sans-serif;">KOST</text>
        </svg>
        <h1>Bergabunglah</h1>
        <p>Buat akun dan mulai sewa kamar kost.</p>
      </div>

      <div class="login-right">
        <div class="login-header">
          <h2>Buat Akun Baru</h2>
          <p>Isi data diri Anda untuk mendaftar</p>
        </div>

        <?php if (!empty($_GET['error'])): ?>
          <div style="background:#fef2f2; color:#991b1b; padding:12px; border-radius:8px; font-size:14px; margin-bottom:20px; border:1px solid #fecaca; text-align:center;">
            <?php
            if ($_GET['error'] == 'duplikat') echo "Email sudah terdaftar!";
            elseif ($_GET['error'] == 'invalid') echo "Data tidak valid atau format salah!";
            elseif ($_GET['error'] == 'weak_password') echo "Password lemah! minimal 8 karakter, huruf, dan angka.";
            else echo "Terjadi kesalahan sistem.";
            ?>
          </div>
        <?php endif; ?>

        <form method="POST" action="proses_register.php">
          <input type="hidden" name="csrf" value="<?= csrf_token() ?>">

          <div class="form-group">
            <label class="form-label">Nama Lengkap</label>
            <input type="text" name="nama" class="form-input" placeholder="Contoh: Budi Santoso" required>
          </div>

          <div class="grid-2">
            <div class="form-group">
              <label class="form-label">Email</label>
              <input type="email" name="email" class="form-input" placeholder="email@anda.com" required>
            </div>
            <div class="form-group">
              <label class="form-label">No. WhatsApp</label>
              <input type="text" name="no_hp" class="form-input" placeholder="0812..." required>
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-input" placeholder="Minimal 8 karakter" required>
          </div>

          <button type="submit" class="btn-login">Daftar Sekarang</button>
        </form>

        <div class="divider">
          <span>Atau daftar dengan</span>
        </div>

        <div class="social-buttons">
          <button type="button" onclick="alert('Fitur Google Login belum tersedia.')" class="btn-social">
            <img src="https://www.svgrepo.com/show/475656/google-color.svg" alt="Google">
            <span>Google</span>
          </button>
          <button type="button" onclick="alert('Fitur Facebook Login belum tersedia.')" class="btn-social">
            <img src="https://www.svgrepo.com/show/475647/facebook-color.svg" alt="Facebook">
            <span>Facebook</span>
          </button>
        </div>

        <div class="auth-footer">
          Sudah punya akun? <a href="login.php">Masuk Disini</a>
        </div>
      </div>

    </div>
  </div>

</body>

</html>