// assets/js/main.js

function toggleSidebar() {
    // Ambil elemen body
    const body = document.body;
    
    // Toggle class 'sidebar-collapsed' pada body
    // Class ini akan kita gunakan di CSS untuk mengatur tampilan
    body.classList.toggle('sidebar-collapsed');
    
    // Simpan preferensi user di LocalStorage (agar saat refresh tetap pada posisi terakhir)
    const isCollapsed = body.classList.contains('sidebar-collapsed');
    localStorage.setItem('sidebarState', isCollapsed ? 'collapsed' : 'expanded');
}

// Saat halaman dimuat, cek status terakhir
document.addEventListener('DOMContentLoaded', function() {
    const savedState = localStorage.getItem('sidebarState');
    
    // Jika sebelumnya user menutup sidebar, kita terapkan lagi saat loading
    if (savedState === 'collapsed') {
        document.body.classList.add('sidebar-collapsed');
    }
});
// --- FITUR GLOBAL: ANTI DOUBLE SUBMIT & LOADING STATE ---
document.addEventListener('DOMContentLoaded', function() {
    const forms = document.querySelectorAll('form');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            // Cek validitas HTML5 (biar tombol gak mati kalau input masih kosong)
            if (!this.checkValidity()) return;

            // Cari tombol submit di dalam form ini
            const btn = this.querySelector('button[type="submit"], input[type="submit"]');
            
            if(btn) {
                // Ubah tampilan tombol biar user tau sistem sedang bekerja
                const originalText = btn.innerHTML || btn.value;
                const loadingText = '<i class="fa-solid fa-spinner fa-spin"></i> Memproses...';
                
                if(btn.tagName === 'INPUT') {
                    btn.value = 'Memproses...';
                } else {
                    // Simpan lebar asli biar tombol gak mengecil aneh
                    btn.style.width = getComputedStyle(btn).width;
                    btn.innerHTML = loadingText;
                }
                
                btn.style.opacity = '0.7';
                btn.style.cursor = 'wait';
                
                // Disable tombol setelah submit terkirim (kasih delay dikit biar event submit jalan dulu)
                setTimeout(() => {
                    btn.disabled = true;
                }, 50);
            }
        });
    });
});