<?php
session_start();
require '../inc/koneksi.php';
require '../inc/guard.php';
if (!is_admin() && !is_owner()) die('Forbidden');

$db = new Database();

// --- 1. LOGIKA PENCARIAN & PAGINATION ---
$batas = 10;
$halaman = isset($_GET['halaman']) ? (int)$_GET['halaman'] : 1;
if ($halaman < 1) $halaman = 1;
$halaman_awal = ($halaman > 1) ? ($halaman * $batas) - $batas : 0;

$cari = isset($_GET['cari']) ? $_GET['cari'] : "";

// Query Dasar
$sql_base = "SELECT p.id_penghuni, u.nama, u.no_hp, k.kode_kamar, ko.tanggal_mulai, ko.tanggal_selesai, ko.status
             FROM penghuni p
             JOIN pengguna u ON p.id_pengguna = u.id_pengguna
             LEFT JOIN kontrak ko ON p.id_penghuni = ko.id_penghuni AND ko.status = 'AKTIF'
             LEFT JOIN kamar k ON ko.id_kamar = k.id_kamar";

// Filter WHERE
$where = [];
if (!empty($cari)) {
    // escape sederhana (idealnya prepared statements)
    $escaped = $mysqli->real_escape_string($cari);
    $where[] = "u.nama LIKE '%$escaped%'";
    $where[] = "k.kode_kamar LIKE '%$escaped%'";
}
if (!empty($where)) {
    $sql_base .= " WHERE (" . implode(" OR ", $where) . ")";
}

// Hitung Total Data
$sql_count = "SELECT COUNT(*) as total FROM (" . $sql_base . ") AS subcount";
$total_data_res = $mysqli->query($sql_count);
$total_data = ($total_data_res && $total_data_res->num_rows) ? (int)$total_data_res->fetch_assoc()['total'] : 0;
$total_halaman = $total_data > 0 ? (int)ceil($total_data / $batas) : 1;

// Query Final
$sql_final = $sql_base . " ORDER BY u.nama ASC LIMIT $halaman_awal, $batas";
$res = $mysqli->query($sql_final);

$nomor = $halaman_awal + 1;
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <title>Data Penghuni</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="../assets/css/app.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  
  <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
  <script src="../assets/js/main.js"></script>

  <style>
      /* Custom Style untuk Autocomplete agar serasi dengan desain baru */
      .ui-autocomplete {
        background: white; border: 1px solid var(--border); border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1); padding: 5px; max-width: 300px; z-index: 9999;
      }
      .ui-menu-item .ui-menu-item-wrapper {
        padding: 10px 12px; font-size: 14px; color: var(--text-main); border-radius: 6px; cursor: pointer;
      }
      .ui-state-active, .ui-widget-content .ui-state-active {
        background: #eff6ff !important; border: 1px solid #bfdbfe !important; color: var(--primary) !important;
      }

      /* Pagination small adjustments (optional) */
      .pagination-wrapper .btn { margin: 0 4px; }
  </style>
