<?php
session_start();
require '../inc/koneksi.php';
require '../inc/guard.php';
if (!is_admin() && !is_owner()) die('Forbidden');

$db = new Database();

// --- 1. LOGIKA PENCARIAN & PAGINATION ---
$batas = 10; 
$halaman = isset($_GET['halaman']) ? (int)$_GET['halaman'] : 1;
$halaman_awal = ($halaman > 1) ? ($halaman * $batas) - $batas : 0;

$cari = isset($_GET['cari']) ? $_GET['cari'] : "";

// Query Dasar 
$sql_base = "SELECT p.id_penghuni, u.nama, u.no_hp, k.kode_kamar, ko.tanggal_mulai, ko.tanggal_selesai, ko.status
             FROM penghuni p
             JOIN pengguna u ON p.id_pengguna = u.id_pengguna
             LEFT JOIN kontrak ko ON p.id_penghuni = ko.id_penghuni AND ko.status = 'AKTIF'
             LEFT JOIN kamar k ON ko.id_kamar = k.id_kamar";

// Filter WHERE
if (!empty($cari)) {
    $sql_base .= " WHERE u.nama LIKE '%$cari%' OR k.kode_kamar LIKE '%$cari%'";
}

// Hitung Total Data
$sql_count = str_replace("SELECT p.id_penghuni, u.nama, u.no_hp, k.kode_kamar, ko.tanggal_mulai, ko.tanggal_selesai, ko.status", "SELECT COUNT(*) as total", $sql_base);
$total_data = $mysqli->query($sql_count)->fetch_assoc()['total'];
$total_halaman = ceil($total_data / $batas);

// Query Final
$sql_final = $sql_base . " ORDER BY u.nama ASC LIMIT $halaman_awal, $batas";
$res = $mysqli->query($sql_final);

