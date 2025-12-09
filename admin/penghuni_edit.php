<?php
// [OOP: Session]
session_start();
require '../inc/koneksi.php';
require '../inc/guard.php';

// [Security] Hanya Admin
if (!is_admin()) {
    die('Forbidden');
}

$id = intval($_GET['id']);

// [Database: Join Query]
// Mengambil data penghuni beserta detail user (nama, hp, email) dalam satu query
$stmt = $mysqli->prepare("SELECT p.*, u.nama, u.email, u.no_hp FROM penghuni p JOIN pengguna u ON p.id_pengguna=u.id_pengguna WHERE p.id_penghuni=?");
$stmt->bind_param('i', $id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();

if (!$row) die("Data tidak ditemukan");
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <title>Edit Data Penghuni</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/app.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="dashboard-body">

    <button class="sidebar-toggle" onclick="toggleSidebar()">☰</button>
    <?php include '../components/sidebar_admin.php'; ?>

    <main class="main-content">
        <div class="flex justify-between items-center mb-6">
            <h1 class="font-bold text-xl text-slate-800">Edit Penghuni: <?= htmlspecialchars($row['nama']) ?></h1>
            <a href="penghuni_data.php" class="btn btn-secondary text-sm"><i class="fa-solid fa-arrow-left mr-2"></i> Kembali</a>
        </div>

        <div class="card-white max-w-3xl">
            <form method="post" action="penghuni_proses.php?act=edit">
                <input type="hidden" name="id" value="<?= $id ?>">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="form-label">Nama Lengkap</label>
                        <!-- [UX] Readonly: Nama login user tidak bisa diedit sembarangan di sini -->
                        <input type="text" value="<?= htmlspecialchars($row['nama']) ?>" class="form-input w-full bg-slate-100" readonly disabled>
                    </div>
                    <div>
                        <label class="form-label">Kontak (HP)</label>
                        <!-- [UX] Readonly: Kontak utama akun -->
                        <input type="text" value="<?= htmlspecialchars($row['no_hp']) ?>" class="form-input w-full bg-slate-100" readonly disabled>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label">Alamat Asal</label>
                    <!-- Textarea untuk alamat yang mungkin panjang -->
                    <textarea name="alamat" class="form-input w-full" rows="3"><?= htmlspecialchars($row['alamat']); ?></textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="form-label">Pekerjaan / Status</label>
                        <input type="text" name="pekerjaan" value="<?= htmlspecialchars($row['pekerjaan']); ?>" class="form-input w-full">
                    </div>
                    <div>
                        <label class="form-label">Kontak Darurat (Emergency)</label>
                        <input type="text" name="emergency_cp" value="<?= htmlspecialchars($row['emergency_cp']); ?>" class="form-input w-full" placeholder="Nama - No HP">
                    </div>
                </div>

                <div class="flex justify-end border-t pt-4">
                    <button type="submit" class="btn btn-primary px-6">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </main>
</body>

</html>