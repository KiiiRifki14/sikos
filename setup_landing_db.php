require 'inc/koneksi.php';
echo "<h1>Setup Database Landing Page</h1><pre>";

// 1. Table Fasilitas Umum
$sql = "CREATE TABLE IF NOT EXISTS fasilitas_umum (
    id INT AUTO_INCREMENT PRIMARY KEY,
    judul VARCHAR(100) NOT NULL,
    deskripsi TEXT,
    icon VARCHAR(50) DEFAULT 'fa-star'
)";
if ($mysqli->query($sql)) {
    echo "Table 'fasilitas_umum' check/create OK.\n";
} else {
    echo "Error creating table: " . $mysqli->error . "\n";
}

// 2. Insert Default Fasilitas if empty
$cek = $mysqli->query("SELECT COUNT(*) as tot FROM fasilitas_umum");
if ($cek && $cek->fetch_assoc()['tot'] == 0) {
    $stmt = $mysqli->prepare("INSERT INTO fasilitas_umum (judul, deskripsi, icon) VALUES (?, ?, ?)");
    
    $data = [
        ['Keamanan 24 Jam', 'CCTV 24 jam dan sistem akses satu pintu untuk keamanan maksimal penghuni.', 'fa-shield-halved'],
        ['Internet Super Cepat', 'Koneksi Wi-Fi dedicated fiber optic untuk menunjang aktivitas kerja & hiburan.', 'fa-wifi'],
        ['Kamar Mandi Dalam', 'Kenyamanan privasi dengan kamar mandi bersih dan shower di setiap kamar.', 'fa-shower']
    ];
    
    foreach($data as $d) {
        $stmt->bind_param('sss', $d[0], $d[1], $d[2]);
        $stmt->execute();
    }
    echo "Inserted default facilities.\n";
}

// 3. Add Columns to Pengaturan
function add_column_if_not_exists($mysqli, $table, $column, $def) {
    $check = $mysqli->query("SHOW COLUMNS FROM $table LIKE '$column'");
    if ($check->num_rows == 0) {
        if ($mysqli->query("ALTER TABLE $table ADD COLUMN $column $def")) {
            echo "Added column '$column' to '$table'.\n";
        } else {
            echo "Error adding column '$column': " . $mysqli->error . "\n";
        }
    } else {
        echo "Column '$column' already exists.\n";
    }
}

add_column_if_not_exists($mysqli, 'pengaturan', 'no_wa', "VARCHAR(20) DEFAULT '62881011201664'");
add_column_if_not_exists($mysqli, 'pengaturan', 'link_fb', "VARCHAR(100) DEFAULT '#'");
add_column_if_not_exists($mysqli, 'pengaturan', 'link_ig', "VARCHAR(100) DEFAULT '#'");
add_column_if_not_exists($mysqli, 'pengaturan', 'deskripsi_footer', "TEXT");
add_column_if_not_exists($mysqli, 'pengaturan', 'foto_logo', "VARCHAR(255) DEFAULT ''");

// Set default deskripsi footer if empty
$mysqli->query("UPDATE pengaturan SET deskripsi_footer = 'Platform penyewaan kost modern yang mengutamakan kenyamanan dan keamanan penghuni dengan sistem digital yang terintegrasi.' WHERE id=1 AND (deskripsi_footer IS NULL OR deskripsi_footer = '')");

echo "</pre>";
echo "<h3 style='color:green;'>✅ Database Migration Complete!</h3>";
echo "<p>Tabel 'fasilitas_umum' dan kolom 'pengaturan' sudah berhasil dibuat/diupdate.</p>";
echo "<a href='index.php' style='padding:10px 20px; background:blue; color:white; text-decoration:none; border-radius:5px;'>Kembali ke Home</a>";
?>
