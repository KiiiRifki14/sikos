<?php
/**
 * Props: $row (assoc kamar + tipe)
 */
?>
<div class="card-white group hover:shadow-xl hover:-translate-y-1 transition duration-300">
  <div class="h-60 bg-slate-100 relative overflow-hidden">
    <?php if(!empty($row['foto_cover'])): ?>
      <img src="assets/uploads/kamar/<?= htmlspecialchars($row['foto_cover']) ?>" alt="Foto Kamar <?= htmlspecialchars($row['kode_kamar']) ?>" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
    <?php else: ?>
      <div class="w-full h-full flex items-center justify-center text-4xl text-slate-300" aria-label="Ikon kamar">🏠</div>
    <?php endif; ?>

    <div class="absolute top-4 right-4">
      <?php if($row['status_kamar'] === 'TERSEDIA'): ?>
        <span class="px-2 py-1 text-xs rounded-full font-semibold bg-green-100 text-green-700">✓ Tersedia</span>
      <?php else: ?>
        <span class="px-2 py-1 text-xs rounded-full font-semibold bg-red-100 text-red-700">✕ Terisi</span>
      <?php endif; ?>
    </div>
  </div>

  <div class="p-6">
    <div class="text-xs font-bold text-blue-600 uppercase tracking-wider mb-1"><?= htmlspecialchars($row['nama_tipe']) ?></div>
    <h3 class="text-xl font-bold text-slate-900 mb-2">Kamar <?= htmlspecialchars($row['kode_kamar']) ?></h3>

    <div class="flex items-baseline gap-1 mb-4">
      <span class="text-2xl font-bold text-blue-600">Rp <?= number_format((int)$row['harga'], 0, ',', '.') ?></span>
      <span class="text-sm text-slate-400">/bulan</span>
    </div>

    <div class="flex gap-3 text-xs text-slate-600 mb-6">
      <span class="bg-slate-50 px-2 py-1 rounded border border-slate-100">📐 <?= htmlspecialchars($row['luas_m2']) ?> m²</span>
      <span class="bg-slate-50 px-2 py-1 rounded border border-slate-100">⚡ Listrik</span>
      <span class="bg-slate-50 px-2 py-1 rounded border border-slate-100">🚿 Dalam</span>
    </div>

    <?php if($row['status_kamar'] === 'TERSEDIA'): ?>
      <a href="detail_kamar.php?id=<?= (int)$row['id_kamar'] ?>" class="block w-full bg-blue-600 hover:bg-blue-700 text-white text-center py-3 rounded-xl font-bold transition">
        Lihat Detail & Pesan
      </a>
    <?php else: ?>
      <button disabled class="block w-full bg-slate-100 text-slate-400 text-center py-3 rounded-xl font-bold cursor-not-allowed">
        Tidak Tersedia
      </button>
    <?php endif; ?>
  </div>
</div>