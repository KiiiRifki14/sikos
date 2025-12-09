<?php
// [OOP: Session]
session_start();
require '../inc/koneksi.php';
require '../inc/guard.php';

// [Security]
if (!is_admin()) {
    die('Forbidden');
}

// Menangkap ID (jika mode edit)
$id = intval($_GET['id'] ?? 0);
$data = ['nama_fasilitas' => '', 'icon' => 'fa-star']; // Default value untuk mode Tambah
$title = "Tambah Fasilitas";
$act = "tambah";

// [Logic] Jika ID > 0, berarti ini mode EDIT
if ($id > 0) {
    // Ambil data lama dari DB untuk pre-fill form
    $stmt = $mysqli->prepare("SELECT * FROM fasilitas_master WHERE id_fasilitas = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    if ($res) {
        $data = $res;
        $title = "Edit Fasilitas";
        $act = "edit";
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <title><?= $title ?> - SIKOS Admin</title>

    <link rel="stylesheet" href="../assets/css/app.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="dashboard-body">
    <?php include '../components/sidebar_admin.php'; ?>
    <main class="main-content animate-fade-up">
        <!-- Header -->
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-2xl font-bold text-slate-800"><?= $title ?></h1>
                <p class="text-slate-500 text-sm mt-1">Kelola data fasilitas dengan mudah.</p>
            </div>
            <a href="fasilitas_data.php" class="btn-secondary">
                <i class="fa-solid fa-arrow-left"></i> Kembali
            </a>
        </div>

        <!-- Form Section -->
        <div class="flex justify-center">
            <div class="card-white w-full max-w-2xl">
                <!-- Form Action Dinamis ($act bisa tambah atau edit) -->
                <form action="fasilitas_proses.php?act=<?= $act ?>" method="post">
                    <input type="hidden" name="id_fasilitas" value="<?= $id ?>">

                    <div class="space-y-6 mb-8">
                        <!-- Nama Fasilitas -->
                        <div>
                            <label class="form-label">Nama Fasilitas <span class="text-red-500">*</span></label>
                            <input type="text" name="nama" value="<?= htmlspecialchars($data['nama_fasilitas']) ?>"
                                class="form-input" placeholder="Contoh: Smart TV, Kulkas, dll" required>
                            <p class="text-xs text-slate-500 mt-1">Masukkan nama fasilitas yang tersedia.</p>
                        </div>

                        <!-- Icon Picker Sederhana -->
                        <div>
                            <label class="form-label">Icon FontAwesome <span class="text-red-500">*</span></label>
                            <div class="flex gap-4 items-center">
                                <!-- Box Preview Icon Live -->
                                <div class="w-[60px] h-[60px] bg-slate-50 border-2 border-slate-200 rounded-xl flex items-center justify-center text-primary text-2xl shrink-0 transition-all duration-300 shadow-sm" id="icon-preview-box">
                                    <i id="icon-preview" class="fa-solid <?= $data['icon'] ? $data['icon'] : 'fa-icons' ?>"></i>
                                </div>
                                <div class="flex-1">
                                    <input type="text" name="icon" id="icon-input" value="<?= htmlspecialchars($data['icon']) ?>"
                                        class="form-input" placeholder="Contoh: fa-wifi" required>
                                </div>
                            </div>
                            <p class="text-xs text-slate-500 mt-2">
                                Cari nama icon di <a href="https://fontawesome.com/search?o=r&m=free" target="_blank" class="text-primary font-medium hover:underline">FontAwesome Free</a>, lalu copy nama class-nya (misal: fa-wifi).
                            </p>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex justify-end gap-3 pt-6 border-t border-slate-100">
                        <a href="fasilitas_data.php" class="btn-secondary px-6 rounded-full">
                            <i class="fa-solid fa-times"></i> Batal
                        </a>
                        <button type="submit" class="btn-primary px-8 rounded-full shadow-lg-soft hover:shadow-indigo-500/50 transition-all duration-300 transform hover:-translate-y-1">
                            <i class="fa-solid fa-save"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Script Live Icon Preview -->
        <script>
            const iconInput = document.getElementById('icon-input');
            const iconPreview = document.getElementById('icon-preview');
            const iconBox = document.getElementById('icon-preview-box');

            // [Event Listener] Mendeteksi setiap ketikan user di input icon
            iconInput.addEventListener('keyup', function() {
                const val = this.value.trim();
                // Jika diawali fa-, update preview iconnya
                if (val.startsWith('fa-')) {
                    iconPreview.className = 'fa-solid ' + val;
                    iconBox.classList.add('bg-indigo-50', 'border-indigo-200');
                } else {
                    iconPreview.className = 'fa-solid fa-icons';
                    iconBox.classList.remove('bg-indigo-50', 'border-indigo-200');
                }
            });
        </script>
    </main>
</body>

</html>