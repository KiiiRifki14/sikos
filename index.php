<?php
session_start();
require 'inc/koneksi.php';

// --- TRIGGER AUTO CANCEL ---
// Jalankan pengecekan setiap kali halaman beranda dibuka
$db->auto_batal_booking();

// --- LOGIC FILTER & URUTAN ---
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

// 1. QUERY DATA (Hanya 6 Pertama)
// MODIFIKASI: Tambahkan pengecekan agar kamar yang sedang di-booking (PENDING) tidak muncul/dianggap tersedia
$sql = "SELECT k.*, t.nama_tipe 
        FROM kamar k 
        JOIN tipe_kamar t ON k.id_tipe=t.id_tipe 
        WHERE k.id_kamar NOT IN (SELECT id_kamar FROM booking WHERE status='PENDING')"; // Filter tambahan

if ($where) $sql .= " AND " . implode(" AND ", $where); // Perhatikan ganti WHERE jadi AND karena WHERE sudah dipakai di atas
$sql .= " ORDER BY " . $order_sql . " LIMIT 6";

$stmt = $mysqli->prepare($sql);
if ($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$res = $stmt->get_result();

// 2. HITUNG TOTAL DATA (Untuk cek sisa kamar)
$sqlCount = "SELECT COUNT(*) as total FROM kamar k";
if ($where) $sqlCount .= " WHERE " . implode(" AND ", $where);
$stmtCount = $mysqli->prepare($sqlCount);
if (!empty($params)) $stmtCount->bind_param($types, ...$params);
$stmtCount->execute();
$totalKamar = $stmtCount->get_result()->fetch_assoc()['total'];
$sisaKamar = $totalKamar - 6; // Menghitung sisa kamar yang belum tampil

/* Data Tipe untuk Filter */
$tipeRes = $mysqli->query("SELECT id_tipe, nama_tipe FROM tipe_kamar ORDER BY nama_tipe ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SIKOS Paadaasih</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="assets/css/app.css"/>
</head>
<body class="bg-slate-50 pt-20 md:pt-24">
  <?php include 'components/header.php'; ?>

  <section id="beranda" class="hero-container mb-12 bg-[url('assets/img/hero-bg.jpg')] bg-cover bg-center min-h-[35vh] flex items-start">
    <div class="hero-box w-full text-center pt-20 pb-10 px-20 bg-white/90 transform translate-y-6 md:translate-y-8 lg:translate-y-10 max-w-4xl mx-auto rounded-xl shadow-sm -mb-6">
      <h1 class="text-4xl md:text-5xl font-extrabold text-slate-900 mb-4">Temukan Kos Impian Anda</h1>
      <p class="text-lg text-slate-600 mb-6 max-w-2xl mx-auto">
        Sistem pengelolaan kos yang mudah, aman, dan terpercaya untuk kenyamanan hidup Anda di Cimahi.
      </p>
      <div class="flex justify-center gap-4">
        <a href="#kamar" class="bg-blue-600 text-white px-8 py-3 rounded-xl font-bold shadow-lg hover:bg-blue-700 transition">🔍 Cari Kamar</a>
        <a href="https://wa.me/62881011201664" target="_blank" class="bg-white border border-slate-200 text-slate-700 px-8 py-3 rounded-xl font-bold hover:bg-slate-50 transition flex items-center gap-2">📞 Hubungi Kami</a>
      </div>
    </div>
  </section>

  <section id="kamar" class="max-w-7xl mx-auto px-6 mb-20">
    <div class="flex items-end justify-between mb-8">
      <div>
        <h2 class="text-2xl font-bold text-slate-900">Kamar Tersedia</h2>
        <p class="text-slate-500">Menampilkan kamar terbaik untuk Anda (Total: <?= $totalKamar ?>)</p>
      </div>
    </div>

    <form method="get" id="filter-kamar" class="mb-8 bg-white border border-slate-200 rounded-xl p-4 md:p-5">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-3 items-end">
        <div>
          <label class="block text-xs font-bold text-slate-600 mb-1">Status</label>
          <select name="status" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm">
            <option value="">Semua Status</option>
            <option value="TERSEDIA" <?= (isset($_GET['status']) && $_GET['status']==='TERSEDIA') ? 'selected' : '' ?>>Tersedia</option>
            <option value="TERISI" <?= (isset($_GET['status']) && $_GET['status']==='TERISI') ? 'selected' : '' ?>>Terisi</option>
          </select>
        </div>
        <div>
          <label class="block text-xs font-bold text-slate-600 mb-1">Tipe Kamar</label>
          <select name="tipe" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm">
            <option value="">Semua Tipe</option>
            <?php while($t = $tipeRes->fetch_assoc()): ?>
              <option value="<?= $t['id_tipe'] ?>" <?= (isset($_GET['tipe']) && $_GET['tipe']==$t['id_tipe']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($t['nama_tipe']) ?>
              </option>
            <?php endwhile; ?>
          </select>
        </div>
        <div>
          <label class="block text-xs font-bold text-slate-600 mb-1">Maks Harga</label>
          <div class="relative">
            <span class="absolute left-3 top-2.5 text-slate-400 text-sm">Rp</span>
            <input type="number" name="max_harga" value="<?= $_GET['max_harga'] ?? '' ?>" class="w-full border border-slate-200 rounded-lg pl-10 pr-3 py-2 text-sm" placeholder="Contoh 1500000">
          </div>
        </div>
        <div>
          <label class="block text-xs font-bold text-slate-600 mb-1">Urutan</label>
          <div class="flex gap-2">
            <select name="order" class="flex-1 border border-slate-200 rounded-lg px-3 py-2 text-sm">
              <option value="terbaru" <?= ($order_param==='terbaru') ? 'selected' : '' ?>>Terbaru</option>
              <option value="harga_asc" <?= ($order_param==='harga_asc') ? 'selected' : '' ?>>Termurah</option>
              <option value="harga_desc" <?= ($order_param==='harga_desc') ? 'selected' : '' ?>>Termahal</option>
            </select>
            <a href="index.php" class="px-3 py-2 rounded-lg border text-sm hover:bg-slate-50">Reset</a>
          </div>
        </div>
      </div>
      <div class="mt-4 flex justify-end">
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold">Terapkan</button>
      </div>
    </form>

    <div id="kamar-container" class="grid grid-cols-1 md:grid-cols-3 gap-8">
      <?php while($row = $res->fetch_assoc()){ include 'components/card_kamar.php'; } ?>
    </div>

    <?php if ($sisaKamar > 0): ?>
    <div class="mt-12 text-center" id="load-more-wrapper">
        <button id="btn-load-more" onclick="loadMore()" class="bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 font-bold py-3 px-8 rounded-full shadow-sm transition transform hover:-translate-y-0.5 flex items-center gap-2 mx-auto">
            <span>Lihat Lebih Banyak</span>
            <span class="bg-slate-200 text-xs px-2 py-0.5 rounded-full text-slate-600" id="sisa-count"><?= $sisaKamar ?>+</span>
        </button>
        <div id="loading-spinner" class="hidden text-blue-600 mt-4">
            <svg class="animate-spin h-8 w-8 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </div>
    </div>
    <?php endif; ?>

  </section>

  <section id="fasilitas" class="max-w-7xl mx-auto px-6 mb-24">
    <div class="text-center mb-10">
      <h2 class="text-2xl font-bold text-slate-900">Fasilitas Bangunan</h2>
      <p class="text-slate-500">Kenyamanan Anda adalah prioritas kami</p>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div class="bg-white border border-slate-200 rounded-xl p-6">
        <div class="text-3xl mb-3">🛡️</div>
        <h3 class="font-bold text-slate-900 mb-2">Keamanan 24 Jam</h3>
        <p class="text-sm text-slate-600">CCTV dan akses terkontrol.</p>
      </div>
      <div class="bg-white border border-slate-200 rounded-xl p-6">
        <div class="text-3xl mb-3">📶</div>
        <h3 class="font-bold text-slate-900 mb-2">Internet Stabil</h3>
        <p class="text-sm text-slate-600">Wi-Fi cepat untuk aktivitas digital.</p>
      </div>
      <div class="bg-white border border-slate-200 rounded-xl p-6">
        <div class="text-3xl mb-3">🚿</div>
        <h3 class="font-bold text-slate-900 mb-2">Kamar Mandi Dalam</h3>
        <p class="text-sm text-slate-600">Privasi dan kebersihan terjaga.</p>
      </div>
    </div>
  </section>

  <?php include 'components/footer.php'; ?>

  <script>
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
                    
                    // Update counter sisa
                    let sisaElem = document.getElementById('sisa-count');
                    let sisa = parseInt(sisaElem.innerText);
                    if(sisa > limit) {
                        sisaElem.innerText = (sisa - limit) + '+';
                    } else {
                        // Kalau sudah habis, sembunyikan tombol
                         document.getElementById('load-more-wrapper').innerHTML = '<p class="text-slate-400 text-sm mt-8">Semua kamar sudah ditampilkan.</p>';
                    }
                } else {
                    spinner.classList.add('hidden');
                    document.getElementById('load-more-wrapper').innerHTML = '<p class="text-slate-400 text-sm mt-8">Semua kamar sudah ditampilkan.</p>';
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