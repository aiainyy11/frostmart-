<?php
include '../includes/cek_admin.php';
include '../koneksi.php';

// ============================================
// QUERY STATISTIK
// ============================================
$q_harian = mysqli_query($conn, "SELECT SUM(total_harga) AS total FROM tb_transaksi WHERE tanggal = CURDATE()");
$total_harian = mysqli_fetch_assoc($q_harian)['total'] ?? 0;

$q_bulanan = mysqli_query($conn, "SELECT SUM(total_harga) AS total FROM tb_transaksi WHERE MONTH(tanggal) = MONTH(CURDATE()) AND YEAR(tanggal) = YEAR(CURDATE())");
$total_bulanan = mysqli_fetch_assoc($q_bulanan)['total'] ?? 0;

$q_tahunan = mysqli_query($conn, "SELECT SUM(total_harga) AS total FROM tb_transaksi WHERE YEAR(tanggal) = YEAR(CURDATE())");
$total_tahunan = mysqli_fetch_assoc($q_tahunan)['total'] ?? 0;

$q_jumlah = mysqli_query($conn, "SELECT COUNT(*) AS jumlah FROM tb_transaksi");
$jumlah_pesanan = mysqli_fetch_assoc($q_jumlah)['jumlah'] ?? 0;

$q_laris = mysqli_query($conn, "
    SELECT p.nama, SUM(d.jumlah) AS total_terjual
    FROM tb_detail d
    JOIN tb_produk p ON d.id_produk = p.id
    GROUP BY d.id_produk
    ORDER BY total_terjual DESC
    LIMIT 1
");
$produk_terlaris = mysqli_fetch_assoc($q_laris);

$q_stok = mysqli_query($conn, "SELECT COUNT(*) AS jumlah FROM tb_produk WHERE stok < 10");
$jumlah_stok_menipis = mysqli_fetch_assoc($q_stok)['jumlah'] ?? 0;

$q_exp = mysqli_query($conn, "SELECT COUNT(*) AS jumlah FROM tb_produk WHERE tanggal_kadaluwarsa BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)");
$jumlah_exp = mysqli_fetch_assoc($q_exp)['jumlah'] ?? 0;

// ============================================
// NOTIFIKASI: PESANAN BARU MASUK (status masih pending)
// ============================================
$q_notif = mysqli_query($conn, "
    SELECT t.*, u.nama AS nama_pelanggan
    FROM tb_transaksi t
    JOIN tb_user u ON t.id_pelanggan = u.id
    WHERE t.status_pesanan = 'pending'
    ORDER BY t.tanggal DESC, t.id_transaksi DESC
    LIMIT 10
");
$jumlah_notif = mysqli_num_rows($q_notif);

// ============================================
// QUERY DATA PRODUK
// ============================================
$produk = mysqli_query($conn, "
    SELECT p.*, k.nama_kategori 
    FROM tb_produk p
    LEFT JOIN tb_kategori k ON p.id_kategori = k.id
    ORDER BY p.id DESC
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard Admin — FrostMart</title>
<link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>

<div class="admin-layout">

  <aside class="sidebar">
    <div class="sidebar-brand">❄️ FrostMart Admin</div>
    <nav>
      <a href="dashboard.php" class="active">Dashboard</a>
      <a href="kelola_transaksi.php">Data Transaksi</a>
      <a href="kelola_pelanggan.php">Data Pelanggan</a>
      <a href="profil.php">Profil Saya</a>
      <a href="../logout.php">Logout</a>
    </nav>
  </aside>

  <main class="main-content">
    <div class="topbar">
      <div>
        <h1>Dashboard</h1>
        <p class="welcome">Halo, <?php echo htmlspecialchars($_SESSION['nama']); ?> 👋</p>
      </div>

      <!-- ICON NOTIFIKASI ADMIN -->
      <div class="notif-wrap">
        <button type="button" class="icon-btn" onclick="toggleDropdown('dropdownNotif')">
          🔔
          <?php if ($jumlah_notif > 0): ?><span class="notif-dot"></span><?php endif; ?>
        </button>
        <div class="dropdown-menu" id="dropdownNotif">
          <div class="dropdown-title">Pesanan Baru Masuk (<?php echo $jumlah_notif; ?>)</div>
          <?php if ($jumlah_notif > 0): ?>
            <?php while ($n = mysqli_fetch_assoc($q_notif)): ?>
              <a href="detail_transaksi.php?id=<?php echo $n['id_transaksi']; ?>" class="dropdown-item dropdown-item-link">
                <span class="dropdown-icon">📦</span>
                <div>
                  <div class="dropdown-item-title">Pesanan #<?php echo $n['id_transaksi']; ?> dari <?php echo htmlspecialchars($n['nama_pelanggan']); ?></div>
                  <div class="dropdown-item-sub">Rp <?php echo number_format($n['total_harga'], 0, ',', '.'); ?> — menunggu diproses</div>
                </div>
              </a>
            <?php endwhile; ?>
          <?php else: ?>
            <div class="dropdown-kosong">Tidak ada pesanan baru.</div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <?php if (isset($_GET['gagal_hapus'])): ?>
      <div class="alert alert-error">Produk tidak bisa dihapus karena sudah pernah ada transaksi. Gunakan "Sembunyikan" sebagai gantinya.</div>
    <?php endif; ?>
    <?php if (isset($_GET['hapus_sukses'])): ?>
      <div class="alert alert-success">Produk berhasil dihapus.</div>
    <?php endif; ?>

    <!-- KARTU STATISTIK -->
    <div class="stat-grid">
      <div class="stat-card">
        <span class="stat-label">Penjualan Hari Ini</span>
        <span class="stat-value">Rp <?php echo number_format($total_harian, 0, ',', '.'); ?></span>
      </div>
      <div class="stat-card">
        <span class="stat-label">Penjualan Bulan Ini</span>
        <span class="stat-value">Rp <?php echo number_format($total_bulanan, 0, ',', '.'); ?></span>
      </div>
      <div class="stat-card">
        <span class="stat-label">Penjualan Tahun Ini</span>
        <span class="stat-value">Rp <?php echo number_format($total_tahunan, 0, ',', '.'); ?></span>
      </div>
      <div class="stat-card">
        <span class="stat-label">Jumlah Pesanan</span>
        <span class="stat-value"><?php echo $jumlah_pesanan; ?></span>
      </div>
      <div class="stat-card">
        <span class="stat-label">Produk Terlaris</span>
        <span class="stat-value" style="font-size:16px;">
          <?php echo $produk_terlaris ? htmlspecialchars($produk_terlaris['nama']) . " (" . $produk_terlaris['total_terjual'] . " terjual)" : "Belum ada data"; ?>
        </span>
      </div>
      <div class="stat-card <?php echo $jumlah_stok_menipis > 0 ? 'stat-warning' : ''; ?>">
        <span class="stat-label">Stok Menipis</span>
        <span class="stat-value"><?php echo $jumlah_stok_menipis; ?> produk</span>
      </div>
      <div class="stat-card <?php echo $jumlah_exp > 0 ? 'stat-danger' : ''; ?>">
        <span class="stat-label">Mendekati Kadaluwarsa</span>
        <span class="stat-value"><?php echo $jumlah_exp; ?> produk</span>
      </div>
    </div>

    <!-- TABEL DATA PRODUK -->
    <section class="panel">
      <div class="panel-header">
        <h2>Data Produk</h2>
        <a href="tambah_produk.php" class="btn-primary btn-inline">+ Tambah Produk</a>
      </div>
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>Nama</th>
              <th>Kategori</th>
              <th>Harga</th>
              <th>Stok</th>
              <th>Kadaluwarsa</th>
              <th>Status</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($p = mysqli_fetch_assoc($produk)): ?>
            <tr>
              <td><?php echo htmlspecialchars($p['nama']); ?></td>
              <td><?php echo htmlspecialchars($p['nama_kategori'] ?? '-'); ?></td>
              <td>Rp <?php echo number_format($p['harga'], 0, ',', '.'); ?></td>
              <td><?php echo $p['stok']; ?></td>
              <td><?php echo $p['tanggal_kadaluwarsa'] ? date('d/m/Y', strtotime($p['tanggal_kadaluwarsa'])) : '-'; ?></td>
              <td>
                <span class="badge <?php echo $p['status'] == 'aktif' ? 'badge-hijau' : 'badge-abu'; ?>">
                  <?php echo ucfirst($p['status']); ?>
                </span>
              </td>
              <td class="aksi-cell">
                <a href="edit_produk.php?id=<?php echo $p['id']; ?>" class="btn-kecil">Edit</a>
                <a href="toggle_status_produk.php?id=<?php echo $p['id']; ?>" class="btn-kecil">
                  <?php echo $p['status'] == 'aktif' ? 'Sembunyikan' : 'Tampilkan'; ?>
                </a>
                <a href="hapus_produk.php?id=<?php echo $p['id']; ?>" class="btn-kecil btn-hapus" onclick="return confirm('Yakin hapus produk ini?')">Hapus</a>
              </td>
            </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </section>

  </main>
</div>

<script>
  function toggleDropdown(idDropdown) {
    const target = document.getElementById(idDropdown);
    document.querySelectorAll('.dropdown-menu').forEach(d => {
      if (d.id !== idDropdown) d.classList.remove('show');
    });
    target.classList.toggle('show');
  }

  document.addEventListener('click', function(e) {
    if (!e.target.closest('.notif-wrap')) {
      document.querySelectorAll('.dropdown-menu').forEach(d => d.classList.remove('show'));
    }
  });
</script>

</body>
</html>