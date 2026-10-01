<?php
include '../includes/cek_login.php';
include '../koneksi.php';

$id_user = $_SESSION['id'];

// Update qty
if (isset($_POST['update_qty'])) {
    $id_keranjang = $_POST['id_keranjang'];
    $jumlah_baru = max(1, intval($_POST['jumlah']));
    mysqli_query($conn, "UPDATE tb_keranjang SET jumlah='$jumlah_baru' WHERE id='$id_keranjang' AND id_user='$id_user'");
    header("Location: keranjang.php");
    exit;
}

// Hapus item
if (isset($_GET['hapus'])) {
    $id_keranjang = $_GET['hapus'];
    mysqli_query($conn, "DELETE FROM tb_keranjang WHERE id='$id_keranjang' AND id_user='$id_user'");
    header("Location: keranjang.php");
    exit;
}

// Ambil isi keranjang
$keranjang = mysqli_query($conn, "
    SELECT ke.*, p.nama, p.harga, p.stok,
    (SELECT url_foto FROM tb_foto_produk WHERE id_produk = p.id ORDER BY urutan ASC LIMIT 1) AS foto
    FROM tb_keranjang ke
    JOIN tb_produk p ON ke.id_produk = p.id
    WHERE ke.id_user = '$id_user'
");

$items = [];
while ($row = mysqli_fetch_assoc($keranjang)) {
    $row['subtotal'] = $row['harga'] * $row['jumlah'];
    $items[] = $row;
}

$jumlah_keranjang = count($items);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Keranjang — FrostMart</title>
<link rel="stylesheet" href="../assets/css/pelanggan.css">
<link rel="stylesheet" href="../assets/css/keranjang.css">
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
      <a href="keranjang.php" class="cart-icon">🛒
        <?php if ($jumlah_keranjang > 0): ?><span class="cart-badge"><?php echo $jumlah_keranjang; ?></span><?php endif; ?>
      </a>
      <a href="profile.php" class="nav-link">Profil Saya</a>
      <a href="../logout.php" class="nav-link">Logout</a>
    </div>
  </div>
</header>

<main class="keranjang-wrap">
  <h1>Keranjang Belanja</h1>

  <?php if (empty($items)): ?>
    <div class="keranjang-kosong">
      <p>Keranjang kamu masih kosong.</p>
      <a href="index.php" class="btn-solid">Mulai Belanja</a>
    </div>
  <?php else: ?>

    <form method="POST" action="checkout.php" id="formKeranjang">
      <div class="keranjang-grid">
        <!-- DAFTAR ITEM -->
        <div class="keranjang-items">
          <label class="pilih-semua">
            <input type="checkbox" id="pilihSemua" onclick="toggleSemua(this)">
            Pilih Semua
          </label>

          <?php foreach ($items as $item): ?>
            <div class="item-card">
              <input type="checkbox" name="item_ids[]" value="<?php echo $item['id']; ?>"
                     class="item-checkbox" data-subtotal="<?php echo $item['subtotal']; ?>"
                     onchange="hitungTotal()">
              <div class="item-img">
                <?php if ($item['foto']): ?>
                  <img src="../<?php echo htmlspecialchars($item['foto']); ?>" alt="">
                <?php else: ?>
                  <div class="no-img">❄️</div>
                <?php endif; ?>
              </div>
              <div class="item-info">
                <h3><?php echo htmlspecialchars($item['nama']); ?></h3>
                <span class="item-harga">Rp <?php echo number_format($item['harga'], 0, ',', '.'); ?> / item</span>
              </div>
              <form method="POST" action="keranjang.php" class="item-qty">
                <input type="hidden" name="id_keranjang" value="<?php echo $item['id']; ?>">
                <input type="number" name="jumlah" value="<?php echo $item['jumlah']; ?>" min="1" max="<?php echo $item['stok']; ?>" onchange="this.form.submit()">
                <button type="submit" name="update_qty" style="display:none;"></button>
              </form>
              <div class="item-subtotal">Rp <?php echo number_format($item['subtotal'], 0, ',', '.'); ?></div>
              <a href="keranjang.php?hapus=<?php echo $item['id']; ?>" class="item-hapus" onclick="return confirm('Hapus produk ini dari keranjang?')">🗑️</a>
            </div>
          <?php endforeach; ?>
        </div>

        <!-- RINGKASAN -->
        <div class="ringkasan-box">
          <h2>Ringkasan</h2>
          <div id="pesanKosong" class="pesan-pilih">Pilih minimal 1 produk untuk checkout.</div>

          <div id="ringkasanIsi" style="display:none;">
            <div class="baris-ringkasan">
              <span id="jumlahTerpilih">0 item dipilih</span>
              <span id="totalTerpilih">Rp 0</span>
            </div>
            <button type="submit" class="btn-solid btn-checkout">Checkout</button>
          </div>
        </div>
      </div>
    </form>

  <?php endif; ?>
</main>

<script>
  function toggleSemua(checkboxSemua) {
    document.querySelectorAll('.item-checkbox').forEach(cb => cb.checked = checkboxSemua.checked);
    hitungTotal();
  }

  function hitungTotal() {
    const checkboxes = document.querySelectorAll('.item-checkbox:checked');
    let total = 0;
    checkboxes.forEach(cb => total += parseInt(cb.dataset.subtotal));

    const pesanKosong = document.getElementById('pesanKosong');
    const ringkasanIsi = document.getElementById('ringkasanIsi');

    if (checkboxes.length > 0) {
      pesanKosong.style.display = 'none';
      ringkasanIsi.style.display = 'block';
      document.getElementById('jumlahTerpilih').innerText = checkboxes.length + ' item dipilih';
      document.getElementById('totalTerpilih').innerText = 'Rp ' + total.toLocaleString('id-ID');
    } else {
      pesanKosong.style.display = 'block';
      ringkasanIsi.style.display = 'none';
    }
  }
</script>

</body>
</html>