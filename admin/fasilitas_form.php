<?php
session_start();
require '../inc/koneksi.php';
require '../inc/guard.php';
if (!is_admin()) { die('Forbidden'); }

$id = intval($_GET['id'] ?? 0);
$data = ['nama_fasilitas' => '', 'icon' => 'fa-star']; // Default value
$title = "Tambah Fasilitas";
$act = "tambah";

// Jika Edit, ambil data lama
if ($id > 0) {
    $stmt = $mysqli->prepare("SELECT * FROM fasilitas_master WHERE id_fasilitas = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    if ($res) {
        $data = $res;
        $title = "Edit Fasilitas";
        $act = "edit";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <title><?= $title ?> - SIKOS Admin</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="../assets/css/app.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="dashboard-body">

  <?php include '../components/sidebar_admin.php'; ?>

  <main class="main-content">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-slate-800"><?= $title ?></h1>
        <a href="fasilitas_data.php" class="btn-secondary">Kembali</a>
    </div>

    <div class="card-white max-w-lg">
        <form action="fasilitas_proses.php?act=<?= $act ?>" method="post">
            <input type="hidden" name="id_fasilitas" value="<?= $id ?>">

            <div class="mb-4">
                <label class="form-label">Nama Fasilitas</label>
                <input type="text" name="nama" value="<?= htmlspecialchars($data['nama_fasilitas']) ?>" 
                       class="form-input" placeholder="Contoh: Smart TV, Kulkas, dll" required>
            </div>

            <div class="mb-6">
                <label class="form-label">Icon FontAwesome (Tanpa 'fa-solid')</label>
                <div class="flex gap-2">
                    <span class="p-3 bg-slate-100 border rounded flex items-center justify-center w-12">
                        <i class="fa-solid fa-icons"></i>
                    </span>
                    <input type="text" name="icon" value="<?= htmlspecialchars($data['icon']) ?>" 
                           class="form-input" placeholder="Contoh: fa-tv, fa-snowflake" required>
                </div>
                <p class="text-xs text-slate-500 mt-2">
                    Cari nama icon di <a href="https://fontawesome.com/search?o=r&m=free" target="_blank" class="text-blue-600 underline">FontAwesome Free</a>. 
                    Copy nama class-nya, misal: <code>fa-wifi</code>.
                </p>
            </div>

            <button type="submit" class="btn-primary w-full">💾 Simpan Data</button>
        </form>
    </div>
  </main>
</body>
</html>