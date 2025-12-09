<?php
session_start();
require 'inc/koneksi.php';
require 'inc/csrf.php';

if (!isset($_SESSION['id_pengguna'])) {
    header('Location: login.php');
    exit;
}
$id_pengguna = $_SESSION['id_pengguna'];

// [REFACTOR]
$user = $db->get_profil_penghuni($id_pengguna);
$foto_url = !empty($user['foto_profil']) ? "assets/uploads/profil/" . $user['foto_profil'] : "assets/img/avatar.png";
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Edit Profil</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link rel="stylesheet" href="assets/css/app.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="role-penghuni">
    <?php include 'components/sidebar_penghuni.php'; ?>
    <main class="main-content animate-fade-up">

        <div style="margin-bottom: 25px;">
            <h1 class="text-xl font-bold text-main">Edit Profil</h1>
            <p class="text-sm text-muted">Perbarui informasi pribadi dan keamanan akun Anda.</p>
        </div>

        <form action="penghuni_proses.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="act" value="update_profil">
            <input type="hidden" name="csrf" value="<?= csrf_token() ?>">

            <div class="grid-profile">
                <!-- SIDE LEFT: FOTO -->
                <div class="card-white text-center h-fit">
                    <div style="width: 120px; height: 120px; margin: 0 auto 20px; position: relative;">
                        <img id="preview" src="<?= $foto_url ?>" style="width:100%; height:100%; object-fit:cover; border-radius:50%; border:4px solid #f8fafc; box-shadow:0 4px 6px -1px rgba(0,0,0,0.1);">
                        <label style="position:absolute; bottom:0; right:0; background:var(--primary); color:white; width:36px; height:36px; border-radius:50%; display:flex; align-items:center; justify-content:center; cursor:pointer; border:3px solid white; transition:transform 0.2s;" onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'">
                            <i class="fa-solid fa-camera"></i>
                            <input type="file" name="foto_profil" style="display:none;" accept="image/*" onchange="loadFile(event)">
                        </label>
                    </div>
                    <h3 class="font-bold text-lg text-main mb-1"><?= htmlspecialchars($user['nama']) ?></h3>
                    <span style="font-size:12px; background:#e0f2fe; color:#0369a1; padding:4px 12px; border-radius:15px; font-weight:600;">Penghuni Kost</span>
                </div>

                <!-- SIDE RIGHT: FORM -->
                <div class="card-white">
                    <h4 class="font-bold text-main border-b border-gray-100 pb-3 mb-6">Informasi Pribadi</h4>

                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div class="form-group">
                            <label class="form-label">Nama Lengkap</label>
                            <input type="text" name="nama" value="<?= htmlspecialchars($user['nama']) ?>" class="form-input" maxlength="30" oninput="validateName(this)" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">No. Handphone</label>
                            <input type="tel" name="no_hp" value="<?= htmlspecialchars($user['no_hp']) ?>" class="form-input" maxlength="17" oninput="validatePhone(this)" required>
                        </div>
                    </div>

                    <div class="form-group mb-4">
                        <label class="form-label">Alamat Asal</label>
                        <textarea name="alamat" class="form-input" rows="2"><?= htmlspecialchars($user['alamat']) ?></textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-6">
                        <div class="form-group">
                            <label class="form-label">Pekerjaan</label>
                            <input type="text" name="pekerjaan" value="<?= htmlspecialchars($user['pekerjaan']) ?>" class="form-input">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Kontak Darurat</label>
                            <input type="text" name="emergency_cp" value="<?= htmlspecialchars($user['emergency_cp']) ?>" class="form-input" placeholder="Nama - No HP">
                        </div>
                    </div>

                    <h4 class="font-bold text-main border-b border-gray-100 pb-3 mb-6 mt-8">Keamanan Akun</h4>

                    <div class="grid grid-cols-2 gap-4 mb-6">
                        <div class="form-group">
                            <label class="form-label">Password Baru</label>
                            <input type="password" name="pass_baru" class="form-input" placeholder="Kosongkan jika tidak diganti">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Ulangi Password</label>
                            <input type="password" name="pass_konfirm" class="form-input" placeholder="Ketik ulang password baru">
                        </div>
                    </div>

                    <div class="text-right pt-4 border-t border-gray-50">
                        <button type="submit" class="btn btn-primary" style="padding:12px 40px; border-radius:12px;">
                            <i class="fa-solid fa-check mr-2"></i> Simpan Perubahan
                        </button>
                    </div>
                </div>
            </div>
        </form>

        <style>
            .grid-profile {
                display: grid;
                grid-template-columns: 300px 1fr;
                gap: 30px;
            }

            .h-fit {
                height: fit-content;
            }

            @media (max-width: 900px) {
                .grid-profile {
                    grid-template-columns: 1fr;
                }

            }
        </style>

    </main>
    <script>
        var loadFile = function(event) {
            var output = document.getElementById('preview');
            if (event.target.files && event.target.files[0]) {
                output.src = URL.createObjectURL(event.target.files[0]);
            }
        };

        function validateName(input) {
            // Hanya huruf dan spasi
            input.value = input.value.replace(/[^a-zA-Z\s]/g, '');
        }

        function validatePhone(input) {
            // Hanya angka
            input.value = input.value.replace(/[^0-9]/g, '');
        }
    </script>
</body>

</html>