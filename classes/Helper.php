<?php
class Helper {
    
    // Kita pakai 'static' agar bisa dipanggil tanpa perlu 'new Helper()'
    
    public static function h($string) {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }

    public static function format_rupiah($angka) {
        return "Rp " . number_format($angka, 0, ',', '.');
    }
}
?>