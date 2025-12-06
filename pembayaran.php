<?php
session_start();
require 'inc/koneksi.php';
require 'inc/csrf.php';

$id_booking = intval($_GET['booking'] ?? 0);
if ($id_booking < 1) die("Invalid booking!");

$res = $mysqli->prepare("SELECT p.*, b.id_kamar, b.checkin_rencana FROM pembayaran p JOIN booking b ON p.ref_id=b.id_booking WHERE b.id_booking=? AND p.ref_type='BOOKING'");
$res->bind_param('i', $id_booking);
$res->execute();
$row = $res->get_result()->fetch_assoc();
if (!$row) die("Data booking tidak ditemukan!");

?>
<!DOCTYPE html>
<html>
<head><title>Pembayaran Booking Kamar</title>
<link rel="stylesheet" href="assets/css/app.css"/></head>
<body style="background-color: #f8fafc;">

    <nav class="navbar" style="position: sticky; top: 0;">
        <div class="navbar-container">
            <a href="index.php" class="nav-brand">
                <div class="nav-brand-logo">K</div>
                <span style="font-weight: 700; font-size: 18px;">KOS PAADAASIH</span>
            </a>
            <div class="nav-menu">
                <a href="penghuni_dashboard.php" class="nav-link">Dashboard</a>
            </div>
        </div>
    </nav>

    <div class="container animate-fade-up" style="max-width: 600px; margin: 60px auto; padding: 20px;">
        
        <div class="card-white" style="padding: 0; overflow: hidden; border: none; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);">
            
            <!-- Header Invoice -->
            <div style="background: #1e293b; padding: 40px; text-align: center; color: white;">
                <i class="fa-solid fa-receipt" style="font-size: 48px; margin-bottom: 20px; opacity: 0.8;"></i>
                <h2 style="font-size: 24px; font-weight: 700; margin-bottom: 5px;">Pembayaran Booking</h2>
                <p style="opacity: 0.7;">Booking ID #<?= $id_booking ?></p>
            </div>

            <div style="padding: 40px;">
                
                <!-- Amount Box -->
                <div class="text-center mb-8">
                    <p style="font-size: 14px; color: #64748b; margin-bottom: 5px;">Total yang harus dibayar</p>
                    <div style="font-size: 36px; font-weight: 800; color: #1e293b;">
                        Rp <?= number_format($row['jumlah'], 0, ',', '.') ?>
                    </div>
                </div>

                <!-- Info Rekening -->
                <div style="background: #f8fafc; border: 2px dashed #cbd5e1; border-radius: 12px; padding: 20px; text-align: center; margin-bottom: 30px;">
                    <p style="font-size: 12px; color: #64748b; font-weight: 600; letter-spacing: 0.5px; text-transform: uppercase; margin-bottom: 8px;">Transfer ke Rekening</p>
                    <div style="font-size: 18px; font-weight: 700; color: #334155; margin-bottom: 4px;">BANK BCA 1473210151</div>
                    <div style="font-size: 14px; color: #64748b;">a.n KOS PAADAASIH</div>
                </div>

                <form method="POST" action="proses_pembayaran.php" enctype="multipart/form-data">
                    <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
                    <input type="hidden" name="id_pembayaran" value="<?= $row['id_pembayaran'] ?>">

                    <div style="margin-bottom: 30px;">
                        <label class="form-label" style="text-align: center; margin-bottom: 12px;">Upload Bukti Transfer</label>
                        <input type="file" name="bukti_tf" required 
                               class="form-input" style="padding: 12px; border: 1px solid #cbd5e1; background: #fff;">
                        <p class="text-xs text-center text-muted mt-2">Format: JPG/PNG/WEBP. Maks 2MB.</p>
                    </div>

                    <button type="submit" onclick="this.innerHTML='<i class=\'fa-solid fa-spinner fa-spin\'></i> Mengupload...'; this.disabled=true; this.form.submit();" 
                            class="btn btn-primary full" style="height: 50px; font-size: 16px;">
                        Konfirmasi Pembayaran
                    </button>
                    
                    <a href="penghuni_dashboard.php" class="btn btn-secondary full mt-4" style="border: none; color: #64748b;">
                        Kembali ke Dashboard
                    </a>
                </form>

            </div>
        </div>
        
        <div class="text-center mt-6">
            <p style="font-size: 13px; color: #94a3b8;">
                Jika ada kendala, hubungi Admin: <strong style="color: #64748b;">0812-3456-7890</strong>
            </p>
        </div>

    </div>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</body>
</html>