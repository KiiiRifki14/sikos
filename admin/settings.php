<?php
session_start();
require '../inc/koneksi.php';
require '../inc/guard.php';

// Validasi Admin
if (!is_admin() && !is_owner()) { 
    header("Location: ../login.php"); exit; 
}

// 1. TENTUKAN LOKASI FILE PENYIMPANAN DATA
// Kita simpan di folder 'inc' agar aman
$file_settings = __DIR__ . '/../inc/settings_data.json';

// 2. DATA DEFAULT (Dipakai jika file belum ada)
$data = [
    'nama_kos'   => 'SIKOS PAADAASIH',
    'alamat'     => 'Jl. Paadaasih No. 123, Cimahi, Jawa Barat',
    'no_hp'      => '0812-3456-7890',
    'email'      => 'admin@sikos.com',
    'rek_bank'   => 'BCA 123456789 a.n Owner',
    'pemilik'    => 'Ibu Kost'
];

// 3. LOAD DATA LAMA (Jika ada)
if (file_exists($file_settings)) {
    $json = file_get_contents($file_settings);
    $data_saved = json_decode($json, true);
    if ($data_saved) $data = array_merge($data, $data_saved);
}

// 4. PROSES SIMPAN (Jika tombol ditekan)
$pesan = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Update data array dari input form
    $data['nama_kos'] = htmlspecialchars($_POST['nama_kos']);
    $data['alamat']   = htmlspecialchars($_POST['alamat']);
    $data['no_hp']    = htmlspecialchars($_POST['no_hp']);
    $data['email']    = htmlspecialchars($_POST['email']);
    $data['rek_bank'] = htmlspecialchars($_POST['rek_bank']);
    $data['pemilik']  = htmlspecialchars($_POST['pemilik']);

    // Simpan ke file JSON
    if (file_put_contents($file_settings, json_encode($data, JSON_PRETTY_PRINT))) {
        $pesan = '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">✅ Pengaturan berhasil disimpan dan diterapkan ke seluruh sistem!</div>';
    } else {
        $pesan = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">❌ Gagal menyimpan. Pastikan folder inc memiliki izin tulis (writable).</div>';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <title>Pengaturan Sistem</title>

  <link rel="stylesheet" href="../assets/css/app.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="dashboard-body">
  <?php include '../components/sidebar_admin.php'; ?>
  <main class="main-content">
     </main>
</body>
    <h1 style="font-size:24px; font-weight:700; color:#1e293b; margin-bottom:32px;">Pengaturan Sistem</h1>
    <?= $pesan ?>

    <div class="card-white" style="max-width:800px;">
        <form method="post">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="form-label">Nama Kost</label>
                    <input type="text" name="nama_kos" class="form-input" value="<?= $data['nama_kos'] ?>" required>
                </div>
                <div>
                    <label class="form-label">Nama Pemilik/Admin</label>
                    <input type="text" name="pemilik" class="form-input" value="<?= $data['pemilik'] ?>" required>
                </div>
            </div>

            <div class="mb-6">
                <label class="form-label">Alamat Lengkap (Muncul di Kuitansi)</label>
                <textarea name="alamat" class="form-input" rows="2" required><?= $data['alamat'] ?></textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
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

            <div class="flex justify-end border-t pt-4">
                <button type="submit" class="btn-primary px-6">
                    <i class="fa-solid fa-save mr-2"></i> Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
  </main>
</body>
</html>