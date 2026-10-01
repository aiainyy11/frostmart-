<?php
include '../includes/cek_admin.php';
include '../koneksi.php';

$kategori_list = mysqli_query($conn, "SELECT * FROM tb_kategori");
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tambah Produk — FrostMart</title>
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
    <h1>Tambah Produk Baru</h1>
    <p class="welcome">Lengkapi detail produk yang mau ditambahkan.</p>

    <section class="panel">
      <form method="POST" action="proses_tambah.php" enctype="multipart/form-data" class="form-grid">
        <div class="field">
          <label>Nama Produk</label>
          <input type="text" name="nama" required>
        </div>
        <div class="field">
          <label>Kategori</label>
          <select name="id_kategori" required>
            <?php while ($k = mysqli_fetch_assoc($kategori_list)): ?>
              <option value="<?php echo $k['id']; ?>"><?php echo htmlspecialchars($k['nama_kategori']); ?></option>
            <?php endwhile; ?>
          </select>
        </div>
        <div class="field">
          <label>Sub Kategori</label>
          <input type="text" name="sub_kategori" placeholder="misal: Camilan Tradisional">
        </div>
        <div class="field">
          <label>Harga (Rp)</label>
          <input type="number" name="harga" required>
        </div>
        <div class="field">
          <label>Stok</label>
          <input type="number" name="stok" required>
        </div>
        <div class="field">
          <label>Tanggal Kadaluwarsa</label>
          <input type="date" name="tanggal_kadaluwarsa">
        </div>
        <div class="field field-full">
          <label>Deskripsi</label>
          <textarea name="deskripsi" rows="3"></textarea>
        </div>
        <div class="field field-full">
          <label>Foto Produk (boleh pilih lebih dari 1)</label>
          <input type="file" name="foto[]" accept="image/*" multiple required>
        </div>
        <button type="submit" class="btn-primary">Simpan Produk</button>
      </form>
    </section>
  </main>
</div>

</body>
</html>