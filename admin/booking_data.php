<?php
// [OOP: Session] Memulai sesi pengguna
session_start();

// [OOP: Modularization] Load class database dan fungsi security
require '../inc/koneksi.php';
require '../inc/guard.php';

// [Security: RBAC] Cek apakah user adalah ADMIN atau OWNER
if (!is_admin() && !is_owner()) die('Forbidden');

// ==========================================================================
// BAGIAN 1: LOGIKA PAGINATION (HALAMAN)
// ==========================================================================
// Menentukan jumlah data yang tampil per halaman
$batas = 10;
// Menentukan halaman aktif saat ini dari URL, default halaman 1
$halaman = isset($_GET['halaman']) ? (int)$_GET['halaman'] : 1;
// Mencegah halaman bernilai negatif atau 0
if ($halaman < 1) $halaman = 1;
// Menghitung offset (mulai data ke berapa?) untuk query SQL
$halaman_awal = ($halaman > 1) ? ($halaman * $batas) - $batas : 0;

// [OOP: Method Call] Mengambil Total Data Booking untuk menghitung jumlah halaman
// Menggunakan method get_total_booking() dari Class Database
$total_data = $db->get_total_booking();
// Menghitung total halaman (Total Data dibagi Batas per halaman, dibulatkan ke atas)
$total_halaman = $total_data > 0 ? ceil($total_data / $batas) : 1;

// [OOP: Method Call] Mengambil Data Booking sesuai halaman aktif (Limit & Offset)
// Data ini sudah berisi join table pengguna, kamar, dan status pembayaran dari method di Class Database
$res = $db->get_all_booking_paginated($halaman_awal, $batas);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <title>Data Booking</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/app.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="../assets/js/main.js"></script>
</head>

