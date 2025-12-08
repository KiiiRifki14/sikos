<?php
session_start();
require 'inc/koneksi.php';

if (!isset($_SESSION['id_pengguna']) || $_SESSION['peran'] != 'PENGHUNI') {
    header('Location: login.php');
    exit;
}

$id_pengguna = $_SESSION['id_pengguna'];
// Pastikan $mysqli tersedia
$db = new Database();
$mysqli = $db->koneksi;

// [REFACTOR] Gunakan Method Database
// Ambil Nama User (Optional jika sudah ada di session)
// $user = $db->get_user_by_id($id_pengguna); 

// Cek ID Penghuni
$id_penghuni = $db->get_id_penghuni_by_user($id_pengguna);

$row_kamar = null; // Default null

// Hanya jalankan query kontrak jika id_penghuni ada
if ($id_penghuni > 0) {
    $row_kamar = $db->get_kamar_penghuni_detail($id_penghuni);
}

$fasilitas = [];
if ($row_kamar) {
    $id_kamar = $row_kamar['id_kamar'];
    $fasilitas = $db->get_fasilitas_kamar($id_kamar);
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Kamar Saya</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link rel="stylesheet" href="assets/css/app.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="role-penghuni">
    <?php include 'components/sidebar_penghuni.php'; ?>

    <main class="main-content animate-fade-up">

        <?php if ($row_kamar): ?>

            <div class="card-white" style="overflow:hidden; padding:0; border-radius: 20px;">
                <div style="display: grid; grid-template-columns: 1fr 1.5fr; min-height: 400px;" class="grid-responsive-room">

                    <!-- ROOM IMAGE SECTION -->
                    <div style="background:#f1f5f9; position:relative; min-height:300px;">
                        <?php if (!empty($row_kamar['foto_cover'])): ?>
                            <img src="assets/uploads/kamar/<?= htmlspecialchars($row_kamar['foto_cover']) ?>" style="width:100%; height:100%; object-fit:cover; display:block;">
                        <?php else: ?>
                            <div style="height:100%; display:flex; flex-direction:column; align-items:center; justify-content:center; color:#94a3b8;">
                                <i class="fa-regular fa-image" style="font-size:64px; margin-bottom:15px; opacity:0.5;"></i>
                                <span class="text-sm font-bold uppercase">No Image</span>
                            </div>
                        <?php endif; ?>

                        <div style="position: absolute; bottom: 20px; left: 20px; background: rgba(0,0,0,0.7); color: white; padding: 8px 16px; border-radius: 30px; font-size: 13px; font-weight: 700; backdrop-filter: blur(5px);">
                            <i class="fa-solid fa-tag mr-2"></i> <?= htmlspecialchars($row_kamar['nama_tipe']) ?>
                        </div>
                    </div>

                    <!-- ROOM INFO SECTION -->
                    <div style="padding: 40px;">
                        <div class="flex justify-between items-start mb-8 pb-6 border-b border-gray-100">
                            <div>
                                <span class="text-xs font-bold text-primary tracking-widest uppercase mb-1 block">Kode Kamar</span>
                                <h2 style="font-size: 42px; font-weight: 800; color: var(--text-main); line-height: 1; letter-spacing: -1px;"><?= htmlspecialchars($row_kamar['kode_kamar']) ?></h2>
                            </div>
                            <div class="text-right">
                                <span class="block text-xs font-bold text-muted uppercase tracking-wider mb-1">Biaya Sewa</span>
                                <div class="text-2xl font-bold text-primary">Rp <?= number_format($row_kamar['harga']) ?></div>
                                <span class="text-xs text-muted">/ bulan</span>
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; margin-bottom: 30px;">
                            <div class="bg-slate-50 p-4 rounded-xl border border-slate-100">
                                <div class="text-xs font-bold text-muted uppercase mb-1">Mulai Sewa</div>
                                <div class="font-bold text-main"><?= date('d F Y', strtotime($row_kamar['tanggal_mulai'])) ?></div>
                            </div>
                            <div class="bg-slate-50 p-4 rounded-xl border border-slate-100">
                                <div class="text-xs font-bold text-muted uppercase mb-1">Berakhir</div>
                                <div class="font-bold text-main"><?= date('d F Y', strtotime($row_kamar['tanggal_selesai'])) ?></div>
                            </div>
                            <div class="bg-slate-50 p-4 rounded-xl border border-slate-100">
                                <div class="text-xs font-bold text-muted uppercase mb-1">Lantai</div>
                                <div class="font-bold text-main"><?= htmlspecialchars($row_kamar['lantai']) ?></div>
                            </div>
                            <div class="bg-slate-50 p-4 rounded-xl border border-slate-100">
                                <div class="text-xs font-bold text-muted uppercase mb-1">Luas Kamar</div>
                                <div class="font-bold text-main"><?= htmlspecialchars($row_kamar['luas_m2']) ?> m²</div>
                            </div>
                        </div>

                        <div class="mb-6">
                            <div class="text-sm font-bold text-main mb-3 uppercase tracking-wide">Fasilitas Termasuk</div>
                            <div class="flex flex-wrap gap-3">
                                <?php if (!empty($fasilitas)): foreach ($fasilitas as $f): ?>
                                        <span style="background: #e0f2fe; color: #0369a1; padding: 8px 16px; border-radius: 20px; font-size: 13px; font-weight: 600; display: flex; align-items: center; gap: 8px;">
                                            <i class="fa-solid <?= $f['icon'] ?>"></i> <?= htmlspecialchars($f['nama_fasilitas']) ?>
                                        </span>
                                    <?php endforeach;
                                else: ?>
                                    <span class="text-sm text-muted">-</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <?php if (!empty($row_kamar['catatan'])): ?>
                            <div style="background:#fff7ed; border:1px solid #ffedd5; padding:20px; border-radius:16px;">
                                <strong style="display:block; font-size:12px; color:#9a3412; margin-bottom:5px; text-transform:uppercase;">Catatan Tambahan</strong>
                                <p style="font-size:14px; color:#c2410c; margin:0; line-height:1.6;"><?= htmlspecialchars($row_kamar['catatan']) ?></p>
                            </div>
                        <?php endif; ?>

                    </div>
                </div>
            </div>

        <?php else: ?>

            <?php
            // Cek apakah ada booking pending
            $stmt_booking = $mysqli->prepare("SELECT b.*, k.kode_kamar, k.foto_cover, t.nama_tipe, k.harga, p.status as status_bayar, p.bukti_path 
                                              FROM booking b 
                                              JOIN kamar k ON b.id_kamar = k.id_kamar
                                              JOIN tipe_kamar t ON k.id_tipe = t.id_tipe
                                              LEFT JOIN pembayaran p ON b.id_booking = p.ref_id AND p.ref_type='BOOKING'
                                              WHERE b.id_pengguna = ? AND b.status = 'PENDING'");
            $stmt_booking->bind_param('i', $id_pengguna);
            $stmt_booking->execute();
            $res_booking = $stmt_booking->get_result();
            $row_booking = $res_booking->fetch_assoc();
            ?>

            <?php if ($row_booking): ?>

                <?php if (!empty($row_booking['bukti_path'])): ?>
                    <!-- STATE: SUDAH UPLOAD -->
                    <div class="card-white" style="text-align:center; padding:60px 20px; display:flex; flex-direction:column; align-items:center;">
                        <div style="width:100px; height:100px; background:#e0f2fe; border-radius:50%; display:flex; align-items:center; justify-content:center; margin-bottom:24px;">
                            <i class="fa-solid fa-hourglass-half" style="font-size:40px; color:#0284c7;"></i>
                        </div>

                        <h3 style="color:#1e293b; font-size:22px; font-weight:800; margin-bottom:8px;">
                            Menunggu Verifikasi Admin
                        </h3>
                        <p style="color:#64748b; margin-bottom: 30px; max-width:500px;">
                            Terima kasih! Bukti pembayaran Anda sedang diperiksa oleh admin. admin akan segera memproses.
                        </p>

                        <div class="bg-slate-50 border p-4 rounded-lg mb-8 text-left w-full max-w-md">
                            <div class="flex justify-between mb-2">
                                <span class="text-xs text-muted font-bold uppercase">Kamar</span>
                                <span class="text-sm font-bold"><?= htmlspecialchars($row_booking['kode_kamar']) ?></span>
                            </div>
                            <div class="flex justify-between mb-2">
                                <span class="text-xs text-muted font-bold uppercase">Status</span>
                                <span class="text-sm font-bold text-blue-600">Verifikasi Pembayaran</span>
                            </div>
                        </div>

                        <div class="flex gap-4 flex-wrap justify-center">
                            <a href="https://wa.me/6281234567890" target="_blank" class="btn btn-secondary px-6 py-2 rounded-full text-sm">
                                <i class="fa-brands fa-whatsapp mr-2"></i> Hubungi Admin
                            </a>
                        </div>
                    </div>

                <?php else: ?>
                    <!-- STATE: BELUM UPLOAD -->
                    <div class="card-white" style="text-align:center; padding:60px 20px; display:flex; flex-direction:column; align-items:center;">
                        <div style="width:100px; height:100px; background:#fff7ed; border-radius:50%; display:flex; align-items:center; justify-content:center; margin-bottom:24px;">
                            <i class="fa-solid fa-clock" style="font-size:40px; color:#f97316;"></i>
                        </div>

                        <h3 style="color:#1e293b; font-size:22px; font-weight:800; margin-bottom:8px;">
                            Selesaikan Pembayaran Anda
                        </h3>
                        <p style="color:#64748b; margin-bottom: 30px; max-width:500px;">
                            Anda telah memesan <strong>Kamar <?= htmlspecialchars($row_booking['kode_kamar']) ?></strong>.
                            <br>Silakan selesaikan pembayaran booking fee sebesar <strong>Rp 100.000</strong>.
                        </p>

                        <div class="bg-slate-50 border p-4 rounded-lg mb-8 text-left w-full max-w-md">
                            <div class="flex justify-between mb-2">
                                <span class="text-xs text-muted font-bold uppercase">Total Biaya</span>
                                <span class="text-sm font-bold text-primary">Rp 100.000</span>
                            </div>
                        </div>

                        <div class="flex gap-4 flex-wrap justify-center">
                            <a href="pembayaran.php?booking=<?= $row_booking['id_booking'] ?>" class="btn btn-primary px-8 py-3 rounded-full">
                                <i class="fa-solid fa-wallet mr-2"></i> Bayar Sekarang
                            </a>
                        </div>
                    </div>
                <?php endif; ?>

            <?php else: ?>
                <!-- TAMPILAN KOSONG -->
                <div class="card-white" style="text-align:center; padding:80px 20px; display:flex; flex-direction:column; align-items:center;">
                    <div style="width:120px; height:120px; background:#f1f5f9; border-radius:50%; display:flex; align-items:center; justify-content:center; margin-bottom:30px;">
                        <i class="fa-solid fa-bed" style="font-size:50px; color:#94a3b8;"></i>
                    </div>
                    <h3 style="color:#1e293b; font-size:24px; font-weight:800; margin-bottom:10px;">
                        Anda belum menyewa kamar
                    </h3>
                    <p style="color:#64748b; font-size:16px; margin-bottom:40px; max-width:400px; line-height:1.6;">
                        Sepertinya Anda belum memiliki kamar yang aktif saat ini. Yuk cari kamar idamanmu sekarang!
                    </p>
                    <a href="index.php#kamar" class="btn btn-primary" style="padding: 16px 40px; font-size: 16px; border-radius: 50px; box-shadow: 0 10px 20px -5px rgba(79, 70, 229, 0.4);">
                        <i class="fa-solid fa-magnifying-glass mr-2"></i> Cari Kamar Sekarang
                    </a>
                </div>
            <?php endif; ?>

        <?php endif; ?>

    </main>
</body>

</html>