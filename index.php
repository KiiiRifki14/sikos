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

$order_param = $_GET['order'] ?? 'default';
// PRIORITASKAN TERSEDIA DULUAN (CASE WHEN), BARU ALFABET
$order_sql = "CASE WHEN k.status_kamar = 'TERSEDIA' THEN 1 ELSE 2 END ASC, k.kode_kamar ASC"; 

if ($order_param === 'harga_asc') $order_sql = "k.harga ASC";
elseif ($order_param === 'harga_desc') $order_sql = "k.harga DESC";
elseif ($order_param === 'terbaru') $order_sql = "k.id_kamar DESC";

// QUERY DATA
$sql = "SELECT k.*, t.nama_tipe, 
        (SELECT COUNT(*) FROM booking b WHERE b.id_kamar = k.id_kamar AND b.status = 'PENDING') as is_pending
        FROM kamar k 
        JOIN tipe_kamar t ON k.id_tipe=t.id_tipe";
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
    <div class="flex justify-between items-end mb-8">
      <div>
        <h2 class="section-title">Kamar Tersedia</h2>
        <p class="section-subtitle">Pilihan kamar terbaik untuk kenyamanan Anda</p>
      </div>
      <!-- Filter Toggle (Mobile) -->
      <button class="md:hidden btn btn-secondary" onclick="document.getElementById('filter-box').classList.toggle('hidden')">
        <i class="fa-solid fa-filter"></i> Filter
      </button>
    </div>

    <!-- FILTER BOX -->
    <div id="filter-box" class="filter-box hidden md:block animate-fade-up">
      <form id="filterForm" class="filter-grid">
         <!-- Status Filter -->
         <div class="form-group">
            <label class="form-label">Status</label>
            <select name="status" class="form-input">
                <option value="">Semua Status</option>
                <option value="TERSEDIA">Tersedia</option>
                <option value="TERISI">Terisi</option>
            </select>
         </div>
         <!-- Tipe Filter -->
         <div class="form-group">
            <label class="form-label">Tipe Kamar</label>
            <select name="tipe" class="form-input">
                <option value="">Semua Tipe</option>
                <?php
                $res_tipe = $db->koneksi->query("SELECT * FROM tipe_kamar");
                while($tipe = $res_tipe->fetch_assoc()):
                ?>
                <option value="<?= $tipe['id_tipe'] ?>"><?= htmlspecialchars($tipe['nama_tipe']) ?></option>
                <?php endwhile; ?>
            </select>
         </div>
         <!-- Harga Filter -->
         <div class="form-group">
            <label class="form-label">Maksimal Harga</label>
            <select name="max_harga" class="form-input">
                <option value="">Semua Harga</option>
                <option value="500000">Di bawah 500rb</option>
                <option value="1000000">Di bawah 1jt</option>
                <option value="2000000">Di bawah 2jt</option>
            </select>
         </div>
         <!-- Button Cari -->
         <div class="form-group">
             <button type="button" onclick="loadKamar(true)" class="btn btn-primary w-full" style="height: 48px; margin-top: auto;">
                <i class="fa-solid fa-search"></i> Terapkan
             </button>
         </div>
      </form>
    </div>

    <!-- ROOM LIST CONTAINER -->
    <div id="kamar-container" class="grid-rooms">
        <?php
        // LOGIC PHP UNTUK MENAMPILKAN 6 KAMAR PERTAMA
        // (Mirip dengan ajax_kamar.php tapi versi inisial)
        $sql_awal = "SELECT k.*, t.nama_tipe,
                     (SELECT COUNT(*) FROM booking b WHERE b.id_kamar = k.id_kamar AND b.status = 'PENDING') as is_pending 
                     FROM kamar k 
                     JOIN tipe_kamar t ON k.id_tipe=t.id_tipe
                     WHERE 1=1
                     ORDER BY CASE WHEN k.status_kamar = 'TERSEDIA' THEN 1 ELSE 2 END ASC, k.kode_kamar ASC
                     LIMIT 6";
        $res_awal = $db->koneksi->query($sql_awal);
        
        if($res_awal && $res_awal->num_rows > 0) {
            while($row = $res_awal->fetch_assoc()) {
                include 'components/card_kamar.php';
            }
        } else {
            echo '<p class="col-span-3 text-center text-muted">Belum ada data kamar.</p>';
        }
        ?>
    </div>

    <!-- LOAD MORE BUTTON -->
    <div id="load-more-wrapper" class="text-center mt-12">
        <button id="btn-load-more" onclick="loadMore()" class="btn btn-secondary px-8 py-3 rounded-full">
            Lihat Lebih Banyak <i class="fa-solid fa-chevron-down ml-2"></i>
        </button>
        <div id="loading-spinner" class="hidden">
            <i class="fa-solid fa-circle-notch fa-spin text-2xl text-primary"></i>
        </div>
    </div>
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
    /* Logic Filter & Load More */
    let currentOffset = 6;
    const limit = 6;
    
    // Function to load filtered rooms
    function loadKamar(reset = false) {
        const form = document.getElementById('filterForm');
        const formData = new FormData(form);
        const params = new URLSearchParams(formData);
        const spinner = document.getElementById('loading-spinner');
        const btn = document.getElementById('btn-load-more');
        const container = document.getElementById('kamar-container');

        if (reset) {
            currentOffset = 0; // Reset offset
            container.innerHTML = ''; // Clear container
            document.getElementById('load-more-wrapper').innerHTML = `
                <button id="btn-load-more" onclick="loadMore()" class="btn btn-secondary px-8 py-3 rounded-full">
                    Lihat Lebih Banyak <i class="fa-solid fa-chevron-down ml-2"></i>
                </button>
                <div id="loading-spinner" class="hidden">
                    <i class="fa-solid fa-circle-notch fa-spin text-2xl text-primary"></i>
                </div>`;
            // Re-select btn and spinner after rewrite
        }

        params.set('offset', currentOffset);

        // Show spinner
        const currentBtn = document.getElementById('btn-load-more');
        const currentSpinner = document.getElementById('loading-spinner');
        if(currentBtn) currentBtn.classList.add('hidden');
        if(currentSpinner) currentSpinner.classList.remove('hidden');

        fetch(`ajax_kamar.php?${params.toString()}`)
            .then(response => response.text())
            .then(data => {
                if(currentSpinner) currentSpinner.classList.add('hidden');
                
                if (data.trim().length > 0) {
                    container.insertAdjacentHTML('beforeend', data);
                    currentOffset += limit;
                    if(currentBtn) currentBtn.classList.remove('hidden');
                } else {
                    if (reset) {
                        container.innerHTML = '<p class="col-span-3 text-center text-muted">Tidak ada kamar yang cocok.</p>';
                    }
                    if(document.getElementById('load-more-wrapper')) {
                        document.getElementById('load-more-wrapper').innerHTML = '<p class="text-muted text-sm">Semua kamar sudah ditampilkan.</p>';
                    }
                }
            })
            .catch(err => {
                console.error(err);
                alert('Gagal memuat data.');
            });
    }

    // Load More Wrapper
    function loadMore() {
        loadKamar(false);
    }
  </script>
</body>
</html>