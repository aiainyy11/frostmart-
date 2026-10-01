<?php
include '../includes/cek_admin.php';
include '../koneksi.php';

$id_produk = $_GET['id'] ?? null;

if (!$id_produk) {
    header("Location: dashboard.php");
    exit;
}

$q_produk = mysqli_query($conn, "SELECT * FROM tb_produk WHERE id = '$id_produk'");
$produk = mysqli_fetch_assoc($q_produk);

if (!$produk) {
    header("Location: dashboard.php");
    exit;
}

$kategori_list = mysqli_query($conn, "SELECT * FROM tb_kategori");
$foto_list = mysqli_query($conn, "SELECT * FROM tb_foto_produk WHERE id_produk = '$id_produk' ORDER BY urutan ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Produk — FrostMart Admin</title>
<link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>

<div class="admin-layout">

  <aside class="sidebar">
    <div class="sidebar-brand">❄️ FrostMart Admin</div>
    <nav>
      <a href="dashboard.php">Dashboard</a>
      <a href="kelola_transaksi.php">Data Transaksi</a>
      <a href="kelola_pelanggan.php">Data Pelanggan</a>
      <a href="profil.php">Profil Saya</a>
      <a href="../logout.php">Logout</a>
    </nav>
  </aside>

  <main class="main-content">
    <a href="dashboard.php" class="back-link">← Kembali ke Dashboard</a>
    <h1>Edit Produk</h1>
    <p class="welcome"><?php echo htmlspecialchars($produk['nama']); ?></p>

    <!-- FORM EDIT DATA PRODUK -->
    <section class="panel">
      <h2>Data Produk</h2>
      <form method="POST" action="proses_edit_produk.php" class="form-grid">
        <input type="hidden" name="id_produk" value="<?php echo $produk['id']; ?>">

        <div class="field">
          <label>Nama Produk</label>
          <input type="text" name="nama" value="<?php echo htmlspecialchars($produk['nama']); ?>" required>
        </div>
        <div class="field">
          <label>Kategori</label>
          <select name="id_kategori" required>
            <?php while ($k = mysqli_fetch_assoc($kategori_list)): ?>
              <option value="<?php echo $k['id']; ?>" <?php echo $k['id'] == $produk['id_kategori'] ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($k['nama_kategori']); ?>
              </option>
            <?php endwhile; ?>
          </select>
        </div>
        <div class="field">
          <label>Sub Kategori</label>
          <input type="text" name="sub_kategori" value="<?php echo htmlspecialchars($produk['sub_kategori']); ?>">
        </div>
        <div class="field">
          <label>Harga (Rp)</label>
          <input type="number" name="harga" value="<?php echo $produk['harga']; ?>" required>
        </div>
        <div class="field">
          <label>Stok</label>
          <input type="number" name="stok" value="<?php echo $produk['stok']; ?>" required>
        </div>
        <div class="field">
          <label>Tanggal Kadaluwarsa</label>
          <input type="date" name="tanggal_kadaluwarsa" value="<?php echo $produk['tanggal_kadaluwarsa']; ?>">
        </div>
        <div class="field field-full">
          <label>Deskripsi</label>
          <textarea name="deskripsi" rows="3"><?php echo htmlspecialchars($produk['deskripsi']); ?></textarea>
        </div>

        <button type="submit" class="btn-primary">Simpan Perubahan</button>
      </form>
    </section>

    <!-- KELOLA FOTO -->
    <section class="panel">
      <h2>Foto Produk</h2>

      <div class="foto-existing">
        <?php if (mysqli_num_rows($foto_list) == 0): ?>
          <p style="color:var(--frost-dim); font-size:13px;">Belum ada foto untuk produk ini.</p>
        <?php endif; ?>
        <?php while ($f = mysqli_fetch_assoc($foto_list)): ?>
          <div class="foto-item">
            <img src="../<?php echo htmlspecialchars($f['url_foto']); ?>" alt="">
            <a href="hapus_foto_produk.php?id=<?php echo $f['id']; ?>&id_produk=<?php echo $id_produk; ?>"
               class="foto-hapus" onclick="return confirm('Hapus foto ini?')">✕</a>
          </div>
        <?php endwhile; ?>
      </div>

      <form method="POST" action="proses_tambah_foto.php" enctype="multipart/form-data" class="form-tambah-foto">
        <input type="hidden" name="id_produk" value="<?php echo $id_produk; ?>">
        <input type="file" name="foto[]" accept="image/*" multiple required>
        <button type="submit" class="btn-kecil">+ Tambah Foto</button>
      </form>
    </section>

  </main>
</div>

</body>
</html>