</head>
<body class="dashboard-body">

  <button class="sidebar-toggle" onclick="toggleSidebar()">☰</button>
  <?php include '../components/sidebar_admin.php'; ?>
  
  <main class="main-content animate-fade-up">
    <div class="flex justify-between items-center mb-6 flex-wrap gap-4">
        <h1 class="font-bold text-xl">Data Penghuni</h1>
        
        <a href="penghuni_print.php" target="_blank" class="btn btn-secondary text-sm">
            <i class="fa-solid fa-print"></i> Cetak Laporan
        </a>
    </div>

    <div class="mb-6" style="position:relative; max-width: 400px;">
        <form method="get" action="">
            <input type="text" id="cari_penghuni" name="cari" value="<?= htmlspecialchars($cari) ?>" 
                   class="form-input" 
                   placeholder="Ketik nama penghuni..." 
                   style="padding-left: 40px;">
            
            <button type="submit" style="position:absolute; left:12px; top:10px; background:none; border:none; color:var(--text-muted); cursor:pointer;">
                <i class="fa-solid fa-search"></i>
            </button>
        </form>
        <?php if(!empty($cari)): ?>
            <div class="mt-2">
                <a href="penghuni_data.php" class="text-xs text-red-500 hover:underline">Reset Pencarian</a>
            </div>
        <?php endif; ?>
    </div>

    <div class="card-white">
        <div style="overflow-x: auto;">
            <table style="width:100%;">
                <thead>
                    <tr>
                        <th>NO</th>
                        <th>NAMA</th>
                        <th>KAMAR</th>
                        <th>PERIODE SEWA</th>
                        <th>STATUS</th>
                        <th>AKSI</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                if ($res->num_rows > 0) {
                    while ($row = $res->fetch_assoc()) {
                        $statusBadge = ($row['status'] == 'AKTIF') 
                            ? '<span style="background:#dcfce7; color:#166534; padding:4px 8px; border-radius:4px; font-size:10px; font-weight:bold;">AKTIF</span>' 
                            : '<span style="background:#f1f5f9; color:#64748b; padding:4px 8px; border-radius:4px; font-size:10px; font-weight:bold;">MANTAN</span>';
                        
                        // Format WA
                        $hp = preg_replace('/^0/', '62', $row['no_hp']);
                ?>
                <tr>
                    <td class="text-center" style="color:var(--text-muted);"><?= $nomor++ ?></td>
                    <td>
                        <div class="font-bold"><?= htmlspecialchars($row['nama']) ?></div>
                        <div class="text-xs" style="color:var(--text-muted);">📞 <?= htmlspecialchars($row['no_hp']) ?></div>
                    </td>
                    <td class="font-bold" style="color:var(--primary);"><?= htmlspecialchars($row['kode_kamar'] ?? '-') ?></td>
                    <td>
                        <?php if($row['tanggal_mulai']): ?>
                            <div class="text-sm"><?= date('d M Y', strtotime($row['tanggal_mulai'])) ?></div>
                            <div class="text-xs" style="color:var(--text-muted);">s/d <?= date('d M Y', strtotime($row['tanggal_selesai'])) ?></div>
                        <?php else: ?> - <?php endif; ?>
                    </td>
                    <td><?= $statusBadge ?></td>
                    <td>
                        <div class="flex flex-wrap gap-2">
                            <!-- 1. CHAT WA -->
                            <a href="https://wa.me/<?= $hp ?>" target="_blank" class="btn text-xs" style="background:#25D366; color:white; padding:6px 10px;" title="Chat WhatsApp">
                                <i class="fa-brands fa-whatsapp"></i>
                            </a>

                            <!-- 2. EDIT -->
                            <a href="penghuni_edit.php?id=<?= $row['id_penghuni'] ?>" class="btn btn-secondary text-xs" style="padding:6px 10px;" title="Edit Biodata">
                                <i class="fa-solid fa-pen"></i>
                            </a>
                            
                            <?php if($row['status'] == 'AKTIF'): ?>
                                <!-- 3. PERPANJANG (Modal) -->
                                <button onclick="bukaModalPerpanjang(<?= $row['id_penghuni'] ?>, '<?= htmlspecialchars($row['nama']) ?>')" class="btn text-xs" style="background:#3b82f6; color:white; padding:6px 10px;" title="Perpanjang Sewa">
                                    <i class="fa-solid fa-clock-rotate-left"></i>
                                </button>

                                <!-- 4. STOP (Konfirmasi) -->
                                <a href="penghuni_proses.php?act=stop&id=<?= $row['id_penghuni'] ?>" onclick="konfirmasiAksi(event, 'Yakin ingin memberhentikan sewa (Checkout)? Kamar akan menjadi TERSEDIA kembali.', this.href)" class="btn text-xs" style="background:#ef4444; color:white; padding:6px 10px;" title="Stop Sewa / Checkout">
                                    <i class="fa-solid fa-power-off"></i>
                                </a>

                                <!-- 5. KONTRAK -->
                                <a href="cetak_kontrak.php?id=<?= $row['id_penghuni'] ?>" target="_blank" class="btn btn-primary text-xs" style="padding:6px 10px; background-color:#4f46e5;" title="Cetak Kontrak">
                                    <i class="fa-solid fa-file-contract"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php 
                    }
                } else {
                    echo "<tr><td colspan='6' class='text-center p-8 text-muted'>Data tidak ditemukan.</td></tr>";
                }
                ?>
                </tbody>
            </table>
        </div>

    <!-- MODAL PERPANJANG -->
    <div id="modalPerpanjang" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999; justify-content:center; align-items:center;">
        <div class="bg-white p-6 rounded-lg shadow-xl w-96 animate-fade-up">
            <h3 class="font-bold text-lg mb-4">Perpanjang Sewa</h3>
            <p class="text-sm mb-4">Penghuni: <span id="nama_penghuni_modal" class="font-bold"></span></p>
            
            <form action="penghuni_proses.php?act=perpanjang" method="POST">
                <input type="hidden" name="id_penghuni" id="id_penghuni_modal">
                <div class="mb-4">
                    <label class="block text-sm font-bold mb-2">Tambah Durasi (Bulan)</label>
                    <input type="number" name="durasi" class="form-input w-full" min="1" value="1" required>
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" onclick="tutupModal()" class="btn btn-secondary">Batal</button>
                    <button type="submit" class="btn btn-primary" onclick="konfirmasiForm(event, 'Yakin ingin memperpanjang sewa penghuni ini?')">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function bukaModalPerpanjang(id, nama) {
        document.getElementById('id_penghuni_modal').value = id;
        document.getElementById('nama_penghuni_modal').innerText = nama;
        document.getElementById('modalPerpanjang').style.display = 'flex';
    }
    function tutupModal() {
        document.getElementById('modalPerpanjang').style.display = 'none';
    }
    </script>

        <!-- Pagination (Standardized UI with Search Preservation) -->
        <?php
            $total_halaman = max(1, (int)$total_halaman);
            $prev = max(1, $halaman - 1);
            $next = min($total_halaman, $halaman + 1);
        ?>
        <div class="pagination-container" style="margin-top: 20px; display:flex; gap:5px; justify-content:center;">
            <?php
                $qs = $_GET;
                $qs['halaman'] = $prev;
                $href_prev = ($halaman > 1) ? '?'.http_build_query($qs) : '#';
                
                $qs['halaman'] = $next;
                $href_next = ($halaman < $total_halaman) ? '?'.http_build_query($qs) : '#';
            ?>
            
            <a href="<?= $href_prev ?>" 
               class="btn btn-secondary text-xs <?= ($halaman <= 1) ? 'disabled' : '' ?>" 
               style="padding:6px 12px;">
               <i class="fa-solid fa-chevron-left"></i> Prev
            </a>

            <?php for($x = 1; $x <= $total_halaman; $x++):
                $qs = $_GET;
                $qs['halaman'] = $x;
                $href_page = '?'.http_build_query($qs);
            ?>
                <a href="<?= $href_page ?>" 
                   class="btn text-xs <?= ($halaman == $x) ? 'btn-primary' : 'btn-secondary' ?>" 
                   style="padding:6px 12px;"><?= $x ?></a>
            <?php endfor; ?>

            <a href="<?= $href_next ?>" 
               class="btn btn-secondary text-xs <?= ($halaman >= $total_halaman) ? 'disabled' : '' ?>" 
               style="padding:6px 12px;">
               Next <i class="fa-solid fa-chevron-right"></i>
            </a>
        </div>
        
        <div class="text-center mt-4 text-xs text-muted">
            Halaman <?= $halaman ?> dari <?= $total_halaman ?> (Total <?= $total_data ?> data)
        </div>
    </div>
  </main>

  <script>
  $( function() {
    // Autocomplete dengan style baru
    var ac = $( "#cari_penghuni" ).autocomplete({
      source: "../api/cari_penghuni.php",
      minLength: 2,
      select: function( event, ui ) {
          $(this).val(ui.item.value);
          $(this).closest("form").submit();
      }
    });

    // Highlight hasil pencarian
    ac.autocomplete( "instance" )._renderItem = function( ul, item ) {
        var term = this.term; 
        var re = new RegExp( "(" + term + ")", "gi" );
        var highlightedResult = item.label.replace(re, "<span style='color: var(--primary); font-weight: 800;'>$1</span>");

        return $( "<li>" )
            .append( "<div>" + highlightedResult + "</div>" )
            .appendTo( ul );
    };
  } );
  </script>

</body>
</html>