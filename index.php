<?php
session_start();
include '../koneksi.php';

$sudah_login = isset($_SESSION['login']) && $_SESSION['login'] === true;

$jumlah_keranjang = 0;
if ($sudah_login) {
    $q_cart = mysqli_query($conn, "SELECT SUM(jumlah) AS total FROM tb_keranjang WHERE id_user = '{$_SESSION['id']}'");
    $jumlah_keranjang = mysqli_fetch_assoc($q_cart)['total'] ?? 0;
}

$kategori_list = mysqli_query($conn, "SELECT * FROM tb_kategori");

$filter_kategori = $_GET['kategori'] ?? '';
$kata_kunci = $_GET['cari'] ?? '';

$where = "WHERE p.status = 'aktif'";
if ($filter_kategori) {
    $where .= " AND p.id_kategori = '" . intval($filter_kategori) . "'";
}
if ($kata_kunci) {
    $kata_kunci_aman = mysqli_real_escape_string($conn, $kata_kunci);
    $where .= " AND p.nama LIKE '%$kata_kunci_aman%'";
}

$produk_query = mysqli_query($conn, "
    SELECT p.*, k.nama_kategori,
    (SELECT url_foto FROM tb_foto_produk WHERE id_produk = p.id ORDER BY urutan ASC LIMIT 1) AS foto_utama
    FROM tb_produk p
    LEFT JOIN tb_kategori k ON p.id_kategori = k.id
    $where
    ORDER BY p.id DESC
");

// ============ MENU TERLARIS ============
$terlaris_query = mysqli_query($conn, "
    SELECT p.*, k.nama_kategori, SUM(d.jumlah) AS total_terjual,
    (SELECT url_foto FROM tb_foto_produk WHERE id_produk = p.id ORDER BY urutan ASC LIMIT 1) AS foto_utama
    FROM tb_detail d
    JOIN tb_produk p ON d.id_produk = p.id
    LEFT JOIN tb_kategori k ON p.id_kategori = k.id
    WHERE p.status = 'aktif'
    GROUP BY p.id
    ORDER BY total_terjual DESC
    LIMIT 4
");
$ada_terlaris = mysqli_num_rows($terlaris_query) > 0;

// ============ VOUCHER AKTIF (yang masih berlaku & kuota tersisa) ============
$voucher_query = mysqli_query($conn, "
    SELECT * FROM tb_voucher
    WHERE kuota > 0
    AND CURDATE() BETWEEN tanggal_berlaku AND tanggal_berakhir
    ORDER BY nilai_diskon DESC
");
$ada_voucher = mysqli_num_rows($voucher_query) > 0;

// ============ NOTIFIKASI CUSTOMER (INBOX) ============
$notifikasi_query = null;
$jumlah_belum_dibaca = 0;
if ($sudah_login) {
    $notifikasi_query = mysqli_query($conn, "
        SELECT * FROM tb_notifikasi
        WHERE id_user = '{$_SESSION['id']}'
        ORDER BY tanggal DESC
        LIMIT 15
    ");
    $q_unread = mysqli_query($conn, "SELECT COUNT(*) AS jumlah FROM tb_notifikasi WHERE id_user = '{$_SESSION['id']}' AND dibaca = 0");
    $jumlah_belum_dibaca = mysqli_fetch_assoc($q_unread)['jumlah'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>FrostMart — Belanja Frozen Food</title>
<link rel="stylesheet" href="../assets/css/pelanggan.css">
</head>
<body>

<!-- ============ NAVBAR ============ -->
<header class="navbar">
  <div class="navbar-inner">
    <a href="index.php" class="brand">❄️ FrostMart</a>

    <form method="GET" action="index.php" class="search-bar">
      <input type="text" name="cari" placeholder="Cari nugget, bakso, seafood..." value="<?php echo htmlspecialchars($kata_kunci); ?>">
      <button type="submit">🔍</button>
    </form>

    <div class="navbar-actions">
      <?php if ($sudah_login): ?>
        <!-- ICON INBOX (notifikasi status pesanan & ajakan review) -->
        <div class="notif-wrap">
          <button type="button" class="icon-btn" onclick="toggleDropdown('dropdownInbox')">
            ✉️
            <?php if ($jumlah_belum_dibaca > 0): ?><span class="notif-dot"></span><?php endif; ?>
          </button>
          <div class="dropdown-menu" id="dropdownInbox">
            <div class="dropdown-title">Kotak Masuk</div>
            <?php if ($notifikasi_query && mysqli_num_rows($notifikasi_query) > 0): ?>
              <?php while ($n = mysqli_fetch_assoc($notifikasi_query)): ?>
                <a href="buka_notifikasi.php?id=<?php echo $n['id']; ?>" class="dropdown-item dropdown-item-link <?php echo $n['dibaca'] == 0 ? 'item-belum-dibaca' : ''; ?>">
                  <span class="dropdown-icon"><?php echo $n['dibaca'] == 0 ? '🔵' : '📦'; ?></span>
                  <div>
                    <div class="dropdown-item-title"><?php echo htmlspecialchars($n['judul']); ?></div>
                    <div class="dropdown-item-sub"><?php echo htmlspecialchars($n['pesan']); ?></div>
                  </div>
                </a>
              <?php endwhile; ?>
            <?php else: ?>
              <div class="dropdown-kosong">Belum ada pesan masuk.</div>
            <?php endif; ?>
          </div>
        </div>

        <a href="keranjang.php" class="cart-icon">
          🛒
          <?php if ($jumlah_keranjang > 0): ?>
            <span class="cart-badge"><?php echo $jumlah_keranjang; ?></span>
          <?php endif; ?>
        </a>
       <?php
<a href="profile.php" class="btn-solid">
  👋 Selamat datang, <?php echo htmlspecialchars($_SESSION['nama'] ?? 'Pelanggan', ENT_QUOTES, 'UTF-8'); ?>
</a>
        <a href="../logout.php" class="nav-link">Logout</a>
      <?php else: ?>
        <a href="../login.php" class="btn-outline">Masuk</a>
        <a href="../register.php" class="btn-solid">Daftar</a>
      <?php endif; ?>
    </div>
  </div>
</header>

<!-- ============ HERO CAROUSEL ============ -->
<section class="carousel" id="carousel">
  <div class="carousel-track" id="carouselTrack">
    <div class="carousel-slide slide-1">
      <div class="slide-content">
        <span class="slide-tag">Promo Minggu Ini</span>
        <h1>Diskon 20% Semua Paket Steamboat</h1>
        <p>Nikmati steamboat rumahan lengkap dengan harga spesial.</p>
      </div>
    </div>
    <div class="carousel-slide slide-2">
      <div class="slide-content">
        <span class="slide-tag">Kualitas Terjamin</span>
        <h1>100% Daging & Seafood Pilihan</h1>
        <p>Dibekukan segar, higienis, tanpa pengawet berlebih.</p>
      </div>
    </div>
    <div class="carousel-slide slide-3">
      <div class="slide-content">
        <span class="slide-tag">Pengiriman Cepat</span>
        <h1>Same Day Delivery di Area Kamu</h1>
        <p>Pesan hari ini, sampai hari ini juga — tetap beku sampai rumah.</p>
      </div>
    </div>
  </div>
  <div class="carousel-dots" id="carouselDots"></div>
</section>

<!-- ============ VOUCHER MENCOLOK ============ -->
<?php if ($ada_voucher): ?>
<section class="voucher-section">
  <h2>🎟️ Voucher Spesial Untukmu</h2>
  <div class="voucher-scroll">
    <?php while ($v = mysqli_fetch_assoc($voucher_query)): ?>
      <div class="voucher-card">
        <div class="voucher-left">
          <?php if ($v['jenis_diskon'] == 'persen'): ?>
            <span class="voucher-nilai"><?php echo $v['nilai_diskon']; ?>%</span>
          <?php else: ?>
            <span class="voucher-nilai">Rp<?php echo number_format($v['nilai_diskon']/1000); ?>K</span>
          <?php endif; ?>
          <span class="voucher-label">OFF</span>
        </div>
        <div class="voucher-right">
          <div class="voucher-syarat">Min. belanja Rp <?php echo number_format($v['min_belanja'], 0, ',', '.'); ?></div>
          <div class="voucher-kode-wrap">
            <code class="voucher-kode"><?php echo htmlspecialchars($v['kode_voucher']); ?></code>
            <button type="button" class="voucher-copy" onclick="copyVoucher('<?php echo htmlspecialchars($v['kode_voucher']); ?>', this)">Salin</button>
          </div>
          <div class="voucher-expired">Berlaku s/d <?php echo date('d/m/Y', strtotime($v['tanggal_berakhir'])); ?></div>
        </div>
      </div>
    <?php endwhile; ?>
  </div>
</section>
<?php endif; ?>

<!-- ============ MENU TERLARIS ============ -->
<?php if ($ada_terlaris): ?>
<section class="terlaris-section">
  <h2>🔥 Menu Terlaris</h2>
  <div class="produk-grid">
    <?php while ($p = mysqli_fetch_assoc($terlaris_query)): ?>
      <a href="detail_produk.php?id=<?php echo $p['id']; ?>" class="produk-card">
        <div class="produk-img">
          <?php if ($p['foto_utama']): ?>
            <img src="../<?php echo htmlspecialchars($p['foto_utama']); ?>" alt="<?php echo htmlspecialchars($p['nama']); ?>">
          <?php else: ?>
            <div class="no-img">❄️</div>
          <?php endif; ?>
          <span class="produk-badge badge-terlaris">🔥 <?php echo $p['total_terjual']; ?> terjual</span>
        </div>
        <div class="produk-info">
          <span class="produk-kategori"><?php echo htmlspecialchars($p['nama_kategori'] ?? ''); ?></span>
          <h3><?php echo htmlspecialchars($p['nama']); ?></h3>
          <span class="produk-harga">Rp <?php echo number_format($p['harga'], 0, ',', '.'); ?></span>
        </div>
      </a>
    <?php endwhile; ?>
  </div>
</section>
<?php endif; ?>

<!-- ============ FILTER KATEGORI ============ -->
<section class="kategori-nav">
  <a href="index.php" class="kategori-chip <?php echo !$filter_kategori ? 'active' : ''; ?>">Semua Produk</a>
  <?php mysqli_data_seek($kategori_list, 0); ?>
  <?php while ($k = mysqli_fetch_assoc($kategori_list)): ?>
    <a href="index.php?kategori=<?php echo $k['id']; ?>" class="kategori-chip <?php echo $filter_kategori == $k['id'] ? 'active' : ''; ?>">
      <?php echo htmlspecialchars($k['nama_kategori']); ?>
    </a>
  <?php endwhile; ?>
</section>

<!-- ============ GRID PRODUK ============ -->
<main class="produk-section">
  <?php if ($kata_kunci): ?>
    <p class="hasil-cari">Hasil pencarian untuk "<?php echo htmlspecialchars($kata_kunci); ?>"</p>
  <?php else: ?>
    <h2 style="font-size:18px; margin-bottom:16px;">Semua Produk</h2>
  <?php endif; ?>

  <div class="produk-grid">
    <?php if (mysqli_num_rows($produk_query) == 0): ?>
      <p class="kosong">Produk tidak ditemukan.</p>
    <?php endif; ?>

    <?php while ($p = mysqli_fetch_assoc($produk_query)): ?>
      <?php
        $badge = null;
        if ($p['tanggal_kadaluwarsa']) {
            $sisa_hari = (strtotime($p['tanggal_kadaluwarsa']) - time()) / 86400;
            if ($sisa_hari <= 7) {
                $badge = ['text' => 'Segera Kadaluwarsa', 'class' => 'badge-warning'];
            } elseif ($sisa_hari > 30) {
                $badge = ['text' => 'Fresh Stock', 'class' => 'badge-fresh'];
            }
        }
      ?>
      <a href="detail_produk.php?id=<?php echo $p['id']; ?>" class="produk-card">
        <div class="produk-img">
          <?php if ($p['foto_utama']): ?>
            <img src="../<?php echo htmlspecialchars($p['foto_utama']); ?>" alt="<?php echo htmlspecialchars($p['nama']); ?>">
          <?php else: ?>
            <div class="no-img">❄️</div>
          <?php endif; ?>
          <?php if ($badge): ?>
            <span class="produk-badge <?php echo $badge['class']; ?>"><?php echo $badge['text']; ?></span>
          <?php endif; ?>
          <?php if ($p['stok'] <= 0): ?>
            <span class="produk-badge badge-habis">Stok Habis</span>
          <?php endif; ?>
        </div>
        <div class="produk-info">
          <span class="produk-kategori"><?php echo htmlspecialchars($p['nama_kategori'] ?? ''); ?></span>
          <h3><?php echo htmlspecialchars($p['nama']); ?></h3>
          <span class="produk-harga">Rp <?php echo number_format($p['harga'], 0, ',', '.'); ?></span>
        </div>
      </a>
    <?php endwhile; ?>
  </div>
</main>

<!-- ============ PANDUAN PENYIMPANAN ============ -->
<section class="panduan-section">
  <h2>Panduan Penyimpanan Frozen Food</h2>
  <div class="panduan-grid">
    <div class="panduan-card">
      <span class="panduan-icon">❄️</span>
      <h4>Simpan di Freezer</h4>
      <p>Selalu simpan di suhu -18°C atau lebih rendah untuk menjaga kesegaran maksimal.</p>
    </div>
    <div class="panduan-card">
      <span class="panduan-icon">🚫</span>
      <h4>Jangan Bekukan Ulang</h4>
      <p>Produk yang sudah dicairkan sebaiknya langsung dimasak, jangan dibekukan kembali.</p>
    </div>
    <div class="panduan-card">
      <span class="panduan-icon">📦</span>
      <h4>Simpan dalam Kemasan Rapat</h4>
      <p>Pastikan kemasan tertutup rapat untuk menghindari freezer burn.</p>
    </div>
  </div>
</section>

<footer class="footer">
  <p>❄️ FrostMart — Belanja frozen food jadi lebih mudah.</p>
</footer>

<script>
  const slides = document.querySelectorAll('.carousel-slide');
  const dotsContainer = document.getElementById('carouselDots');
  const track = document.getElementById('carouselTrack');
  let current = 0;

  slides.forEach((_, i) => {
    const dot = document.createElement('span');
    dot.className = 'dot' + (i === 0 ? ' active' : '');
    dot.onclick = () => goToSlide(i);
    dotsContainer.appendChild(dot);
  });

  function goToSlide(index) {
    current = index;
    track.style.transform = `translateX(-${current * 100}%)`;
    document.querySelectorAll('.dot').forEach((d, i) => d.classList.toggle('active', i === current));
  }

  setInterval(() => {
    current = (current + 1) % slides.length;
    goToSlide(current);
  }, 4000);

  // ============ COPY KODE VOUCHER ============
  function copyVoucher(kode, btn) {
    navigator.clipboard.writeText(kode).then(() => {
      const teksAsli = btn.innerText;
      btn.innerText = 'Tersalin!';
      setTimeout(() => { btn.innerText = teksAsli; }, 1500);
    });
  }

  // ============ DROPDOWN NOTIFIKASI ============
  function toggleDropdown(idDropdown) {
    const target = document.getElementById(idDropdown);
    const semuaDropdown = document.querySelectorAll('.dropdown-menu');
    semuaDropdown.forEach(d => {
      if (d.id !== idDropdown) d.classList.remove('show');
    });
    target.classList.toggle('show');
  }

  // Klik di luar dropdown otomatis nutup semua dropdown
  document.addEventListener('click', function(e) {
    if (!e.target.closest('.notif-wrap')) {
      document.querySelectorAll('.dropdown-menu').forEach(d => d.classList.remove('show'));
    }
  });
</script>

</body>
</html>