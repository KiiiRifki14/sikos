<?php
require_once 'inc/koneksi.php';

// Ambil parameter dari URL
$offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;
$limit  = 6; // Jumlah kamar yang diambil per klik

// Logic Filter (Harus sama persis dengan index.php agar hasil konsisten)
$where = [];
$params = [];
$types = '';

if (!empty($_GET['status'])) {
    $status = $_GET['status'] === 'TERSEDIA' ? 'TERSEDIA' : ($_GET['status'] === 'TERISI' ? 'TERISI' : null);
    if ($status) {
        $where[] = "k.status_kamar = ?";
        $params[] = $status;
        $types .= 's';
    }
}
if (!empty($_GET['tipe'])) {
    $where[] = "k.id_tipe = ?";
    $params[] = (int)$_GET['tipe'];
    $types .= 'i';
}
if (!empty($_GET['max_harga'])) {
    $where[] = "k.harga <= ?";
    $params[] = (int)$_GET['max_harga'];
    $types .= 'i';
}

// Logic Urutan
$order_param = $_GET['order'] ?? 'terbaru';
$order_sql = "k.status_kamar ASC, k.kode_kamar ASC";
if ($order_param === 'harga_asc') {
    $order_sql = "k.harga ASC";
} elseif ($order_param === 'harga_desc') {
    $order_sql = "k.harga DESC";
} elseif ($order_param === 'terbaru') {
    $order_sql = "k.id_kamar DESC";
}

// Query Utama dengan LIMIT & OFFSET
// Query Utama dengan LIMIT & OFFSET
// MODIFIKASI: Filter kamar yang sedang PENDING booking
$sql = "SELECT k.*, t.nama_tipe 
        FROM kamar k 
        JOIN tipe_kamar t ON k.id_tipe=t.id_tipe
        WHERE k.id_kamar NOT IN (SELECT id_kamar FROM booking WHERE status='PENDING')";

if ($where) $sql .= " AND " . implode(" AND ", $where); // Ganti WHERE jadi AND
$sql .= " ORDER BY " . $order_sql;
$sql .= " LIMIT ? OFFSET ?";

// Tambahkan limit & offset ke parameter binding
$params[] = $limit;
$params[] = $offset;
$types .= 'ii';

// Eksekusi
$stmt = $mysqli->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$res = $stmt->get_result();

// Render Kartu Kamar (Menggunakan komponen yang sama)
if ($res->num_rows > 0) {
    while ($row = $res->fetch_assoc()) {
        include 'components/card_kamar.php';
    }
}
?>