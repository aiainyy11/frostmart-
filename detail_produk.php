<?php
session_start();
include '../koneksi.php';

$sudah_login = isset($_SESSION['login']) && $_SESSION['login'] === true;
$id_produk = $_GET['id'] ?? 0;

// Ambil data produk
$q_produk = mysqli_query($conn, "
    SELECT p.*, k.nama_kategori
    FROM tb_produk p
    LEFT JOIN tb_kategori k ON p.id_kategori = k.id
    WHERE p.id = '$id_produk'
");
$produk = mysqli_fetch_assoc($q_produk);

if (!$produk) {
    header("Location: index.php");
    exit;
}

// Ambil semua foto produk ini
$foto_list = mysqli_query($conn, "SELECT * FROM tb_foto_produk WHERE id_produk = '$id_produk' ORDER BY urutan ASC");

// Proses submit komentar
if (isset($_POST['kirim_komentar']) && $sudah_login) {
    $rating = $_POST['rating'];
    $isi = $_POST['isi_komentar'];
    $id_user = $_SESSION['id'];

    // Cek dulu udah pernah komentar di produk ini belum (inget UNIQUE constraint kita)
    $cek = mysqli_query($conn, "SELECT * FROM tb_komentar WHERE id_user='$id_user' AND id_produk='$id_produk'");
    if (mysqli_num_rows($cek) > 0) {
        $error_komentar = "Kamu sudah pernah memberi ulasan untuk produk ini.";
    } else {
        mysqli_query($conn, "INSERT INTO tb_komentar (id_user, id_produk, rating, isi_komentar) VALUES ('$id_user', '$id_produk', '$rating', '$isi')");
        header("Location: detail_produk.php?id=$id_produk#komentar");
        exit;
    }
}

// Ambil semua komentar buat produk ini
$komentar_list = mysqli_query($conn, "
    SELECT km.*, u.nama
    FROM tb_komentar km
    JOIN tb_user u ON km.id_user = u.id
    WHERE km.id_produk = '$id_produk'
    ORDER BY km.tanggal DESC
");

// Hitung rata-rata rating
$q_avg = mysqli_query($conn, "SELECT AVG(rating) AS rata, COUNT(*) AS jumlah FROM tb_komentar WHERE id_produk = '$id_produk'");
$rating_info = mysqli_fetch_assoc($q_avg);

// Hitung jumlah item di keranjang (buat navbar)
$jumlah_keranjang = 0;
if ($sudah_login) {
    $q_cart = mysqli_query($conn, "SELECT SUM(jumlah) AS total FROM tb_keranjang WHERE id_user = '{$_SESSION['id']}'");
    $jumlah_keranjang = mysqli_fetch_assoc($q_cart)['total'] ?? 0;
}

// Badge kadaluwarsa (sama kayak di index)
$badge = null;
if ($produk['tanggal_kadaluwarsa']) {
    $sisa_hari = (strtotime($produk['tanggal_kadaluwarsa']) - time()) / 86400;
    if ($sisa_hari <= 7) {
        $badge = ['text' => 'Segera Kadaluwarsa', 'class' => 'badge-warning'];
    } elseif ($sisa_hari > 30) {
        $badge = ['text' => 'Fresh Stock', 'class' => 'badge-fresh'];
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo htmlspecialchars($produk['nama']); ?> — FrostMart</title>
<link rel="stylesheet" href="../assets/css/pelanggan.css">
<link rel="stylesheet" href="../assets/css/detail.css">
</head>
<body>

<header class="navbar">
  <div class="navbar-inner">
    <a href="index.php" class="brand">❄️ FrostMart</a>
    <form method="GET" action="index.php" class="search-bar">
      <input type="text" name="cari" placeholder="Cari nugget, bakso, seafood...">
      <button type="submit">🔍</button>
    </form>
    <div class="navbar-actions">
      <?php if ($sudah_login): ?>
        <a href="keranjang.php" class="cart-icon">🛒
          <?php if ($jumlah_keranjang > 0): ?><span class="cart-badge"><?php echo $jumlah_keranjang; ?></span><?php endif; ?>
        </a>
        <a href="profile.php" class="nav-link">Profil Saya</a>
        <a href="../logout.php" class="nav-link">Logout</a>
      <?php else: ?>
        <a href="../login.php" class="btn-outline">Masuk</a>
        <a href="../register.php" class="btn-solid">Daftar</a>
      <?php endif; ?>
    </div>
  </div>
</header>

<main class="detail-wrap">
  <a href="index.php" class="back-link">← Kembali ke Produk</a>

  <?php if (isset($_GET['ditambahkan'])): ?>
    <div class="alert-sukses">✅ Produk berhasil ditambahkan ke keranjang!</div>
  <?php endif; ?>

  <div class="detail-grid">
    <!-- GALERI FOTO (CAROUSEL) -->
    <div class="galeri">
      <div class="galeri-carousel">
        <?php if (mysqli_num_rows($foto_list) > 0): ?>
          <div class="galeri-track" id="galeriTrack">
            <?php while ($f = mysqli_fetch_assoc($foto_list)): ?>
              <div class="galeri-slide">
                <img src="../<?php echo htmlspecialchars($f['url_foto']); ?>" alt="<?php echo htmlspecialchars($produk['nama']); ?>">
              </div>
            <?php endwhile; ?>
          </div>
          <button type="button" class="galeri-nav galeri-prev" onclick="geserGaleri(-1)">‹</button>
          <button type="button" class="galeri-nav galeri-next" onclick="geserGaleri(1)">›</button>
        <?php else: ?>
          <div class="no-img-besar">❄️</div>
        <?php endif; ?>
        <?php if ($badge): ?><span class="produk-badge <?php echo $badge['class']; ?>"><?php echo $badge['text']; ?></span><?php endif; ?>
      </div>
      <?php if (mysqli_num_rows($foto_list) > 1): ?>
        <div class="galeri-dots" id="galeriDots"></div>
      <?php endif; ?>
    </div>

    <!-- INFO PRODUK -->
    <div class="info-produk">
      <span class="produk-kategori"><?php echo htmlspecialchars($produk['nama_kategori']); ?> <?php echo $produk['sub_kategori'] ? '· ' . htmlspecialchars($produk['sub_kategori']) : ''; ?></span>
      <h1><?php echo htmlspecialchars($produk['nama']); ?></h1>

      <?php if ($rating_info['jumlah'] > 0): ?>
        <div class="rating-ringkas">⭐ <?php echo number_format($rating_info['rata'], 1); ?> (<?php echo $rating_info['jumlah']; ?> ulasan)</div>
      <?php endif; ?>

      <div class="harga-besar">Rp <?php echo number_format($produk['harga'], 0, ',', '.'); ?></div>

      <p class="deskripsi"><?php echo nl2br(htmlspecialchars($produk['deskripsi'])); ?></p>

      <div class="stok-info">Stok tersedia: <strong><?php echo $produk['stok']; ?></strong></div>

      <?php if ($produk['stok'] > 0): ?>
        <?php if ($sudah_login): ?>
          <form method="POST" action="proses_tambah_keranjang.php" class="form-beli">
            <input type="hidden" name="id_produk" value="<?php echo $produk['id']; ?>">
            <div class="qty-selector">
              <button type="button" onclick="ubahQty(-1)">−</button>
              <input type="number" name="jumlah" id="qtyInput" value="1" min="1" max="<?php echo $produk['stok']; ?>">
              <button type="button" onclick="ubahQty(1)">+</button>
            </div>
            <button type="submit" class="btn-solid btn-besar">Tambah ke Keranjang</button>
          </form>
        <?php else: ?>
          <a href="../login.php" class="btn-solid btn-besar">Masuk untuk Membeli</a>
        <?php endif; ?>
      <?php else: ?>
        <button class="btn-solid btn-besar" disabled style="opacity:0.4;">Stok Habis</button>
      <?php endif; ?>
    </div>
  </div>

  <!-- ============ KOMENTAR / ULASAN ============ -->
  <section class="komentar-section" id="komentar">
    <h2>Ulasan Pelanggan (<?php echo $rating_info['jumlah']; ?>)</h2>

    <?php if ($sudah_login): ?>
      <?php if (isset($error_komentar)): ?>
        <div class="alert-error"><?php echo $error_komentar; ?></div>
      <?php endif; ?>
      <form method="POST" action="detail_produk.php?id=<?php echo $id_produk; ?>" class="form-komentar">
        <div class="rating-input">
          <label>Rating kamu:</label>
          <select name="rating" required>
            <option value="5">⭐⭐⭐⭐⭐ Sangat Puas</option>
            <option value="4">⭐⭐⭐⭐ Puas</option>
            <option value="3">⭐⭐⭐ Cukup</option>
            <option value="2">⭐⭐ Kurang</option>
            <option value="1">⭐ Tidak Puas</option>
          </select>
        </div>
        <textarea name="isi_komentar" placeholder="Bagaimana pendapatmu tentang produk ini?" rows="3" required></textarea>
        <button type="submit" name="kirim_komentar" class="btn-solid">Kirim Ulasan</button>
      </form>
    <?php else: ?>
      <p class="info-login">Silakan <a href="../login.php">masuk</a> untuk memberi ulasan.</p>
    <?php endif; ?>

    <div class="daftar-komentar">
      <?php if (mysqli_num_rows($komentar_list) == 0): ?>
        <p class="kosong">Belum ada ulasan untuk produk ini.</p>
      <?php endif; ?>
      <?php while ($km = mysqli_fetch_assoc($komentar_list)): ?>
        <div class="komentar-item">
          <div class="komentar-head">
            <strong><?php echo htmlspecialchars($km['nama']); ?></strong>
            <span><?php echo str_repeat('⭐', $km['rating']); ?></span>
          </div>
          <p><?php echo htmlspecialchars($km['isi_komentar']); ?></p>
          <span class="komentar-tanggal"><?php echo date('d/m/Y', strtotime($km['tanggal'])); ?></span>
        </div>
      <?php endwhile; ?>
    </div>
  </section>
</main>

<script>
  function ubahQty(delta) {
    const input = document.getElementById('qtyInput');
    let val = parseInt(input.value) + delta;
    const max = parseInt(input.max);
    if (val < 1) val = 1;
    if (val > max) val = max;
    input.value = val;
  }

  // ============ CAROUSEL GALERI FOTO ============
  const galeriTrack = document.getElementById('galeriTrack');
  let slideAktif = 0;

  if (galeriTrack) {
    const slides = galeriTrack.querySelectorAll('.galeri-slide');
    const dotsWrap = document.getElementById('galeriDots');

    if (dotsWrap) {
      slides.forEach((_, i) => {
        const dot = document.createElement('span');
        dot.className = 'dot' + (i === 0 ? ' active' : '');
        dot.onclick = () => pindahKeSlide(i);
        dotsWrap.appendChild(dot);
      });
    }

    function pindahKeSlide(index) {
      const totalSlide = slides.length;
      slideAktif = (index + totalSlide) % totalSlide;
      galeriTrack.style.transform = `translateX(-${slideAktif * 100}%)`;
      if (dotsWrap) {
        dotsWrap.querySelectorAll('.dot').forEach((d, i) => d.classList.toggle('active', i === slideAktif));
      }
    }

    window.geserGaleri = function(arah) {
      pindahKeSlide(slideAktif + arah);
    };
  }
</script>

</body>
</html>