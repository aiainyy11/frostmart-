<?php
include '../includes/cek_admin.php';
include '../koneksi.php';

$id_transaksi = $_GET['id'] ?? null;

if (!$id_transaksi) {
    header("Location: kelola_transaksi.php");
    exit;
}

// Proses update status
if (isset($_POST['update_status'])) {
    $status_baru = $_POST['status_pesanan'];

    // Ambil status lama dulu, buat perbandingan (biar gak nge-notif kalau statusnya gak beneran berubah)
    $q_lama = mysqli_query($conn, "SELECT status_pesanan, id_pelanggan FROM tb_transaksi WHERE id_transaksi = '$id_transaksi'");
    $data_lama = mysqli_fetch_assoc($q_lama);
    $status_lama = $data_lama['status_pesanan'];
    $id_pelanggan = $data_lama['id_pelanggan'];

    mysqli_query($conn, "UPDATE tb_transaksi SET status_pesanan = '$status_baru' WHERE id_transaksi = '$id_transaksi'");

    // Cuma bikin notifikasi kalau statusnya BENERAN berubah
    if ($status_baru !== $status_lama) {
        $pesan_map = [
            'diproses' => ['judul' => 'Pesanan Sedang Dikemas', 'pesan' => "Pesanan #$id_transaksi kamu sedang kami kemas dengan hati-hati."],
            'dikirim'  => ['judul' => 'Pesanan Dalam Perjalanan', 'pesan' => "Pesanan #$id_transaksi sedang dalam perjalanan menuju alamatmu."],
            'selesai'  => ['judul' => 'Pesanan Telah Sampai', 'pesan' => "Pesanan #$id_transaksi telah sampai. Semoga suka dengan produknya!"],
        ];

        if (isset($pesan_map[$status_baru])) {
            $judul = $pesan_map[$status_baru]['judul'];
            $pesan = $pesan_map[$status_baru]['pesan'];
            $link = "invoice.php?id=$id_transaksi";

            mysqli_query($conn, "
                INSERT INTO tb_notifikasi (id_user, id_transaksi, judul, pesan, link)
                VALUES ('$id_pelanggan', '$id_transaksi', '$judul', '$pesan', '$link')
            ");

            // Kalau statusnya jadi "selesai", tambahin 1 notifikasi lagi khusus ajakan review
            if ($status_baru == 'selesai') {
                $q_produk_pertama = mysqli_query($conn, "
                    SELECT d.id_produk, p.nama
                    FROM tb_detail d
                    JOIN tb_produk p ON d.id_produk = p.id
                    WHERE d.id_transaksi = '$id_transaksi'
                    LIMIT 1
                ");
                $produk_pertama = mysqli_fetch_assoc($q_produk_pertama);

                if ($produk_pertama) {
                    $judul_review = "Yuk Beri Ulasan!";
                    $pesan_review = "Bagaimana pendapatmu tentang " . $produk_pertama['nama'] . "? Kasih ulasan yuk, biar pelanggan lain terbantu.";
                    $link_review = "detail_produk.php?id=" . $produk_pertama['id_produk'] . "#komentar";

                    mysqli_query($conn, "
                        INSERT INTO tb_notifikasi (id_user, id_transaksi, judul, pesan, link)
                        VALUES ('$id_pelanggan', '$id_transaksi', '$judul_review', '$pesan_review', '$link_review')
                    ");
                }
            }
        }
    }

    header("Location: detail_transaksi.php?id=$id_transaksi&update_sukses=1");
    exit;
}

$transaksi = mysqli_query($conn, "
    SELECT t.*, u.nama AS nama_pelanggan, u.email, u.hp, u.alamat
    FROM tb_transaksi t
    JOIN tb_user u ON t.id_pelanggan = u.id
    WHERE t.id_transaksi = '$id_transaksi'
");
$t = mysqli_fetch_assoc($transaksi);

if (!$t) {
    header("Location: kelola_transaksi.php");
    exit;
}

$detail = mysqli_query($conn, "
    SELECT d.*, p.nama AS nama_produk, p.harga
    FROM tb_detail d
    JOIN tb_produk p ON d.id_produk = p.id
    WHERE d.id_transaksi = '$id_transaksi'
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Detail Transaksi #<?php echo $id_transaksi; ?> — FrostMart Admin</title>
<link rel="stylesheet" href="../assets/css/admin.css">
<style>
  @media print {
    .sidebar, .back-link, .no-print { display: none !important; }
    .main-content { padding: 0 !important; }
    body { background: white; color: black; }
    .panel { background: white; border: 1px solid #ccc; color: black; }
    table thead th, table tbody td { color: black; border-color: #ccc; }
  }
  .status-form { display: flex; gap: 10px; align-items: center; margin-bottom: 20px; }
  .status-form select {
    padding: 9px 12px; border-radius: 8px; border: 1px solid var(--line);
    background: var(--ice-deep); color: var(--frost); font-size: 13.5px;
  }
</style>
</head>
<body>

<div class="admin-layout">

  <aside class="sidebar">
    <div class="sidebar-brand">❄️ FrostMart Admin</div>
    <nav>
      <a href="dashboard.php">Dashboard</a>
      <a href="kelola_transaksi.php" class="active">Data Transaksi</a>
      <a href="kelola_pelanggan.php">Data Pelanggan</a>
      <a href="profil.php">Profil Saya</a>
      <a href="../logout.php">Logout</a>
    </nav>
  </aside>

  <main class="main-content">
    <a href="kelola_transaksi.php" class="back-link">← Kembali ke Data Transaksi</a>
    <h1>Invoice #<?php echo $t['id_transaksi']; ?></h1>
    <p class="welcome">Detail pesanan dan rincian pembayaran.</p>

    <?php if (isset($_GET['update_sukses'])): ?>
      <div class="alert alert-success no-print">Status pesanan berhasil diperbarui. Notifikasi otomatis terkirim ke pelanggan.</div>
    <?php endif; ?>

    <!-- UBAH STATUS PESANAN -->
    <section class="panel no-print">
      <h2>Ubah Status Pesanan</h2>
      <form method="POST" action="detail_transaksi.php?id=<?php echo $id_transaksi; ?>" class="status-form">
        <select name="status_pesanan">
          <option value="pending" <?php echo $t['status_pesanan'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
          <option value="diproses" <?php echo $t['status_pesanan'] == 'diproses' ? 'selected' : ''; ?>>Diproses (Dikemas)</option>
          <option value="dikirim" <?php echo $t['status_pesanan'] == 'dikirim' ? 'selected' : ''; ?>>Dikirim</option>
          <option value="selesai" <?php echo $t['status_pesanan'] == 'selesai' ? 'selected' : ''; ?>>Selesai (Sampai)</option>
        </select>
        <button type="submit" name="update_status" class="btn-kecil">Update Status</button>
      </form>
    </section>

    <section class="panel">
      <div style="display:flex; justify-content:space-between; margin-bottom:20px;">
        <div>
          <strong>Pelanggan:</strong><br>
          <?php echo htmlspecialchars($t['nama_pelanggan']); ?><br>
          <?php echo htmlspecialchars($t['email']); ?><br>
          <?php echo htmlspecialchars($t['hp']); ?><br>
          <?php echo htmlspecialchars($t['alamat']); ?>
        </div>
        <div style="text-align:right;">
          <strong>Tanggal:</strong> <?php echo date('d/m/Y', strtotime($t['tanggal'])); ?><br>
          <strong>Status:</strong> <?php echo ucfirst($t['status_pesanan']); ?>
        </div>
      </div>

      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>Produk</th>
              <th>Harga Satuan</th>
              <th>Jumlah</th>
              <th>Subtotal</th>
            </tr>
          </thead>
          <tbody>
            <?php $grand_total = 0; ?>
            <?php while ($d = mysqli_fetch_assoc($detail)): ?>
              <?php $subtotal = $d['harga'] * $d['jumlah']; $grand_total += $subtotal; ?>
              <tr>
                <td><?php echo htmlspecialchars($d['nama_produk']); ?></td>
                <td>Rp <?php echo number_format($d['harga'], 0, ',', '.'); ?></td>
                <td><?php echo $d['jumlah']; ?></td>
                <td>Rp <?php echo number_format($subtotal, 0, ',', '.'); ?></td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>

      <div style="text-align:right; margin-top:20px; font-size:18px; font-weight:700;">
        Total: Rp <?php echo number_format($t['total_harga'], 0, ',', '.'); ?>
      </div>

      <div class="no-print" style="margin-top:24px; text-align:right;">
        <button onclick="window.print()" class="btn-primary btn-inline">🖨️ Cetak Invoice</button>
      </div>
    </section>
  </main>
</div>

</body>
</html>