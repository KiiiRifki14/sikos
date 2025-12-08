<?php
session_start();
require 'inc/koneksi.php';
require 'inc/csrf.php';

if (!isset($_SESSION['id_pengguna'])) {
    header('Location: login.php?info=Login dulu sebelum booking!');
    exit;
}
$id_kamar = intval($_GET['id_kamar'] ?? 0);
// KODE BARU (Sudah diperbaiki)
if ($id_kamar <= 0) pesan_error("index.php", "Kamar tidak ditemukan atau URL salah!");

// 1. Ambil detail kamar utama
$stmt = $mysqli->prepare("SELECT k.*, t.nama_tipe FROM kamar k JOIN tipe_kamar t ON k.id_tipe=t.id_tipe WHERE k.id_kamar=?");
$stmt->bind_param('i', $id_kamar);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();

if (!$row) pesan_error("index.php", "Maaf, data kamar tersebut tidak ditemukan.");

?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Booking Kamar <?= htmlspecialchars($row['kode_kamar']) ?></title>
  <link rel="stylesheet" href="assets/css/app.css"/>
</head>
<body style="background-color: #f8fafc;">

    <!-- Navbar Sederhana -->
    <nav class="navbar" style="position: sticky; top: 0;">
        <div class="navbar-container">
            <a href="index.php" class="nav-brand">
                <div class="nav-brand-logo">K</div>
                <span style="font-weight: 700; font-size: 18px;">KOS PAADAASIH</span>
            </a>
            <div class="nav-menu">
                <a href="index.php" class="nav-link">Beranda</a>
                <a href="profil.php" class="nav-link">Profil</a>
            </div>
        </div>
    </nav>

    <div class="container" style="max-width: 800px; margin: 40px auto; padding: 20px;">
        
        <!-- Header Section -->
        <div class="text-center mb-8">
            <h1 style="font-size: 28px; font-weight: 800; color: #1e293b; margin-bottom: 8px;">Form Booking Kamar</h1>
            <p style="color: #64748b;">Lengkapi data di bawah ini untuk mengajukan sewa.</p>
        </div>

        <div class="card-white" style="padding: 40px; border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.05);">
            
            <!-- Info Kamar -->
            <div style="background: #f1f5f9; padding: 20px; border-radius: 12px; margin-bottom: 30px; display: flex; align-items: center; gap: 20px; flex-wrap: wrap;">
                <div style="width: 60px; height: 60px; background: #cbd5e1; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 24px; color: white;">
                    <i class="fa-solid fa-bed"></i>
                </div>
                <div>
                    <h3 style="font-size: 18px; font-weight: 700; color: #334155; margin-bottom: 4px;">
                        Kamar <?= htmlspecialchars($row['kode_kamar']) ?> 
                        <span style="background: #e0e7ff; color: #4338ca; font-size: 11px; padding: 4px 8px; border-radius: 20px; vertical-align: middle; margin-left: 8px;">
                            <?= htmlspecialchars($row['nama_tipe'] ?? 'Standard') ?>
                        </span>
                    </h3>
                    <div style="color: #64748b; font-size: 14px;">
                        Harga: <span style="font-weight: 700; color: #1e293b;">Rp <?= number_format($row['harga'], 0, ',', '.') ?></span> / bulan
                    </div>
                </div>
            </div>

            <form method="POST" action="proses_booking.php" enctype="multipart/form-data" class="space-y-4" novalidate>
                <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
                <input type="hidden" name="id_kamar" value="<?= $id_kamar ?>">

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 24px; margin-bottom: 24px;">
                    <!-- Tanggal Check-in -->
                    <div>
                        <label for="checkin_rencana" class="form-label">Rencana Masuk (Check-in)</label>
                        <div style="position: relative;">
                            <input id="checkin_rencana" type="date" name="checkin_rencana" required class="form-input" style="padding-left: 42px;">
                            <i class="fa-regular fa-calendar" style="position: absolute; left: 14px; top: 12px; color: #94a3b8;"></i>
                        </div>
                    </div>

                    <!-- Durasi -->
                    <div>
                        <label for="durasi_bulan_rencana" class="form-label">Durasi Sewa</label>
                        <div style="position: relative;">
                            <input id="durasi_bulan_rencana" type="number" name="durasi_bulan_rencana" min="1" max="36" value="12" required 
                                   oninput="this.value = !!this.value && Math.abs(this.value) >= 1 ? Math.abs(this.value) : 1; if(this.value > 36) this.value = 36;" 
                                   class="form-input" style="padding-left: 42px;">
                            <span style="position: absolute; left: 14px; top: 12px; color: #94a3b8; font-size: 12px; font-weight: bold;">BLN</span>
                        </div>
                        <p style="font-size: 12px; color: #94a3b8; margin-top: 6px;">Minimal 1 bulan, Maksimal 36 bulan.</p>
                    </div>
                </div>

                <!-- Upload KTP -->
                <div class="mb-8">
                    <label for="ktp_opt" class="form-label">Upload Foto KTP</label>
                    <div style="border: 2px dashed #cbd5e1; padding: 30px; border-radius: 12px; text-align: center; background: #fafafa; transition: 0.2s;" ondragover="this.style.borderColor='#2563eb'; this.style.background='#eff6ff';" ondragleave="this.style.borderColor='#cbd5e1'; this.style.background='#fafafa';">
                        <i class="fa-solid fa-cloud-arrow-up" style="font-size: 32px; color: #cbd5e1; margin-bottom: 10px;"></i>
                        <p style="font-size: 14px; color: #64748b; margin-bottom: 10px;">Klik untuk pilih file atau tarik foto ke sini</p>
                        <input id="ktp_opt" type="file" name="ktp_opt" accept="image/jpeg,image/png,image/webp" required 
                               class="form-input" style="max-width: 250px; margin: 0 auto; display: block;">
                        <p class="text-xs text-slate-400 mt-2">Format: JPG, PNG, WEBP. Maks 2MB.</p>
                    </div>
                </div>

                <!-- Buttons -->
                <div style="display: flex; gap: 16px; margin-top: 40px;">
                    <a href="index.php" class="btn btn-secondary" style="flex: 1; justify-content: center;">Batal</a>
                    <button type="submit" class="btn btn-primary" style="flex: 2; height: 48px; font-size: 16px;">
                        Ajukan Booking <i class="fa-solid fa-arrow-right"></i>
                    </button>
                </div>

            </form>
        </div>

        <div class="text-center mt-4">
            <p style="font-size: 13px; color: #94a3b8;">
                <i class="fa-solid fa-shield-halved"></i> Data Anda aman dan terenkripsi.
            </p>
        </div>
    </div>
    
    <!-- Font Awesome (Jika belum ada di head, kita pastikan ada) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</body>
</html>