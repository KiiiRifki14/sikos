<?php
session_start();
require 'inc/koneksi.php';

/* Filter (opsional, tidak menghilangkan fitur lama) */
$where = [];
$params = [];
if (!empty($_GET['status'])) {
  $status = $_GET['status'] === 'TERSEDIA' ? 'TERSEDIA' : ($_GET['status'] === 'TERISI' ? 'TERISI' : null);
  if ($status) {
    $where[] = "k.status_kamar = ?";
    $params[] = $status;
  }
}
if (!empty($_GET['tipe'])) {
  $where[] = "k.id_tipe = ?";
  $params[] = (int)$_GET['tipe'];
}
if (!empty($_GET['max_harga'])) {
  $where[] = "k.harga <= ?";
  $params[] = (int)$_GET['max_harga'];
}

/* Order handling (server-side) */
$order_param = $_GET['order'] ?? 'terbaru';
$order_sql = "k.status_kamar ASC, k.kode_kamar ASC"; // default
if ($order_param === 'harga_asc') {
  $order_sql = "k.harga ASC";
} elseif ($order_param === 'harga_desc') {
  $order_sql = "k.harga DESC";
} elseif ($order_param === 'terbaru') {
  // contoh: asumsikan id_kamar autoincrement -> terbaru = id_kamar DESC
  $order_sql = "k.id_kamar DESC";
}

/* Build SQL */
$sql = "SELECT k.*, t.nama_tipe FROM kamar k JOIN tipe_kamar t ON k.id_tipe=t.id_tipe";
if ($where) $sql .= " WHERE " . implode(" AND ", $where);
$sql .= " ORDER BY " . $order_sql;

