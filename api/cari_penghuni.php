<?php
// File: api/cari_penghuni.php
require '../inc/koneksi.php';

// 1. Ambil kata kunci yang diketik (dikirim otomatis oleh jQuery UI dengan nama 'term')
$term = isset($_GET['term']) ? $_GET['term'] : '';

$data = [];

if (!empty($term)) {
    // 2. Cari di database (Mirip logika pencarian di admin)
    $term = "%".$term."%";
    $sql = "SELECT u.nama 
            FROM penghuni p 
            JOIN pengguna u ON p.id_pengguna = u.id_pengguna 
            WHERE u.nama LIKE ? 
            LIMIT 10"; // Batasi 10 hasil saja biar tidak kepanjangan
    
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('s', $term);
    $stmt->execute();
    $res = $stmt->get_result();

    // 3. Masukkan ke array
    while ($row = $res->fetch_assoc()) {
        // Format jQuery UI butuh 'label' (tampilan) dan 'value' (isi saat dipilih)
        $data[] = [
            'label' => $row['nama'],
            'value' => $row['nama']
        ];
    }
}

// 4. Kirim balik dalam format JSON
header('Content-Type: application/json');
echo json_encode($data);
?>