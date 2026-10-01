<?php
include '../includes/cek_login.php';
include '../koneksi.php';

$id_user = $_SESSION['id'];
$item_ids = $_POST['item_ids'] ?? [];

if (empty($item_ids)) {
    header("Location: keranjang.php");
    exit;
}

// Bikin string list id yang aman buat query IN(...)
$item_ids_aman = array_map('intval', $item_ids);
$id_list_sql = implode(',', $item_ids_aman);

// Ambil detail item yang dipilih doang
$items_query = mysqli_query($conn, "
    SELECT ke.*, p.nama, p.harga,
    (SELECT url_foto FROM tb_foto_produk WHERE id_produk = p.id ORDER BY urutan ASC LIMIT 1) AS foto
    FROM tb_keranjang ke
    JOIN tb_produk p ON ke.id_produk = p.id
    WHERE ke.id IN ($id_list_sql) AND ke.id_user = '$id_user'
");

$items = [];
$grand_total = 0;
while ($row = mysqli_fetch_assoc($items_query)) {
    $row['subtotal'] = $row['harga'] * $row['jumlah'];
    $grand_total += $row['subtotal'];
    $items[] = $row;
}

if (empty($items)) {
    header("Location: keranjang.php");
    exit;
}

// Ambil data profil buat prefill alamat & no HP
$data_user = mysqli_query($conn, "SELECT * FROM tb_user WHERE id='$id_user'");
$user = mysqli_fetch_assoc($data_user);

// Ambil voucher yang masih aktif & memenuhi syarat minimal belanja
$voucher_query = mysqli_query($conn, "
    SELECT * FROM tb_voucher
    WHERE kuota > 0
    AND CURDATE() BETWEEN tanggal_berlaku AND tanggal_berakhir
    AND min_belanja <= '$grand_total'
    ORDER BY nilai_diskon DESC
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Checkout — FrostMart</title>
<link rel="stylesheet" href="../assets/css/pelanggan.css">
<link rel="stylesheet" href="../assets/css/keranjang.css">
</head>
<body>

<header class="navbar">
  <div class="navbar-inner">
    <a href="index.php" class="brand">❄️ FrostMart</a>
    <div class="navbar-actions">
      <a href="profile.php" class="nav-link">Profil Saya</a>
      <a href="../logout.php" class="nav-link">Logout</a>
    </div>
  </div>
</header>

<main class="keranjang-wrap">
  <a href="keranjang.php" class="back-link">← Kembali ke Keranjang</a>
  <h1>Checkout</h1>

  <form method="POST" action="proses_checkout.php">
    <!-- Kirim ulang item_ids yang dipilih tadi, biar proses_checkout.php tau produk mana aja -->
    <?php foreach ($item_ids_aman as $iid): ?>
      <input type="hidden" name="item_ids[]" value="<?php echo $iid; ?>">
    <?php endforeach; ?>

    <div class="keranjang-grid">
      <div class="keranjang-items">

        <!-- RINGKASAN PRODUK YANG DIPILIH -->
        <div class="ringkasan-box" style="margin-bottom:16px;">
          <h2>Produk Dipesan</h2>
          <?php foreach ($items as $item): ?>
            <div class="baris-ringkasan">
              <span><?php echo htmlspecialchars($item['nama']); ?> × <?php echo $item['jumlah']; ?></span>
              <span>Rp <?php echo number_format($item['subtotal'], 0, ',', '.'); ?></span>
            </div>
          <?php endforeach; ?>
        </div>

        <!-- FORM ALAMAT & TELPON -->
        <div class="ringkasan-box">
          <h2>Info Pengiriman</h2>
          <div class="field">
            <label>Alamat Pengiriman</label>
            <input type="text" name="alamat_kirim" value="<?php echo htmlspecialchars($user['alamat']); ?>" required>
          </div>
          <div class="field">
            <label>Nomor Telepon</label>
            <input type="text" name="no_telp" value="<?php echo htmlspecialchars($user['hp']); ?>" required>
          </div>
          <div class="field">
            <label>Kode Voucher</label>
            <?php if (mysqli_num_rows($voucher_query) > 0): ?>
              <div class="voucher-picker">
                <?php while ($v = mysqli_fetch_assoc($voucher_query)): ?>
                  <div class="voucher-chip" data-kode="<?php echo htmlspecialchars($v['kode_voucher']); ?>" onclick="pilihVoucher(this)">
                    <span class="chip-nilai">
                      <?php echo $v['jenis_diskon'] == 'persen' ? $v['nilai_diskon'] . '%' : 'Rp' . number_format($v['nilai_diskon']/1000) . 'K'; ?> OFF
                    </span>
                    <span class="chip-kode"><?php echo htmlspecialchars($v['kode_voucher']); ?></span>
                  </div>
                <?php endwhile; ?>
              </div>
            <?php else: ?>
              <p style="font-size:12.5px; color:var(--frost-dim);">Tidak ada voucher yang berlaku untuk belanjaan ini.</p>
            <?php endif; ?>
            <input type="text" name="kode_voucher" id="kodeVoucherInput" placeholder="Atau masukkan kode manual">
          </div>
          <div class="field">
            <label>Metode Pembayaran</label>
            <select name="metode_bayar" required>
              <option value="Transfer Bank">Transfer Bank</option>
              <option value="COD">COD (Bayar di Tempat)</option>
              <option value="E-Wallet">E-Wallet</option>
            </select>
          </div>
          <div class="field">
            <label>Metode Pengiriman</label>
            <select name="metode_kirim" required>
              <option value="Reguler">Reguler (2-3 hari)</option>
              <option value="Same Day">Same Day (Hari ini)</option>
              <option value="Ambil Sendiri">Ambil di Toko</option>
            </select>
          </div>
        </div>
      </div>

      <!-- TOTAL & TOMBOL FINAL -->
      <div class="ringkasan-box">
        <h2>Total Bayar</h2>
        <div class="baris-ringkasan baris-total">
          <span>Total (<?php echo count($items); ?> item)</span>
          <span>Rp <?php echo number_format($grand_total, 0, ',', '.'); ?></span>
        </div>
        <button type="submit" class="btn-solid btn-checkout">Buat Pesanan</button>
      </div>
    </div>
  </form>
</main>

<script>
  function pilihVoucher(chip) {
    // Hapus highlight dari semua chip dulu
    document.querySelectorAll('.voucher-chip').forEach(c => c.classList.remove('dipilih'));
    // Highlight chip yang baru diklik
    chip.classList.add('dipilih');
    // Isi otomatis ke input kode voucher
    document.getElementById('kodeVoucherInput').value = chip.dataset.kode;
  }
</script>

</body>
</html>