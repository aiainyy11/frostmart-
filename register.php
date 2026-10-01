<?php
session_start();
include 'koneksi.php';

if (isset($_POST['register'])) {
    $nama = $_POST['nama'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $konfirmasi = $_POST['konfirmasi_password'];
    $hp = $_POST['hp'];
    $alamat = $_POST['alamat'];

    if ($password !== $konfirmasi) {
        $error = "Password dan konfirmasi password tidak sama.";
    } else {
        $cek = mysqli_query($conn, "SELECT * FROM tb_user WHERE email='$email'");

        if (mysqli_num_rows($cek) > 0) {
            $error = "Email sudah terdaftar, coba pakai yang lain.";
        } else {
            }

            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            $insert = mysqli_query($conn, "INSERT INTO tb_user (nama, email, password, hp, alamat, role) 
                VALUES ('$nama', '$email', '$password_hash', '$hp', '$alamat', 'pelanggan')");

           
            if ($insert) {
              header("Location: login.php?success=Registrasi berhasil! Silakan login.");
              exit;
            } else {
            $error = "Terjadi kesalahan, coba lagi.";
            }
        }
    }
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Daftar — FrostMart</title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

  <div class="frost-dot" style="width:6px;height:6px;top:12%;left:8%;animation-delay:0s;"></div>
  <div class="frost-dot" style="width:4px;height:4px;top:70%;left:12%;animation-delay:2s;"></div>
  <div class="frost-dot" style="width:8px;height:8px;top:20%;left:90%;animation-delay:1s;"></div>
  <div class="frost-dot" style="width:5px;height:5px;top:80%;left:85%;animation-delay:3s;"></div>
  <div class="frost-dot" style="width:3px;height:3px;top:45%;left:95%;animation-delay:4s;"></div>

  <div class="card">
    <div class="brand-side">
      <div class="brand-mark">
        <svg class="snow-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M12 2V22M4.5 6.5L19.5 17.5M19.5 6.5L4.5 17.5" stroke="#4fc3e8" stroke-width="1.6" stroke-linecap="round"/>
          <circle cx="12" cy="12" r="10.5" stroke="#4fc3e8" stroke-width="1" opacity="0.35"/>
        </svg>
        <span>FROSTMART</span>
      </div>

      <div>
        <div class="brand-eyebrow">Gabung Sekarang</div>
        <h1 class="brand-title">Mulai belanja frozen food favoritmu hari ini.</h1>
        <p class="brand-desc">Daftar sebentar aja, langsung bisa checkout produk favoritmu.</p>
      </div>

      <div class="temp-row">
        <div class="temp-item">❄️ <strong>-18°C</strong> penyimpanan</div>
        <div class="temp-item">🚚 <strong>Same day</strong> kirim</div>
      </div>
    </div>

    <div class="form-side">
      <h1>Buat Akun Baru</h1>
      <p class="sub">Lengkapi data di bawah untuk mendaftar.</p>

      <?php if (isset($error)): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
      <?php endif; ?>

      <form method="POST" action="register.php">
        <div class="field">
          <label for="nama">Nama Lengkap</label>
          <input type="text" name="nama" id="nama" placeholder="Nama kamu" required>
        </div>

        <div class="two-col">
          <div class="field">
            <label for="email">Email</label>
            <input type="email" name="email" id="email" placeholder="email@contoh.com" required>
          </div>
        </div>

        <div class="two-col">
          <div class="field">
            <label for="password">Kata Sandi</label>
            <input type="password" name="password" id="password" placeholder="Min. 6 karakter" required>
          </div>
          <div class="field">
            <label for="konfirmasi_password">Konfirmasi Sandi</label>
            <input type="password" name="konfirmasi_password" id="konfirmasi_password" placeholder="Ulangi sandi" required>
          </div>
        </div>

        <div class="field">
          <label for="hp">No. HP</label>
          <input type="text" name="hp" id="hp" placeholder="08xxxxxxxxxx" required>
        </div>

        <div class="field">
          <label for="alamat">Alamat</label>
          <input type="text" name="alamat" id="alamat" placeholder="Alamat lengkap untuk pengiriman" required>
        </div>

        <button type="submit" name="register" class="submit">Daftar Sekarang</button>
      </form>

      <p class="footer-note">Sudah punya akun? <a href="login.php">Masuk di sini</a></p>
    </div>
  </div>

</body>
</html>