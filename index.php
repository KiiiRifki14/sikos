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
        <a href="https://wa.me/62881011201664" target="_blank" class="btn btn-secondary" style="padding: 12px 30px; font-size: 16px;">
            <i class="fa-brands fa-whatsapp"></i> Hubungi Kami
        </a>
      </div>
    </div>
  </section>

  <section id="kamar" class="section">
    <div class="flex justify-between items-end mb-8">
      <div>
        <h2 class="section-title">Kamar Tersedia</h2>

      </div>
    </div>

    <form method="get" class="filter-box">
      <div class="filter-grid">
        <div class="form-group">
          <label class="form-label">Status</label>
          <select name="status" class="form-input">
            <option value="">Semua Status</option>
            <option value="TERSEDIA" <?= (isset($_GET['status']) && $_GET['status']==='TERSEDIA') ? 'selected' : '' ?>>Tersedia</option>
            <option value="TERISI" <?= (isset($_GET['status']) && $_GET['status']==='TERISI') ? 'selected' : '' ?>>Terisi</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Tipe Kamar</label>
          <select name="tipe" class="form-input">
            <option value="">Semua Tipe</option>
            <?php while($t = $tipeRes->fetch_assoc()): ?>
              <option value="<?= $t['id_tipe'] ?>" <?= (isset($_GET['tipe']) && $_GET['tipe'] == $t['id_tipe']) ? 'selected' : '' ?>>
              <?= htmlspecialchars($t['nama_tipe']) ?>
            </option>
            <?php endwhile; ?>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Maks Harga</label>
          <input type="number" name="max_harga" value="<?= $_GET['max_harga'] ?? '' ?>" class="form-input" placeholder="Contoh 1500000">
        </div>
        <div class="form-group">
          <label class="form-label">Urutan</label>
          <div class="flex gap-2">
            <select name="order" class="form-input">
              <option value="terbaru" <?= ($order_param==='terbaru') ? 'selected' : '' ?>>Terbaru</option>
              <option value="harga_asc" <?= ($order_param==='harga_asc') ? 'selected' : '' ?>>Termurah</option>
              <option value="harga_desc" <?= ($order_param==='harga_desc') ? 'selected' : '' ?>>Termahal</option>
            </select>
          </div>
        </div>
        <div class="form-group">
            <button type="submit" class="btn btn-primary w-full">Terapkan</button>
        </div>
      </div>
    </form>

    <div id="kamar-container" class="grid-rooms">
      <?php while($row = $res->fetch_assoc()){ include 'components/card_kamar.php'; } ?>
    </div>

    <?php if ($sisaKamar > 0): ?>
    <div class="flex flex-col items-center justify-center mt-12 mb-12" id="load-more-wrapper">
        <button id="btn-load-more" onclick="loadMore()" class="btn btn-secondary" style="padding: 12px 30px;">
            Lihat Lebih Banyak (<?= $sisaKamar ?>+)
        </button>
        <div id="loading-spinner" class="hidden mt-4" style="color:var(--primary);">
            <i class="fa-solid fa-circle-notch fa-spin fa-2x"></i>
        </div>
    </div>
    <?php endif; ?>
  </section>

  <section id="fasilitas" class="section" style="background: white; border-top:1px solid var(--border); border-bottom:1px solid var(--border);">
    <div class="text-center mb-8">
      <h2 class="section-title">Fasilitas Bangunan</h2>
      <p class="section-subtitle">Kenyamanan Anda adalah prioritas kami</p>
    </div>
    <div class="grid-rooms"> <div class="card-white text-center">
        <i class="fa-solid fa-shield-halved" style="font-size:40px; color:var(--primary); margin-bottom:16px;"></i>
        <h3 class="font-bold mb-2">Keamanan 24 Jam</h3>
        <p class="text-sm text-muted">CCTV dan akses terkontrol untuk keamanan maksimal.</p>
      </div>
      <div class="card-white text-center">
        <i class="fa-solid fa-wifi" style="font-size:40px; color:var(--primary); margin-bottom:16px;"></i>
        <h3 class="font-bold mb-2">Internet Stabil</h3>
        <p class="text-sm text-muted">Wi-Fi cepat untuk menunjang aktivitas digital Anda.</p>
      </div>
      <div class="card-white text-center">
        <i class="fa-solid fa-shower" style="font-size:40px; color:var(--primary); margin-bottom:16px;"></i>
        <h3 class="font-bold mb-2">Kamar Mandi Dalam</h3>
        <p class="text-sm text-muted">Privasi dan kebersihan lebih terjaga di setiap kamar.</p>
      </div>
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