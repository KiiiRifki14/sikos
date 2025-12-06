<?php
/**
 * Props: $row (assoc kamar + tipe)
 */
?>
<div class="room-card animate-fade-up">
  
  <div class="room-img-wrapper">
    <?php if(!empty($row['foto_cover'])): ?>
      <img src="assets/uploads/kamar/<?= htmlspecialchars($row['foto_cover']) ?>" 
           alt="Kamar <?= htmlspecialchars($row['kode_kamar']) ?>" 
           class="room-img">
    <?php else: ?>
      <div style="width:100%; height:100%; display:flex; align-items:center; justify-content:center; background:#f1f5f9; color:#cbd5e1;">
        <i class="fa-solid fa-house" style="font-size:3rem;"></i>
      </div>
    <?php endif; ?>

    <div class="room-tag <?= ($row['status_kamar'] === 'TERSEDIA') ? 'tag-avail' : 'tag-booked' ?>">
      <?= ($row['status_kamar'] === 'TERSEDIA') ? 'Tersedia' : 'Terisi' ?>
    </div>
  </div>

  <div class="room-body">
    <div class="room-type">
        <?= htmlspecialchars($row['nama_tipe']) ?>
    </div>
    
    <h3 class="room-title">
        Kamar <?= htmlspecialchars($row['kode_kamar']) ?>
    </h3>

    <div class="room-specs">
      <span><i class="fa-solid fa-ruler-combined"></i> <?= htmlspecialchars($row['luas_m2']) ?> m²</span>
      <span><i class="fa-solid fa-stairs"></i> Lt. <?= htmlspecialchars($row['lantai']) ?></span>
    </div>

    <div class="room-footer">
        <div class="room-price">
            Rp <?= number_format((int)$row['harga'], 0, ',', '.') ?><span>/bln</span>
        </div>
        
        <?php if($row['status_kamar'] === 'TERSEDIA'): ?>
          <a href="detail_kamar.php?id=<?= (int)$row['id_kamar'] ?>" class="btn btn-primary" style="padding: 8px 16px; font-size:13px;">
            Lihat Detail & Pesan
          </a>
        <?php else: ?>
          <button disabled class="btn btn-secondary" style="padding: 8px 16px; font-size:13px; opacity:0.6; cursor:not-allowed;">
            Penuh
          </button>
        <?php endif; ?>
    </div>
  </div>
</div>