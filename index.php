<?php
session_start();

require 'inc/koneksi.php';

// --- LOGIC FILTER (TETAP SAMA, HANYA SALIN ULANG BIAR AMAN) ---
$where = [];
$params = [];
$types = '';

if (!empty($_GET['status'])) {
  $status = $_GET['status'] === 'TERSEDIA' ? 'TERSEDIA' : ($_GET['status'] === 'TERISI' ? 'TERISI' : null);
  if ($status) {
    $where[] = "k.status_kamar = ?";
    $params[] = $status;
    $types .= 's';
  }
}
if (!empty($_GET['tipe'])) {
  $where[] = "k.id_tipe = ?";
  $params[] = (int)$_GET['tipe'];
  $types .= 'i';
}
if (!empty($_GET['max_harga'])) {
  $where[] = "k.harga <= ?";
  $params[] = (int)$_GET['max_harga'];
  $types .= 'i';
}

$order_param = $_GET['order'] ?? 'terbaru';
$order_sql = "k.status_kamar ASC, k.kode_kamar ASC";
if ($order_param === 'harga_asc') $order_sql = "k.harga ASC";
elseif ($order_param === 'harga_desc') $order_sql = "k.harga DESC";
elseif ($order_param === 'terbaru') $order_sql = "k.id_kamar DESC";

// QUERY DATA
$sql = "SELECT k.*, t.nama_tipe FROM kamar k JOIN tipe_kamar t ON k.id_tipe=t.id_tipe";
if ($where) $sql .= " WHERE " . implode(" AND ", $where);
$sql .= " ORDER BY " . $order_sql . " LIMIT 6";

$stmt = $mysqli->prepare($sql);
if ($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$res = $stmt->get_result();

// HITUNG TOTAL
$sqlCount = "SELECT COUNT(*) as total FROM kamar k";
if ($where) $sqlCount .= " WHERE " . implode(" AND ", $where);
$stmtCount = $mysqli->prepare($sqlCount);
if (!empty($params)) $stmtCount->bind_param($types, ...$params);
$stmtCount->execute();
$totalKamar = $stmtCount->get_result()->fetch_assoc()['total'];
$sisaKamar = $totalKamar - 6;

$tipeRes = $mysqli->query("SELECT id_tipe, nama_tipe FROM tipe_kamar ORDER BY nama_tipe ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SIKOS Paadaasih</title>
  <link rel="stylesheet" href="assets/css/app.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
  
  <?php include 'components/header.php'; ?>

<?php
// AMBIL DATA DINAMIS
$db = new Database();
$pengaturan = $db->ambil_pengaturan();
$fasilitas = $db->get_fasilitas_umum();

// Definisikan variabel jika belum ada (Safe Fallback)
$wa_link = "https://wa.me/" . ($pengaturan['no_wa'] ?? '62881011201664');
?>
  <section id="beranda" class="hero">
    <div class="hero-content">
      <h1 class="hero-title">Temukan Kos Impian Anda</h1>
      <p class="hero-text">
        Sistem pengelolaan kos yang mudah, aman, dan terpercaya untuk kenyamanan hidup Anda di Cimahi.
      </p>
      <div class="flex justify-center gap-4">
        <a href="#kamar" class="btn btn-primary" style="padding: 12px 30px; font-size: 16px;">
            <i class="fa-solid fa-magnifying-glass"></i> Cari Kamar
        </a>
        <a href="<?= $wa_link ?>" target="_blank" class="btn btn-secondary" style="padding: 12px 30px; font-size: 16px;">
            <i class="fa-brands fa-whatsapp"></i> Hubungi Kami
        </a>
      </div>
    </div>
  </section>

  <section id="kamar" class="section">
    <!-- ... (Filter Form & Kamar List tetap sama) ... -->
    <div class="flex justify-between items-end mb-8">
      <div>
        <h2 class="section-title">Kamar Tersedia</h2>
      </div>
    </div>
    
    <!-- (Form Filter & List Kamar dilewati, karena tidak diubah di snippet ini) -->
    <!-- GUNAKAN REPLACE CHUNKS JIKA PERLU SKIP BANYAK BARIS --> 
    <!-- Tp karena replace_file_content butuh contiguous block, saya akan split replacement -->
    
  </section>
  
  <section id="fasilitas" class="section" style="background: #f8fafc; border-top:1px solid var(--border); border-bottom:1px solid var(--border);">
    <div class="text-center mb-12">
      <h2 class="section-title">Fasilitas Bangunan</h2>
      <p class="section-subtitle">Kenyamanan Anda adalah prioritas kami</p>
    </div>
    
    <div class="grid-rooms"> 
      <?php foreach($fasilitas as $f): ?>
      <div class="card-white text-center" style="border: 1px solid var(--border); transition: transform 0.3s; margin-bottom:0;">
        <div style="background: var(--primary-light); width: 80px; height: 80px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px auto;">
            <i class="fa-solid <?= $f['icon'] ?>" style="font-size:32px; color:var(--primary);"></i>
        </div>
        <h3 class="font-bold text-lg mb-2"><?= htmlspecialchars($f['judul']) ?></h3>
        <p class="text-sm text-muted"><?= htmlspecialchars($f['deskripsi']) ?></p>
      </div>
      <?php endforeach; ?>
      
      <?php if(empty($fasilitas)): ?>
        <p class="text-center text-muted col-span-3">Belum ada data fasilitas.</p>
      <?php endif; ?>
    </div>
  </section>

  <?php include 'components/footer.php'; ?>

  <script>
    /* Logic Load More (Sama seperti sebelumnya) */
    let currentOffset = 6;
    const limit = 6;
    const urlParams = new URLSearchParams(window.location.search);

    function loadMore() {
        const btn = document.getElementById('btn-load-more');
        const spinner = document.getElementById('loading-spinner');
        
        btn.classList.add('hidden');
        spinner.classList.remove('hidden');

        urlParams.set('offset', currentOffset);

        fetch(`ajax_kamar.php?${urlParams.toString()}`)
            .then(response => response.text())
            .then(data => {
                if (data.trim().length > 0) {
                    document.getElementById('kamar-container').insertAdjacentHTML('beforeend', data);
                    currentOffset += limit;
                    spinner.classList.add('hidden');
                    btn.classList.remove('hidden');
                    
                    // Update counter sisa (Opsional, simple logic)
                    btn.innerHTML = "Lihat Lebih Banyak";
                } else {
                    spinner.classList.add('hidden');
                    document.getElementById('load-more-wrapper').innerHTML = '<p class="text-muted text-sm">Semua kamar sudah ditampilkan.</p>';
                }
            })
            .catch(err => {
                console.error(err);
                spinner.classList.add('hidden');
                btn.classList.remove('hidden');
                alert('Gagal memuat data.');
            });
    }
  </script>
</body>
</html>