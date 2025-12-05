<?php
// FILE: generate_hash.php
// File ini cuma buat bantu kamu generate password hash untuk dimasukkan ke DB manual

if (isset($_POST['password'])) {
    $password_asli = $_POST['password'];
    // Ini algoritma yang sama dengan sistem Login SIKOS kamu
    $hash = password_hash($password_asli, PASSWORD_DEFAULT);
}
?>

<!DOCTYPE html>
<html>
<body>
    <h3>Generator Password Hash SIKOS</h3>
    <form method="post">
        Password yang mau dibuat: <input type="text" name="password" required>
        <button type="submit">Generate Hash</button>
    </form>

    <?php if (isset($hash)) : ?>
        <hr>
        <p>Password Asli: <b><?= htmlspecialchars($password_asli) ?></b></p>
        <p>Hash (Copy kode aneh ini ke kolom <b>password_hash</b> di tabel <b>pengguna</b>):</p>
        <textarea style="width: 100%; height: 100px;"><?= $hash ?></textarea>
    <?php endif; ?>
</body>
</html>