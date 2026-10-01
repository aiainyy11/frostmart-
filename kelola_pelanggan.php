<?php
include '../includes/cek_admin.php';
include '../koneksi.php';

// Proses reset password (kalau form submit)
if (isset($_POST['reset_password'])) {
    $id_user = $_POST['id_user'];
    $password_baru = $_POST['password_baru'];

    $hash_baru = password_hash($password_baru, PASSWORD_DEFAULT);
    mysqli_query($conn, "UPDATE tb_user SET password = '$hash_baru' WHERE id = '$id_user'");

    $sukses = "Password pelanggan berhasil direset.";
}

// Ambil semua data pelanggan (role = pelanggan doang, admin gak usah muncul di sini)
$pelanggan = mysqli_query($conn, "SELECT * FROM tb_user WHERE role = 'pelanggan' ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Data Pelanggan — FrostMart Admin</title>
<link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>

<div class="admin-layout">

  <aside class="sidebar">
    <div class="sidebar-brand">❄️ FrostMart Admin</div>
    <nav>
      <a href="dashboard.php">Dashboard</a>
      <a href="kelola_transaksi.php">Data Transaksi</a>
      <a href="kelola_pelanggan.php" class="active">Data Pelanggan</a>
      <a href="profil.php">Profil Saya</a>
      <a href="../logout.php">Logout</a>
    </nav>
  </aside>

  <main class="main-content">
    <h1>Data Pelanggan</h1>
    <p class="welcome">Daftar semua pelanggan yang terdaftar.</p>

    <?php if (isset($sukses)): ?>
      <div class="alert alert-success"><?php echo $sukses; ?></div>
    <?php endif; ?>

    <section class="panel">
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>Nama</th>
              <th>Email</th>
              <th>No. HP</th>
              <th>Alamat</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($p = mysqli_fetch_assoc($pelanggan)): ?>
            <tr>
              <td><?php echo htmlspecialchars($p['nama']); ?></td>
              <td><?php echo htmlspecialchars($p['email']); ?></td>
              <td><?php echo htmlspecialchars($p['hp']); ?></td>
              <td><?php echo htmlspecialchars($p['alamat']); ?></td>
              <td>
                <button type="button" class="btn-kecil" onclick="bukaModal(<?php echo $p['id']; ?>, '<?php echo htmlspecialchars($p['nama']); ?>')">
                  Reset Password
                </button>
              </td>
            </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </section>
  </main>
</div>

<!-- MODAL RESET PASSWORD (tersembunyi, muncul pas tombol diklik) -->
<div id="modalReset" class="modal-overlay" style="display:none;">
  <div class="modal-box">
    <h3>Reset Password</h3>
    <p id="namaPelangganModal" class="modal-sub"></p>
    <form method="POST" action="kelola_pelanggan.php">
      <input type="hidden" name="id_user" id="idUserModal">
      <div class="field">
        <label>Password Baru</label>
        <input type="text" name="password_baru" required placeholder="Ketik password baru">
      </div>
      <div class="modal-actions">
        <button type="button" class="btn-kecil" onclick="tutupModal()">Batal</button>
        <button type="submit" name="reset_password" class="btn-primary btn-inline">Simpan Password Baru</button>
      </div>
    </form>
  </div>
</div>

<script>
  function bukaModal(id, nama) {
    document.getElementById('idUserModal').value = id;
    document.getElementById('namaPelangganModal').innerText = 'Untuk akun: ' + nama;
    document.getElementById('modalReset').style.display = 'flex';
  }
  function tutupModal() {
    document.getElementById('modalReset').style.display = 'none';
  }
</script>

</body>
</html>