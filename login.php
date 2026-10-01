<?php
session_start();
include 'koneksi.php';

if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $query = mysqli_query($conn, "SELECT * FROM tb_user WHERE email='$email'");
    $user = mysqli_fetch_assoc($query);

    if ($user) {
        if (password_verify($password, $user['password'])) {
            $_SESSION['id'] = $user['id'];
            $_SESSION['nama'] = $user['nama'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['login'] = true;

            if ($user['role'] == 'admin') {
                header("Location: admin/dashboard.php");
                exit;
            } elseif ($user['role'] == 'pelanggan') {
                header("Location: pelanggan/index.php");
                exit;
            }
        } else {
            $error = "Email atau password salah.";
        }
    } else {
        $error = "Email atau password salah.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Masuk — FrostMart</title>
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
        <div class="brand-eyebrow">Selamat Datang Kembali</div>
        <h1 class="brand-title">Belanja frozen food favoritmu, tetap segar sampai rumah.</h1>
        <p class="brand-desc">Masuk untuk lihat produk terbaru, kelola keranjang, dan cek riwayat pesananmu.</p>
      </div>

      <div class="temp-row">
        <div class="temp-item">❄️ <strong>-18°C</strong> penyimpanan</div>
        <div class="temp-item">🚚 <strong>Same day</strong> kirim</div>
      </div>
    </div>

    <div class="form-side">
      <h1>Masuk Akun</h1>
      <p class="sub">Masukkan email dan kata sandi untuk melanjutkan.</p>

      <?php if (isset($error)): ?>
        <p style="background:rgba(255,80,80,0.12); border:1px solid rgba(255,80,80,0.4); color:#ffb4b4; padding:10px 14px; border-radius:8px; font-size:13px; margin-bottom:18px;">
          <?php echo $error; ?>
        </p>
      <?php endif; ?>

      <form method="POST" action="login.php">
        <div class="field">
          <label for="identity">Email</label>
          <input type="text" name="email" id="identity" placeholder="contoh: budi@email.com" required>
        </div>

        <div class="field">
          <label for="password">Kata Sandi</label>
          <div class="password-wrap">
            <input type="password" name="password" id="password" placeholder="Masukkan kata sandi" required>
            <button type="button" class="toggle-eye" id="toggleEye" aria-label="Tampilkan kata sandi">
              <svg id="eyeIcon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M1 12C1 12 5 5 12 5C19 5 23 12 23 12C23 12 19 19 12 19C5 19 1 12 1 12Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/>
                <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.6"/>
              </svg>
            </button>
          </div>
        </div>

        <button type="submit" name="login" class="submit">Masuk</button>
      </form>

      <div class="divider">atau</div>

      <p class="footer-note">Belum punya akun? <a href="register.php">Daftar sekarang</a></p>
    </div>
  </div>

  <script>
    const toggleEye = document.getElementById('toggleEye');
    const passwordInput = document.getElementById('password');
    const eyeIcon = document.getElementById('eyeIcon');

    const eyeOpenSVG = `<path d="M1 12C1 12 5 5 12 5C19 5 23 12 23 12C23 12 19 19 12 19C5 19 1 12 1 12Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/><circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.6"/>`;
    const eyeClosedSVG = `<path d="M1 12C1 12 5 5 12 5C19 5 23 12 23 12C23 12 19 19 12 19C5 19 1 12 1 12Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/><circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.6"/><line x1="3" y1="21" x2="21" y2="3" stroke="currentColor" stroke-width="1.6"/>`;

    toggleEye.addEventListener('click', () => {
      const isPassword = passwordInput.type === 'password';
      passwordInput.type = isPassword ? 'text' : 'password';
      eyeIcon.innerHTML = isPassword ? eyeClosedSVG : eyeOpenSVG;
      toggleEye.setAttribute('aria-label', isPassword ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
    });
  </script>
</body>
</html>