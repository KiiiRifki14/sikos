<?php

function is_admin() {
    return isset($_SESSION['peran']) && $_SESSION['peran'] == 'ADMIN';
}

function is_owner() {
    return isset($_SESSION['peran']) && $_SESSION['peran'] == 'OWNER';
}
// --- FUNGSI NOTIFIKASI (FLASH MESSAGE) ---

// Panggil ini sebelum redirect untuk simpan pesan
function set_flash_message($type, $message) {
    // Tipe: 'success' (Hijau), 'error' (Merah), 'warning' (Kuning)
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
}

// Panggil ini di file tujuan (view) untuk menampilkan pesan
function display_flash_message() {
    if (isset($_SESSION['flash_message'])) {
        $msg = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']); // Hapus setelah ditampilkan (sekali pakai)

        // Tentukan warna berdasarkan tipe
        $colorClass = 'bg-blue-100 text-blue-700 border-blue-200'; // Default
        if ($msg['type'] == 'success') $colorClass = 'bg-green-100 text-green-700 border-green-200';
        if ($msg['type'] == 'error')   $colorClass = 'bg-red-100 text-red-700 border-red-200';
        if ($msg['type'] == 'warning') $colorClass = 'bg-yellow-100 text-yellow-700 border-yellow-200';

        echo '
        <div class="'.$colorClass.' border px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">'.$msg['message'].'</span>
        </div>';
    }
}
?>