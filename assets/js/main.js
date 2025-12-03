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