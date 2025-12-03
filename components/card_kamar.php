<?php
/**
 * Props: $row (assoc kamar + tipe)
 */
?>
<div class="card-white" style="padding:0; overflow:hidden; display:flex; flex-direction:column; height:100%; transition: transform 0.2s;">
  
  <div style="position:relative; height:220px; background-color:#f1f5f9; overflow:hidden;">
    <?php if(!empty($row['foto_cover'])): ?>
      <img src="assets/uploads/kamar/<?= htmlspecialchars($row['foto_cover']) ?>" 
           alt="Kamar <?= htmlspecialchars($row['kode_kamar']) ?>" 
           style="width:100%; height:100%; object-fit:cover;">
    <?php else: ?>
      <div style="width:100%; height:100%; display:flex; align-items:center; justify-content:center; font-size:3rem; color:#cbd5e1;">
        <i class="fa-solid fa-house"></i>
      </div>
    <?php endif; ?>

    <div style="position:absolute; top:10px; right:10px;">
      <?php if($row['status_kamar'] === 'TERSEDIA'): ?>
        <span style="background:#dcfce7; color:#166534; padding:4px 10px; border-radius:20px; font-size:10px; font-weight:bold; border:1px solid #bbf7d0;">
          ✓ Tersedia
        </span>
      <?php else: ?>
        <span style="background:#fee2e2; color:#991b1b; padding:4px 10px; border-radius:20px; font-size:10px; font-weight:bold; border:1px solid #fecaca;">
          ✕ Terisi
        </span>
      <?php endif; ?>
    </div>
  </div>

  <div style="padding:20px; flex:1; display:flex; flex-direction:column;">
    <div style="font-size:10px; font-weight:bold; color:var(--primary); text-transform:uppercase; letter-spacing:1px; margin-bottom:4px;">
        <?= htmlspecialchars($row['nama_tipe']) ?>
    </div>
    
    <h3 style="font-size:18px; font-weight:bold; margin-bottom:8px; color:var(--text-main);">
        Kamar <?= htmlspecialchars($row['kode_kamar']) ?>
    </h3>

    <div style="margin-bottom:16px;">
      <span style="font-size:20px; font-weight:800; color:var(--primary);">
        Rp <?= number_format((int)$row['harga'], 0, ',', '.') ?>
      </span>
      <span style="font-size:12px; color:var(--text-muted);">/bulan</span>
    </div>

    <div style="display:flex; gap:8px; margin-bottom:20px; flex-wrap:wrap;">
      <span style="background:#f8fafc; border:1px solid var(--border); padding:4px 8px; border-radius:6px; font-size:11px; color:var(--text-muted);">
        <i class="fa-solid fa-ruler-combined"></i> <?= htmlspecialchars($row['luas_m2']) ?> m²
      </span>
      <span style="background:#f8fafc; border:1px solid var(--border); padding:4px 8px; border-radius:6px; font-size:11px; color:var(--text-muted);">
        <i class="fa-solid fa-stairs"></i> Lt. <?= htmlspecialchars($row['lantai']) ?>
      </span>
    </div>

    <div style="margin-top:auto;">
        <?php if($row['status_kamar'] === 'TERSEDIA'): ?>
          <a href="detail_kamar.php?id=<?= (int)$row['id_kamar'] ?>" class="btn btn-primary" style="width:100%; text-align:center; justify-content:center;">
            Lihat Detail & Pesan  
          </a>
        <?php else: ?>
          <button disabled class="btn" style="width:100%; background:#f1f5f9; color:#94a3b8; cursor:not-allowed; justify-content:center;">
            Tidak Tersedia
          </button>
        <?php endif; ?>
    </div>
  </div>
</div>