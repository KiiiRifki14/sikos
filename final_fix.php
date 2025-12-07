<?php
require 'inc/koneksi.php';
$log = "";

// 1. Create Table
$sql = "CREATE TABLE IF NOT EXISTS fasilitas_umum (
    id INT AUTO_INCREMENT PRIMARY KEY,
    judul VARCHAR(100) NOT NULL,
    deskripsi TEXT,
    icon VARCHAR(50) DEFAULT 'fa-star'
)";

if ($mysqli->query($sql)) {
    $log .= "Table fasilitas_umum OK.\n";
} else {
    $log .= "Error Table: " . $mysqli->error . "\n";
}

// 2. Insert Default Data
$check = $mysqli->query("SELECT COUNT(*) FROM fasilitas_umum");
if ($check && $check->fetch_row()[0] == 0) {
    $mysqli->query("INSERT INTO fasilitas_umum (judul, deskripsi, icon) VALUES ('Keamanan 24 Jam', 'CCTV 24 jam...', 'fa-shield-halved')");
    $log .= "Inserted default data.\n";
}

// 3. Add Columns
$cols = [
    'no_wa' => "VARCHAR(20) DEFAULT '62881011201664'",
    'link_fb' => "VARCHAR(100) DEFAULT '#'",
    'link_ig' => "VARCHAR(100) DEFAULT '#'",
    'deskripsi_footer' => "TEXT",
    'foto_logo' => "VARCHAR(255) DEFAULT ''"
];

foreach ($cols as $col => $def) {
    try {
        $mysqli->query("ALTER TABLE pengaturan ADD COLUMN $col $def");
        $log .= "Tried adding $col.\n";
    } catch (Exception $e) { /* Ignore if exists */ }
}

file_put_contents('status.txt', $log . "DONE");
?>
