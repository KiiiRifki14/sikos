<nav class="navbar">
  <div class="navbar-container">
    <a href="index.php" class="nav-brand">
      <div class="nav-brand-logo">S</div>
      <div style="display:flex; flex-direction:column;">
        <span style="font-weight:800; font-size:20px; line-height:1;">SIKOS</span>
        <span style="font-size:10px; font-weight:bold; color:var(--text-muted); letter-spacing:1px;">PAADAASIH</span>
      </div>
    </a>

    <div class="nav-menu">
      <a href="index.php#beranda" class="nav-link">Beranda</a>
      <a href="index.php#kamar" class="nav-link">Kamar</a>
      <a href="index.php#fasilitas" class="nav-link">Fasilitas</a>

      <div style="height:24px; width:1px; background:var(--border);"></div>

      <?php if (isset($_SESSION['id_pengguna'])): ?>
        <?php $dash = ($_SESSION['peran'] == 'PENGHUNI') ? 'penghuni_dashboard.php' : 'admin/index.php'; ?>
        <a href="<?= $dash ?>" class="btn btn-primary" style="padding: 8px 20px;">Dashboard</a>
        <a href="logout.php" class="btn btn-danger" style="padding: 8px 16px;">Logout</a>
      <?php else: ?>
        <a href="login.php" class="btn btn-secondary" style="padding: 8px 20px;">Login</a>
      <?php endif; ?>
    </div>

    <button class="mobile-menu-btn" onclick="document.getElementById('mobileMenu').classList.toggle('active')">
        <i class="fa-solid fa-bars"></i>
    </button>
  </div>

  <div id="mobileMenu" class="mobile-menu">
      <a href="index.php#beranda" class="nav-link" onclick="this.parentElement.classList.remove('active')">Beranda</a>
      <a href="index.php#kamar" class="nav-link" onclick="this.parentElement.classList.remove('active')">Kamar</a>
      <a href="index.php#fasilitas" class="nav-link" onclick="this.parentElement.classList.remove('active')">Fasilitas</a>
      <div style="height:1px; width:100%; background:var(--border);"></div>
      
      <?php if (isset($_SESSION['id_pengguna'])): ?>
        <a href="<?= $dash ?>" class="btn btn-primary w-full text-center">Dashboard</a>
        <a href="logout.php" class="btn btn-danger w-full text-center">Logout</a>
      <?php else: ?>
        <a href="login.php" class="btn btn-primary w-full text-center">Login Member</a>
      <?php endif; ?>
  </div>
</nav>