$nomor = $halaman_awal + 1;
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <title>Data Penghuni</title>

  <link rel="stylesheet" href="../assets/css/app.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  
  <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>

  <style>
      .pagination { display: flex; list-style: none; gap: 5px; margin-top: 20px; justify-content: center; }
      .page-link { 
          padding: 8px 14px; border: 1px solid #e2e8f0; background: white; 
          color: #64748b; border-radius: 6px; font-size: 14px; font-weight: 600; text-decoration: none; transition: 0.2s;
      }
      .page-link:hover { background: #f1f5f9; color: #1e293b; }
      .page-item.active .page-link { background: #2563eb; color: white; border-color: #2563eb; }
      .page-item.disabled .page-link { background: #f8fafc; color: #cbd5e1; cursor: not-allowed; }

      /* 2. CUSTOM CSS AUTOCOMPLETE */
      .ui-autocomplete {
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        padding: 5px;
        max-width: 300px;
        z-index: 9999; /* Pastikan di atas elemen lain */
      }
      .ui-menu-item {
        list-style: none; 
        margin: 0;
        padding: 0;
      }
      /* Kita styling div di dalam li, bukan li-nya langsung, karena jQuery UI ngebungkus pake div */
      .ui-menu-item .ui-menu-item-wrapper {
        padding: 10px 12px;
        font-size: 14px;
        color: #475569;
        border-radius: 6px;
        cursor: pointer;
        display: block;
      }
      /* Saat disorot/hover */
      .ui-state-active,
      .ui-widget-content .ui-state-active,
      .ui-widget-header .ui-state-active,
      a.ui-button:active,
      .ui-button:active,
      .ui-button.ui-state-active:hover {
        border: 1px solid #bfdbfe !important;
        background: #eff6ff !important;
        color: #1e293b !important;
        font-weight: normal !important;
      }
  </style>
</head>
<body class="dashboard-body">
  <?php include '../components/sidebar_admin.php'; ?>
  <main class="main-content">
     </main>
</body>
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:32px;">
        <h1 style="font-size:24px; font-weight:700; color:#1e293b;">Data Penghuni</h1>
        
        <a href="penghuni_print.php" target="_blank" class="btn-secondary" style="text-decoration:none;">
            <i class="fa-solid fa-print"></i> Cetak Laporan
        </a>
    </div>

    <div class="relative mb-6">
        <form method="get" action="">
            <input type="text" id="cari_penghuni" name="cari" value="<?= htmlspecialchars($cari) ?>" 
                   placeholder="Ketik nama penghuni..." 
                   style="padding:10px 16px; padding-left:40px; border-radius:8px; border:1px solid #e2e8f0; font-size:14px; width:300px;">
            
            <button type="submit" style="position:absolute; left:12px; top:12px; background:none; border:none; color:#94a3b8;">
                <i class="fa-solid fa-search"></i>
            </button>
            <?php if(!empty($cari)): ?>
                <a href="penghuni_data.php" class="text-red-500 text-sm ml-2 hover:underline">Reset</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="card-white">
        <table style="width:100%; border-collapse:collapse; font-size:14px;">
            <thead>
                <tr style="background:#f8fafc; text-align:left; border-bottom:1px solid #e2e8f0;">
                    <th style="padding:16px; color:#64748b; text-transform:uppercase; font-size:12px;">No</th>
                    <th style="padding:16px; color:#64748b; text-transform:uppercase; font-size:12px;">Nama</th>
                    <th style="padding:16px; color:#64748b; text-transform:uppercase; font-size:12px;">Kamar</th>
                    <th style="padding:16px; color:#64748b; text-transform:uppercase; font-size:12px;">Periode Sewa</th>
                    <th style="padding:16px; color:#64748b; text-transform:uppercase; font-size:12px;">Status</th>
                    <th style="padding:16px; color:#64748b; text-transform:uppercase; font-size:12px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php
            if ($res->num_rows > 0) {
                while ($row = $res->fetch_assoc()) {
                    $statusBadge = ($row['status'] == 'AKTIF') 
                        ? '<span style="background:#dcfce7; color:#166534; padding:4px 10px; border-radius:6px; font-size:11px; font-weight:700;">AKTIF</span>' 
                        : '<span style="background:#f1f5f9; color:#64748b; padding:4px 10px; border-radius:6px; font-size:11px; font-weight:700;">NON-AKTIF</span>';
            ?>
            <tr style="border-bottom:1px solid #f1f5f9;">
                <td style="padding:16px; width:50px; text-align:center; color:#64748b;"><?= $nomor++ ?></td>
                <td style="padding:16px;">
                    <div style="font-weight:700; color:#1e293b;"><?= htmlspecialchars($row['nama']) ?></div>
                    <div style="font-size:12px; color:#64748b;">📞 <?= htmlspecialchars($row['no_hp']) ?></div>
                </td>
                <td style="padding:16px; font-weight:600; color:#1e293b;"><?= $row['kode_kamar'] ?? '-' ?></td>
                <td style="padding:16px;">
                    <?php if($row['tanggal_mulai']): ?>
                        <div style="font-size:13px;"><?= date('d M Y', strtotime($row['tanggal_mulai'])) ?></div>
                        <div style="font-size:12px; color:#94a3b8;">s/d <?= date('d M Y', strtotime($row['tanggal_selesai'])) ?></div>
                    <?php else: ?> - <?php endif; ?>
                </td>
                <td style="padding:16px;"><?= $statusBadge ?></td>
                <td style="padding:16px;">
                    <a href="penghuni_edit.php?id=<?= $row['id_penghuni'] ?>" class="btn-secondary" style="padding:6px 10px; font-size:11px; text-decoration:none; margin-right:4px;">
                        <i class="fa-solid fa-pen"></i> Edit
                    </a>
                    
                    <?php if($row['status'] == 'AKTIF'): ?>
                        <a href="cetak_kontrak.php?id=<?= $row['id_penghuni'] ?>" target="_blank" class="btn-primary" style="padding:6px 10px; font-size:11px; text-decoration:none; background-color:#4f46e5;">
                            <i class="fa-solid fa-file-contract"></i> Kontrak
                        </a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php 
                }
            } else {
                echo "<tr><td colspan='6' class='text-center py-8 text-slate-400'>Data tidak ditemukan.</td></tr>";
            }
            ?>
            </tbody>
        </table>

        <nav>
            <ul class="pagination">
                <li class="page-item <?= ($halaman <= 1) ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= ($halaman > 1) ? "?halaman=".($halaman-1)."&cari=$cari" : '#' ?>">Previous</a>
                </li>
                <?php for($x = 1; $x <= $total_halaman; $x++): ?>
                    <li class="page-item <?= ($halaman == $x) ? 'active' : '' ?>">
                        <a class="page-link" href="?halaman=<?= $x ?>&cari=<?= $cari ?>"><?= $x ?></a>
                    </li>
                <?php endfor; ?>
                <li class="page-item <?= ($halaman >= $total_halaman) ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= ($halaman < $total_halaman) ? "?halaman=".($halaman+1)."&cari=$cari" : '#' ?>">Next</a>
                </li>
            </ul>
        </nav>
        <div style="text-align:center; margin-top:10px; font-size:12px; color:#94a3b8;">
            Menampilkan halaman <?= $halaman ?> dari <?= $total_halaman ?> (Total <?= $total_data ?> data)
        </div>
    </div>
  </main>

  <script>
  $( function() {
    // Inisialisasi Autocomplete
    var ac = $( "#cari_penghuni" ).autocomplete({
      source: "../api/cari_penghuni.php",
      minLength: 2,
      select: function( event, ui ) {
          $(this).val(ui.item.value);
          $(this).closest("form").submit();
      }
    });

    // Override Fungsi Render Item untuk Custom Tampilan
    ac.autocomplete( "instance" )._renderItem = function( ul, item ) {
        // Ambil kata kunci yang diketik user
        var term = this.term; 
        
        // Buat Regular Expression (Regex) untuk mencari kata kunci (insensitive case)
        // Contoh: mencari "ri" di dalam "Rifki"
        var re = new RegExp( "(" + term + ")", "gi" );
        
        // Ganti kata yang ketemu dengan versi berwarna biru & bold
        var highlightedResult = item.label.replace(re, "<span style='color: #2563eb; font-weight: 800;'>$1</span>");

        return $( "<li>" )
            .append( "<div>" + highlightedResult + "</div>" )
            .appendTo( ul );
    };
  } );
  </script>

</body>
</html>