<body class="dashboard-body">

    <!-- Tombol Toggle Sidebar Mobile -->
    <button class="sidebar-toggle" onclick="toggleSidebar()">☰</button>

    <!-- Memuat Sidebar Admin -->
    <?php include '../components/sidebar_admin.php'; ?>

    <main class="main-content animate-fade-up">
        <div class="mb-8">
            <h1 class="font-bold text-xl">Data Booking</h1>
        </div>

        <!-- GRID CARD BOOKING (Responsive Grid System) -->
        <div class="grid-stats" style="grid-template-columns: 1fr; gap: 20px;">
            <?php
            // Check apakah ada data booking?
            if ($res->num_rows > 0) {
                // [Concepts: Looping] Iterasi setiap baris data booking
                while ($row = $res->fetch_assoc()) {
                    // Logika pewarnaan badge status
                    $statusBg = 'background:#f3f4f6; color:#4b5563;';
                    if ($row['status'] == 'PENDING') $statusBg = 'background:#fef3c7; color:#d97706;'; // Kuning
                    if ($row['status'] == 'SELESAI') $statusBg = 'background:#dcfce7; color:#166534;'; // Hijau
                    if ($row['status'] == 'BATAL') $statusBg = 'background:#fee2e2; color:#dc2626;';   // Merah
            ?>
                    <!-- COMPONENT: KARTU DATA BOOKING -->
                    <div class="card-white">
                        <!-- Header Kartu: Nama & Badge Status -->
                        <div class="flex justify-between items-center mb-4">
                            <div>
                                <h4 class="font-bold text-lg"><?= htmlspecialchars($row['nama']) ?></h4>
                                <div class="text-xs" style="color:var(--text-muted);"><?= date('d M Y H:i', strtotime($row['tanggal_booking'])) ?></div>
                            </div>
                            <span style="<?= $statusBg ?> padding:4px 10px; border-radius:6px; font-size:11px; font-weight:700;"><?= $row['status'] ?></span>
                        </div>

                        <!-- Detail Informasi (Grid 2 Kolom) -->
                        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:16px; font-size:13px; color:var(--text-muted); margin-bottom:20px; border-top:1px solid var(--border); border-bottom:1px solid var(--border); padding-top:12px; padding-bottom:12px;">
                            <div>
                                <span class="font-bold block text-xs uppercase mb-1">Kamar</span>
                                <span style="color:var(--text-main);"><?= htmlspecialchars($row['kode_kamar']) ?></span>
                            </div>
                            <div>
                                <span class="font-bold block text-xs uppercase mb-1">Durasi</span>
                                <span style="color:var(--text-main);"><?= htmlspecialchars($row['durasi_bulan_rencana']) ?> Bulan</span>
                            </div>
                            <div>
                                <span class="font-bold block text-xs uppercase mb-1">Kontak</span>
                                <span style="color:var(--text-main);"><?= htmlspecialchars($row['no_hp']) ?></span>
                            </div>
                            <div>
                                <span class="font-bold block text-xs uppercase mb-1">KTP</span>
                                <!-- Link Lihat KTP jika ada file path-nya -->
                                <?php if (!empty($row['ktp_path_opt'])): ?>
                                    <a href="../assets/uploads/ktp/<?= htmlspecialchars($row['ktp_path_opt']) ?>" target="_blank" style="color:var(--primary);">Lihat</a>
                                <?php else: ?> - <?php endif; ?>
                            </div>
                        </div>

                        <!-- Bagian Action Buttons (Tombol Aksi) -->
                        <?php if ($row['status'] == 'PENDING'): ?>
                            <div class="flex gap-2">
                                <!-- Tombol APPROVE: Mengirim parameter act=approve ke booking_proses.php -->
                                <a href="booking_proses.php?act=approve&id=<?= htmlspecialchars($row['id_booking']) ?>" class="btn btn-primary w-full text-center" onclick="konfirmasiAksi(event, 'Terima permintaan booking ini? Calon penyewa akan menjadi Penghuni Aktif.', this.href)">✓ Terima</a>

                                <!-- Tombol REJECT: Mengirim parameter act=reject ke booking_proses.php -->
                                <a href="booking_proses.php?act=reject&id=<?= htmlspecialchars($row['id_booking']) ?>" class="btn btn-danger w-full text-center" onclick="konfirmasiAksi(event, 'Tolak permintaan booking ini?', this.href)">✕ Tolak</a>
                            </div>
                        <?php else: ?>
                            <!-- Jika bukan PENDING, tampilkan pesan statis -->
                            <div class="text-center text-xs italic" style="color:var(--text-muted);">Booking selesai diproses.</div>
                        <?php endif; ?>
                    </div>
            <?php
                }
            } else {
                echo "<p class='text-center p-8' style='color:var(--text-muted);'>Belum ada data booking.</p>";
            }
            ?>
        </div>

        <!-- UI PAGINATION -->
        <?php
        // Pastikan total halaman minimal 1
        $total_halaman = max(1, (int)$total_halaman);
        $prev = max(1, $halaman - 1);
        $next = min($total_halaman, $halaman + 1);
        ?>
        <div class="pagination-container" style="margin-top: 20px; display:flex; gap:5px; justify-content:center;">
            <?php
            // Membangun Query String untuk link Prev/Next agar filter lain tidak hilang
            $qs = $_GET;
            $qs['halaman'] = $prev;
            $href_prev = ($halaman > 1) ? '?' . http_build_query($qs) : '#';

            $qs['halaman'] = $next;
            $href_next = ($halaman < $total_halaman) ? '?' . http_build_query($qs) : '#';
            ?>

            <!-- Tombol Previous -->
            <a href="<?= $href_prev ?>"
                class="btn btn-secondary text-xs <?= ($halaman <= 1) ? 'disabled' : '' ?>"
                style="padding:6px 12px;">
                <i class="fa-solid fa-chevron-left"></i> Prev
            </a>

            <!-- Looping Nomor Halaman 1, 2, 3... dst -->
            <?php for ($x = 1; $x <= $total_halaman; $x++):
                $qs = $_GET;
                $qs['halaman'] = $x;
                $href_page = '?' . http_build_query($qs);
            ?>
                <a href="<?= $href_page ?>"
                    class="btn text-xs <?= ($halaman == $x) ? 'btn-primary' : 'btn-secondary' ?>"
                    style="padding:6px 12px;"><?= $x ?></a>
            <?php endfor; ?>

            <!-- Tombol Next -->
            <a href="<?= $href_next ?>"
                class="btn btn-secondary text-xs <?= ($halaman >= $total_halaman) ? 'disabled' : '' ?>"
                style="padding:6px 12px;">
                Next <i class="fa-solid fa-chevron-right"></i>
            </a>
        </div>
    </main>
</body>

</html>