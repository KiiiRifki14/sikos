<?php
session_start();
require '../inc/koneksi.php';
require '../inc/guard.php';

// Validasi Admin
if (!is_admin() && !is_owner()) { 
    header("Location: ../login.php"); exit; 
}

$file_settings = __DIR__ . '/../inc/settings_data.json';

// DATA DEFAULT
$data = [
    'nama_kos'   => 'SIKOS PAADAASIH',
    'alamat'     => 'Jl. Paadaasih No. 123, Cimahi, Jawa Barat',
    'no_hp'      => '0812-3456-7890',
    'email'      => 'admin@sikos.com',
    'rek_bank'   => 'BCA 123456789 a.n Owner',
    'pemilik'    => 'Ibu Kost'
];

// LOAD DATA
if (file_exists($file_settings)) {
    $json = file_get_contents($file_settings);
    $data_saved = json_decode($json, true);
    if ($data_saved) $data = array_merge($data, $data_saved);
}

// PROSES SIMPAN
$pesan = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data['nama_kos'] = htmlspecialchars($_POST['nama_kos']);
    $data['alamat']   = htmlspecialchars($_POST['alamat']);
    $data['no_hp']    = htmlspecialchars($_POST['no_hp']);
    $data['email']    = htmlspecialchars($_POST['email']);
    $data['rek_bank'] = htmlspecialchars($_POST['rek_bank']);
    $data['pemilik']  = htmlspecialchars($_POST['pemilik']);

    if (file_put_contents($file_settings, json_encode($data, JSON_PRETTY_PRINT))) {
        $pesan = '<div style="background:#dcfce7; color:#166534; padding:12px; border-radius:8px; margin-bottom:20px; border:1px solid #bbf7d0;">✅ Pengaturan berhasil disimpan dan diterapkan ke seluruh sistem!</div>';
    } else {
        $pesan = '<div style="background:#fee2e2; color:#991b1b; padding:12px; border-radius:8px; margin-bottom:20px; border:1px solid #fecaca;">❌ Gagal menyimpan. Pastikan folder inc memiliki izin tulis (writable).</div>';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <title>Pengaturan Sistem</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="../assets/css/app.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <script src="../assets/js/main.js"></script>
  <style>
      /* Custom Grid untuk Form */
      .form-grid {
          display: grid;
          grid-template-columns: 1fr 1fr;
          gap: 20px;
          margin-bottom: 20px;
      }
      /* Responsif di HP jadi 1 kolom */
      @media (max-width: 768px) {
          .form-grid { grid-template-columns: 1fr; }
      }
  </style>
</head>
<body class="dashboard-body">

  <button class="sidebar-toggle" onclick="toggleSidebar()">☰</button>
  <?php include '../components/sidebar_admin.php'; ?>
  
  <main class="main-content">
    <div class="mb-8">
        <h1 class="font-bold text-xl">Pengaturan Sistem</h1>
    </div>
    
    <?= $pesan ?>

    <div class="card-white" style="max-width:800px;">
        <form method="post">
            <div class="form-grid">
                <div>
                    <label class="form-label">Nama Kost</label>
                    <input type="text" name="nama_kos" class="form-input" value="<?= $data['nama_kos'] ?>" required>
                </div>
                <div>
                    <label class="form-label">Nama Pemilik/Admin</label>
                    <input type="text" name="pemilik" class="form-input" value="<?= $data['pemilik'] ?>" required>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label">Alamat Lengkap (Muncul di Kuitansi)</label>
                <textarea name="alamat" class="form-input" rows="2" required><?= $data['alamat'] ?></textarea>
            </div>

            <div class="form-grid">
                <div>
                    <label class="form-label">Nomor WhatsApp Pengelola</label>
                    <input type="text" name="no_hp" class="form-input" value="<?= $data['no_hp'] ?>" required>
                </div>
                <div>
                    <label class="form-label">Email Support</label>
                    <input type="email" name="email" class="form-input" value="<?= $data['email'] ?>">
                </div>
            </div>

            <div class="mb-8">
                <label class="form-label">Info Rekening Bank (Muncul di Halaman Bayar)</label>
                <input type="text" name="rek_bank" class="form-input" value="<?= $data['rek_bank'] ?>" placeholder="Contoh: BCA 123456 a.n Budi" required>
            </div>

            <div style="display:flex; justify-content:flex-end; border-top:1px solid var(--border); padding-top:20px;">
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-save mr-2"></i> Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
  </main>
</body>
</html>