/* Prepared statement untuk aman */
$stmt = $mysqli->prepare($sql);
if ($params) {
  // Build types string (i=int, s=string)
  $types = '';
  foreach ($params as $p) $types .= is_int($p) ? 'i' : 's';
  $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$res = $stmt->get_result();

/* Ambil data tipe untuk filter dropdown */
$tipeRes = $mysqli->query("SELECT id_tipe, nama_tipe FROM tipe_kamar ORDER BY nama_tipe ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SIKOS Paadaasih</title>
  <meta name="description" content="Sistem pengelolaan kos yang mudah, aman, dan terpercaya untuk kenyamanan hidup Anda di Cimahi.">
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="assets/css/app.css"/>
</head>
<body class="bg-slate-50 pt-20 md:pt-24">
  <?php include 'components/header.php'; ?>

<!-- Hero: posisi atas pas, bawah tidak terlalu jauh -->
<section id="beranda" class="hero-container mb-12 bg-[url('assets/img/hero-bg.jpg')] bg-cover bg-center min-h-[35vh] flex items-start">
  <div class="hero-box w-full text-center pt-20 pb-10 px-20 bg-white/90
              transform translate-y-6 md:translate-y-8 lg:translate-y-10
              max-w-4xl mx-auto rounded-xl shadow-sm -mb-6">
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

  <!-- Kamar -->
  <section id="kamar" class="max-w-7xl mx-auto px-6 mb-20">
    <div class="flex items-end justify-between mb-8">
      <div>
        <h2 class="text-2xl font-bold text-slate-900">Kamar Tersedia</h2>
        <p class="text-slate-500">Pilih kamar yang sesuai dengan kebutuhan Anda</p>
      </div>
    </div>


<!-- Filter 4 kontrol (ganti blok form lama dengan ini) -->
<form method="get" id="filter-kamar" class="mb-8 bg-white border border-slate-200 rounded-xl p-4 md:p-5">
  <div class="grid grid-cols-1 md:grid-cols-4 gap-3 items-end">
    <!-- Status -->
    <div>
      <label for="status" class="block text-xs font-bold text-slate-600 mb-1">Status</label>
      <select id="status" name="status" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm">
        <option value="">Semua Status</option>
        <option value="TERSEDIA" <?= (isset($_GET['status']) && $_GET['status']==='TERSEDIA') ? 'selected' : '' ?>>Tersedia</option>
        <option value="TERISI" <?= (isset($_GET['status']) && $_GET['status']==='TERISI') ? 'selected' : '' ?>>Terisi</option>
      </select>
    </div>

    <!-- Tipe Kamar -->
    <div>
      <label for="tipe" class="block text-xs font-bold text-slate-600 mb-1">Tipe Kamar</label>
      <select id="tipe" name="tipe" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm">
        <option value="">Semua Tipe</option>
        <?php
          // pastikan pointer hasil tipeRes di-reset jika sebelumnya sudah di-loop
          if (isset($tipeRes) && $tipeRes instanceof mysqli_result) $tipeRes->data_seek(0);
          while(isset($tipeRes) && $t = $tipeRes->fetch_assoc()):
        ?>
          <option value="<?= (int)$t['id_tipe'] ?>" <?= (isset($_GET['tipe']) && (int)$_GET['tipe']===(int)$t['id_tipe']) ? 'selected' : '' ?>>
            <?= htmlspecialchars($t['nama_tipe']) ?>
          </option>
        <?php endwhile; ?>
      </select>
    </div>

    <!-- Maks Harga -->
    <div>
      <label for="max_harga" class="block text-xs font-bold text-slate-600 mb-1">Maks Harga</label>
      <div class="relative">
        <span class="absolute left-3 top-2.5 text-slate-400 text-sm">Rp</span>
        <input id="max_harga" type="number" name="max_harga" min="0" step="50000"
               value="<?= isset($_GET['max_harga']) ? (int)$_GET['max_harga'] : '' ?>"
               class="w-full border border-slate-200 rounded-lg pl-10 pr-3 py-2 text-sm"
               placeholder="Contoh 1500000" aria-label="Maksimal harga per bulan">
      </div>
    </div>

    <!-- Urutan dan Reset -->
    <div>
      <label for="order" class="block text-xs font-bold text-slate-600 mb-1">Urutan</label>
      <div class="flex gap-2">
        <select id="order" name="order" class="flex-1 border border-slate-200 rounded-lg px-3 py-2 text-sm">
          <option value="terbaru" <?= (isset($_GET['order']) && $_GET['order']==='terbaru') ? 'selected' : '' ?>>Terbaru</option>
          <option value="harga_asc" <?= (isset($_GET['order']) && $_GET['order']==='harga_asc') ? 'selected' : '' ?>>Harga Terendah</option>
          <option value="harga_desc" <?= (isset($_GET['order']) && $_GET['order']==='harga_desc') ? 'selected' : '' ?>>Harga Tertinggi</option>
        </select>

        <a href="index.php#kamar" class="inline-flex items-center justify-center px-3 py-2 rounded-lg border border-slate-200 text-sm text-slate-700 hover:bg-slate-50">
          Reset
        </a>
      </div>
    </div>
  </div>

  <!-- Tombol Terapkan di bawah untuk mobile, kanan untuk desktop -->
  <div class="mt-4 flex justify-end">
    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold">
      Terapkan
    </button>
  </div>
</form>
  <!-- Ringkasan singkat (hanya tampil kalau ada filter aktif) -->
  

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
      <?php while($row = $res->fetch_assoc()){ include 'components/card_kamar.php'; } ?>
    </div>
  </section>

  <!-- Fasilitas -->
  <section id="fasilitas" class="max-w-7xl mx-auto px-6 mb-24">
    <div class="text-center mb-10">
      <h2 class="text-2xl font-bold text-slate-900">Fasilitas Bangunan</h2>
      <p class="text-slate-500">Kenyamanan Anda adalah prioritas kami</p>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div class="bg-white border border-slate-200 rounded-xl p-6">
        <div class="text-3xl mb-3">🛡️</div>
        <h3 class="font-bold text-slate-900 mb-2">Keamanan 24 Jam</h3>
        <p class="text-sm text-slate-600">CCTV dan akses terkontrol untuk kenyamanan penghuni.</p>
      </div>
      <div class="bg-white border border-slate-200 rounded-xl p-6">
        <div class="text-3xl mb-3">📶</div>
        <h3 class="font-bold text-slate-900 mb-2">Internet Stabil</h3>
        <p class="text-sm text-slate-600">Wi-Fi cepat untuk kerja dan hiburan.</p>
      </div>
      <div class="bg-white border border-slate-200 rounded-xl p-6">
        <div class="text-3xl mb-3">🚿</div>
        <h3 class="font-bold text-slate-900 mb-2">Kamar Mandi Dalam</h3>
        <p class="text-sm text-slate-600">Privasi dan kebersihan terjaga setiap saat.</p>
      </div>
    </div>
  </section>

  <?php include 'components/footer.php'; ?>
</body>
</html>