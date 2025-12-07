<?php
require 'inc/koneksi.php';

echo "Attempting to create table fasilitas_umum...\n";

$sql = "CREATE TABLE IF NOT EXISTS fasilitas_umum (
    id INT AUTO_INCREMENT PRIMARY KEY,
    judul VARCHAR(100) NOT NULL,
    deskripsi TEXT,
    icon VARCHAR(50) DEFAULT 'fa-star'
)";

if ($mysqli->query($sql)) {
    echo "SUCCESS: Table 'fasilitas_umum' created.\n";
} else {
    echo "ERROR: " . $mysqli->error . "\n";
}

// Verify it exists
$res = $mysqli->query("SHOW TABLES LIKE 'fasilitas_umum'");
if ($res->num_rows > 0) {
    echo "VERIFIED: Table exists.\n";
} else {
    echo "FAILED: Table still not found.\n";
}

// Add columns just in case
$cols = [
    'no_wa' => "VARCHAR(20) DEFAULT '62881011201664'",
    'link_fb' => "VARCHAR(100) DEFAULT '#'",
    'link_ig' => "VARCHAR(100) DEFAULT '#'",
    'deskripsi_footer' => "TEXT",
    'foto_logo' => "VARCHAR(255) DEFAULT ''"
];

foreach ($cols as $col => $def) {
    echo "Checking column '$col'...\n";
    try {
        $check = $mysqli->query("SHOW COLUMNS FROM pengaturan LIKE '$col'");
        if ($check->num_rows == 0) {
            if ($mysqli->query("ALTER TABLE pengaturan ADD COLUMN $col $def")) {
                echo "Added column '$col'.\n";
            } else {
                echo "Error adding '$col': " . $mysqli->error . "\n";
            }
        } else {
            echo "Column '$col' exists.\n";
        }
    } catch (Exception $e) {
        echo "Exception for '$col': " . $e->getMessage() . "\n";
    }
}
?>
