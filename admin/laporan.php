<?php
session_start();
require '../inc/koneksi.php';
require '../inc/guard.php';

if (!is_admin() && !is_owner()) die('Forbidden');

// Penanganan POST: generate tagihan
$pesan = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['act'] === 'generate') {
    $id_kontrak = intval($_POST['id_kontrak'] ?? 0);
    $bulan = $_POST['bulan_tagih'] ?? '';
    // Validasi input
    if ($id_kontrak < 1 || !$bulan) {
        $pesan = "<div style='color:red'>Kontrak dan bulan wajib dipilih!</div>";
    } else {
        // Cek apakah tagihan bulan ini sudah ada
        $stmt = $mysqli->prepare("SELECT 1 FROM tagihan WHERE id_kontrak=? AND bulan_tagih=?");
        $stmt->bind_param('is', $id_kontrak, $bulan);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows == 0) {
            // Ambil harga
            $row = $mysqli->query("SELECT kamar.harga FROM kamar INNER JOIN kontrak ON kamar.id_kamar=kontrak.id_kamar WHERE kontrak.id_kontrak=$id_kontrak")->fetch_assoc();
            $nominal = $row ? $row['harga'] : 0;
            // Buat tagihan baru
            $stmt2 = $mysqli->prepare("INSERT INTO tagihan (id_kontrak, bulan_tagih, nominal, jatuh_tempo) VALUES (?, ?, ?, DATE_ADD(CURDATE(), INTERVAL 10 DAY))");
            $stmt2->bind_param('isi', $id_kontrak, $bulan, $nominal);
            $stmt2->execute();
            $pesan = "<div style='color:green'>Tagihan sukses dibuat!</div>";
        } else {
            $pesan = "<div style='color:red'>Tagihan untuk kontrak dan bulan tersebut sudah ada!</div>";
        }
    }
}

// Ambil list kontrak aktif
$kontrak = [];
$res = $mysqli->query("SELECT k.id_kontrak, kamar.kode_kamar, p.nama, k.tanggal_mulai, k.tanggal_selesai FROM kontrak k 
    JOIN kamar ON k.id_kamar = kamar.id_kamar
    JOIN penghuni ph ON ph.id_penghuni=k.id_penghuni
    JOIN pengguna p ON p.id_pengguna=ph.id_pengguna
    WHERE k.status='AKTIF'");
while ($r = $res->fetch_assoc()) $kontrak[] = $r;
?>
<!DOCTYPE html>
<html>
<head>
    <title>Generate Tagihan Bulanan</title>
    <link rel="stylesheet" href="../assets/css/app.css"/>
</head>
<body>
<div class="container">
    <h2>Generate Tagihan Bulanan</h2>
    <?php if($pesan) echo $pesan; ?>
    <form method="post">
        Kontrak:
        <select name="id_kontrak" required>
            <option value="">-- Pilih Kontrak --</option>
            <?php foreach($kontrak as $k){ ?>
                <option value="<?= $k['id_kontrak'] ?>">
                    Kamar <?= htmlspecialchars($k['kode_kamar']) ?> -
                    <?= htmlspecialchars($k['nama']) ?> 
                    (<?= $k['tanggal_mulai'] ?> s/d <?= $k['tanggal_selesai'] ?>)
                </option>
            <?php } ?>
        </select>
        Bulan Tagih:
        <input type="month" name="bulan_tagih" required>
        <input type="hidden" name="act" value="generate">
        <button type="submit">Generate Tagihan</button>
    </form>
    <br>
    <a href="index.php" class="button">Kembali ke Dashboard</a>
</div>
</body>
</html>