<?php
include '../includes/cek_admin.php';
include '../koneksi.php';

$id_admin = $_SESSION['id'];

// Proses update profil (termasuk foto)
if (isset($_POST['update_profil'])) {
    $nama = $_POST['nama'];
    $username = $_POST['username'];
    $hp = $_POST['hp'];
    $alamat = $_POST['alamat'];

    // Cek apakah ada foto baru yang diupload
    if (!empty($_FILES['foto_profil']['name'])) {
        $folder_upload = "../uploads/profil/";
        if (!is_dir($folder_upload)) {
            mkdir($folder_upload, 0777, true);
        }

        $nama_file_baru = time() . '_' . $_FILES['foto_profil']['name'];
        $path_tujuan = $folder_upload . $nama_file_baru;

        if (move_uploaded_file($_FILES['foto_profil']['tmp_name'], $path_tujuan)) {
            $foto_profil = "uploads/profil/" . $nama_file_baru;
            mysqli_query($conn, "UPDATE tb_user SET foto_profil='$foto_profil' WHERE id='$id_admin'");
        }
    }

    mysqli_query($conn, "UPDATE tb_user SET nama='$nama', username='$username', hp='$hp', alamat='$alamat' WHERE id='$id_admin'");
    $_SESSION['nama'] = $nama;

    $sukses = "Profil berhasil diperbarui.";
}

// Proses ganti password
if (isset($_POST['ganti_password'])) {
    $password_lama = $_POST['password_lama'];
    $password_baru = $_POST['password_baru'];
    $konfirmasi_baru = $_POST['konfirmasi_baru'];

    $cek = mysqli_query($conn, "SELECT * FROM tb_user WHERE id='$id_admin'");
    $user = mysqli_fetch_assoc($cek);

    if (!password_verify($password_lama, $user['password'])) {
        $error_password = "Password lama yang kamu masukkan salah.";
    } elseif ($password_baru !== $konfirmasi_baru) {
        $error_password = "Konfirmasi password baru tidak sama.";
    } else {
        $hash_baru = password_hash($password_baru, PASSWORD_DEFAULT);
        mysqli_query($conn, "UPDATE tb_user SET password='$hash_baru' WHERE id='$id_admin'");
        $sukses_password = "Password berhasil diubah.";
    }
}

$data = mysqli_query($conn, "SELECT * FROM tb_user WHERE id='$id_admin'");
$admin = mysqli_fetch_assoc($data);

// Ambil inisial nama buat avatar fallback (kalau belum ada foto)
$inisial = strtoupper(substr($admin['nama'], 0, 1));
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Profil Saya — FrostMart Admin</title>
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
      <a href="profil.php" class="active">Profil Saya</a>
      <a href="../logout.php">Logout</a>
    </nav>
  </aside>

  <main class="main-content">
    <h1>Profil Saya</h1>
    <p class="welcome">Kelola data akun admin kamu.</p>

    <?php if (isset($sukses)): ?>
      <div class="alert alert-success"><?php echo $sukses; ?></div>
    <?php endif; ?>

    <!-- ============ VIEW MODE (tampil default) ============ -->
    <section class="panel" id="viewMode">
      <div class="profil-header">
        <div class="avatar">
          <?php if (!empty($admin['foto_profil'])): ?>
            <img src="../<?php echo htmlspecialchars($admin['foto_profil']); ?>" alt="Foto profil">
          <?php else: ?>
            <span><?php echo $inisial; ?></span>
          <?php endif; ?>
        </div>
        <div class="profil-info">
          <h2><?php echo htmlspecialchars($admin['nama']); ?></h2>
          <span class="role-badge">Admin</span>
          <div class="info-rows">
            <div><strong>Email:</strong> <?php echo htmlspecialchars($admin['email']); ?></div>
            <div><strong>No. HP:</strong> <?php echo htmlspecialchars($admin['hp']); ?></div>
            <div><strong>Alamat:</strong> <?php echo htmlspecialchars($admin['alamat']); ?></div>
          </div>
        </div>
        <button type="button" class="btn-kecil" onclick="toggleEdit()">✏️ Edit</button>
      </div>

      <p class="ganti-password-link" onclick="togglePassword()">🔒 Ingin ganti password?</p>
    </section>

    <!-- ============ EDIT FORM (tersembunyi, muncul pas klik Edit) ============ -->
    <section class="panel" id="editMode" style="display:none;">
      <h2>Edit Data Diri</h2>
      <form method="POST" action="profil.php" enctype="multipart/form-data" class="form-grid">
        <div class="field field-full">
          <label>Foto Profil</label>
          <input type="file" name="foto_profil" accept="image/*">
        </div>
        <div class="field">
          <label>Nama Lengkap</label>
          <input type="text" name="nama" value="<?php echo htmlspecialchars($admin['nama']); ?>" required>
        </div>
        <div class="field">
          <label>No. HP</label>
          <input type="text" name="hp" value="<?php echo htmlspecialchars($admin['hp']); ?>" required>
        </div>
        <div class="field">
          <label>Alamat</label>
          <input type="text" name="alamat" value="<?php echo htmlspecialchars($admin['alamat']); ?>" required>
        </div>
        <div class="modal-actions field-full" style="justify-content:flex-start; gap:10px;">
          <button type="submit" name="update_profil" class="btn-primary btn-inline">Simpan Perubahan</button>
          <button type="button" class="btn-kecil" onclick="toggleEdit()">Batal</button>
        </div>
      </form>
    </section>

    <!-- ============ FORM GANTI PASSWORD (tersembunyi) ============ -->
    <section class="panel" id="passwordMode" style="display:none;">
      <h2>Ganti Password</h2>

      <?php if (isset($error_password)): ?>
        <div class="alert alert-error"><?php echo $error_password; ?></div>
      <?php endif; ?>
      <?php if (isset($sukses_password)): ?>
        <div class="alert alert-success"><?php echo $sukses_password; ?></div>
      <?php endif; ?>

      <form method="POST" action="profil.php" class="form-grid">
        <div class="field field-full">
          <label>Password Lama</label>
          <input type="password" name="password_lama" required>
        </div>
        <div class="field">
          <label>Password Baru</label>
          <input type="password" name="password_baru" required>
        </div>
        <div class="field">
          <label>Konfirmasi Password Baru</label>
          <input type="password" name="konfirmasi_baru" required>
        </div>
        <div class="modal-actions field-full" style="justify-content:flex-start; gap:10px;">
          <button type="submit" name="ganti_password" class="btn-primary btn-inline">Ubah Password</button>
          <button type="button" class="btn-kecil" onclick="togglePassword()">Batal</button>
        </div>
      </form>
    </section>

  </main>
</div>

<script>
  function toggleEdit() {
    const view = document.getElementById('viewMode');
    const edit = document.getElementById('editMode');
    const isHidden = edit.style.display === 'none';
    edit.style.display = isHidden ? 'block' : 'none';
    view.style.display = isHidden ? 'none' : 'block';
  }

  function togglePassword() {
    const pass = document.getElementById('passwordMode');
    pass.style.display = pass.style.display === 'none' ? 'block' : 'none';
  }
</script>

</body>
</html>