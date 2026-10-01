<?php
include '../includes/cek_login.php';
include '../koneksi.php';
include '../includes/status_label.php';

$id_user = $_SESSION['id'];


/* ==========================================
   PROSES UPDATE PROFIL
   ========================================== */

if (isset($_POST['update_profil'])) {

    $nama = $_POST['nama'];
    $hp = $_POST['hp'];
    $alamat = $_POST['alamat'];

    /* Upload foto profil */
    if (!empty($_FILES['foto_profil']['name'])) {

        $folder_upload = "../uploads/profil/";

        if (!is_dir($folder_upload)) {
            mkdir($folder_upload, 0777, true);
        }

        $nama_file_baru = time() . '_' . $_FILES['foto_profil']['name'];
        $path_tujuan = $folder_upload . $nama_file_baru;

        if (move_uploaded_file(
            $_FILES['foto_profil']['tmp_name'],
            $path_tujuan
        )) {

            $foto_profil = "uploads/profil/" . $nama_file_baru;

            mysqli_query($conn, "
                UPDATE tb_user
                SET foto_profil='$foto_profil'
                WHERE id='$id_user'
            ");
        }
    }


    /* Update data diri */
    mysqli_query($conn, "
        UPDATE tb_user
        SET
            nama='$nama',
            hp='$hp',
            alamat='$alamat'
        WHERE id='$id_user'
    ");


    $_SESSION['nama'] = $nama;

    $sukses = "Profil berhasil diperbarui.";
}



/* ==========================================
   PROSES GANTI PASSWORD
   ========================================== */

if (isset($_POST['ganti_password'])) {

    $password_lama = $_POST['password_lama'];
    $password_baru = $_POST['password_baru'];
    $konfirmasi_baru = $_POST['konfirmasi_baru'];


    $cek = mysqli_query($conn, "
        SELECT *
        FROM tb_user
        WHERE id='$id_user'
    ");

    $user = mysqli_fetch_assoc($cek);


    if (!password_verify(
        $password_lama,
        $user['password']
    )) {

        $error_password = "Password lama yang kamu masukkan salah.";

    } elseif ($password_baru !== $konfirmasi_baru) {

        $error_password = "Konfirmasi password baru tidak sama.";

    } else {

        $hash_baru = password_hash(
            $password_baru,
            PASSWORD_DEFAULT
        );

        mysqli_query($conn, "
            UPDATE tb_user
            SET password='$hash_baru'
            WHERE id='$id_user'
        ");

        $sukses_password = "Password berhasil diubah.";
    }
}



/* ==========================================
   AMBIL DATA USER
   ========================================== */

$data = mysqli_query($conn, "
    SELECT *
    FROM tb_user
    WHERE id='$id_user'
");

$pelanggan = mysqli_fetch_assoc($data);



/* ==========================================
   RIWAYAT TRANSAKSI USER
   ========================================== */

$riwayat = mysqli_query($conn, "
    SELECT *
    FROM tb_transaksi
    WHERE id_pelanggan='$id_user'
    ORDER BY tanggal DESC, id_transaksi DESC
");



/* ==========================================
   JUMLAH KERANJANG
   ========================================== */

$q_cart = mysqli_query($conn, "
    SELECT SUM(jumlah) AS total
    FROM tb_keranjang
    WHERE id_user='$id_user'
");

$jumlah_keranjang = mysqli_fetch_assoc($q_cart)['total'] ?? 0;

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Profil Saya — FrostMart</title>

    <link
        rel="stylesheet"
        href="../assets/css/pelanggan.css"
    >

    <link
        rel="stylesheet"
        href="../assets/css/profile.css"
    >

</head>


<body>


<!-- ==========================================
     NAVBAR
     ========================================== -->

<header class="navbar">

    <div class="navbar-inner">


        <a
            href="index.php"
            class="brand"
        >
            ❄️ FrostMart
        </a>


        <form
            method="GET"
            action="index.php"
            class="search-bar"
        >

            <input
                type="text"
                name="cari"
                placeholder="Cari nugget, bakso, seafood..."
            >

            <button type="submit">
                🔍
            </button>

        </form>


        <div class="navbar-actions">


            <a
                href="keranjang.php"
                class="cart-icon"
            >

                🛒

                <?php if ($jumlah_keranjang > 0): ?>

                    <span class="cart-badge">

                        <?php
                        echo $jumlah_keranjang;
                        ?>

                    </span>

                <?php endif; ?>

            </a>


            <a
                href="profile.php"
                class="nav-link"
            >
                Profil Saya
            </a>


            <a
                href="../logout.php"
                class="nav-link"
            >
                Logout
            </a>

        </div>

    </div>

</header>



<!-- ==========================================
     MAIN
     ========================================== -->

<main class="profile-wrap">


    <h1>
        Profil Saya
    </h1>



    <!-- ==========================================
         ALERT SUKSES
         ========================================== -->

    <?php if (isset($sukses)): ?>

        <div class="alert-sukses">

            <?php
            echo htmlspecialchars($sukses);
            ?>

        </div>

    <?php endif; ?>



    <!-- ==========================================
         VIEW PROFILE
         ========================================== -->

    <section
        class="panel"
        id="viewMode"
    >

        <div class="profil-header">


            <!-- FOTO -->

            <div class="avatar">

                <?php if (!empty($pelanggan['foto_profil'])): ?>

                    <img
                        src="../<?php
                        echo htmlspecialchars(
                            $pelanggan['foto_profil']
                        );
                        ?>"
                        alt="Foto profil"
                    >

                <?php else: ?>

                    <svg
                        viewBox="0 0 24 24"
                        fill="none"
                        xmlns="http://www.w3.org/2000/svg"
                        class="avatar-default"
                    >

                        <circle
                            cx="12"
                            cy="8"
                            r="4"
                            fill="currentColor"
                        />

                        <path
                            d="M4 20c0-4.4 3.6-8 8-8s8 3.6 8 8"
                            fill="currentColor"
                        />

                    </svg>

                <?php endif; ?>

            </div>



            <!-- INFORMASI USER -->

            <div class="profil-info">


                <h2>

                    <?php
                    echo htmlspecialchars(
                        $pelanggan['nama']
                    );
                    ?>

                </h2>


                <span class="role-badge">
                    Pelanggan
                </span>


                <div class="info-rows">


                    <div>

                        <strong>Email:</strong>

                        <?php
                        echo htmlspecialchars(
                            $pelanggan['email']
                        );
                        ?>

                    </div>


                    <div>

                        <strong>No. HP:</strong>

                        <?php
                        echo htmlspecialchars(
                            $pelanggan['hp']
                        );
                        ?>

                    </div>


                    <div>

                        <strong>Alamat:</strong>

                        <?php
                        echo htmlspecialchars(
                            $pelanggan['alamat']
                        );
                        ?>

                    </div>


                </div>

            </div>



            <!-- EDIT -->

            <button
                type="button"
                class="btn-kecil"
                onclick="toggleEdit()"
            >
                ✏️ Edit
            </button>


        </div>



        <!-- GANTI PASSWORD -->

        <a
            href="#"
            class="ganti-password-link"
            onclick="event.preventDefault(); togglePassword();"
        >
            Ingin ganti password?
        </a>


    </section>



    <!-- ==========================================
         EDIT PROFILE
         ========================================== -->

    <section
        class="panel"
        id="editMode"
        style="display:none;"
    >

        <h2>
            Edit Data Diri
        </h2>


        <form
            method="POST"
            action="profile.php"
            enctype="multipart/form-data"
            class="form-grid"
        >


            <div class="field field-full">

                <label>
                    Foto Profil
                </label>

                <input
                    type="file"
                    name="foto_profil"
                    accept="image/*"
                >

            </div>


            <div class="field">

                <label>
                    Nama Lengkap
                </label>

                <input
                    type="text"
                    name="nama"
                    value="<?php
                    echo htmlspecialchars(
                        $pelanggan['nama']
                    );
                    ?>"
                    required
                >

            </div>


            <div class="field">

                <label>
                    No. HP
                </label>

                <input
                    type="text"
                    name="hp"
                    value="<?php
                    echo htmlspecialchars(
                        $pelanggan['hp']
                    );
                    ?>"
                    required
                >

            </div>


            <div class="field">

                <label>
                    Alamat
                </label>

                <input
                    type="text"
                    name="alamat"
                    value="<?php
                    echo htmlspecialchars(
                        $pelanggan['alamat']
                    );
                    ?>"
                    required
                >

            </div>


            <div class="form-actions field-full">


                <button
                    type="submit"
                    name="update_profil"
                    class="btn-solid"
                >
                    Simpan Perubahan
                </button>


                <button
                    type="button"
                    class="btn-outline"
                    onclick="toggleEdit()"
                >
                    Batal
                </button>


            </div>


        </form>

    </section>



    <!-- ==========================================
         GANTI PASSWORD
         ========================================== -->

    <section
        class="panel"
        id="passwordMode"
        style="display:none;"
    >

        <h2>
            Ganti Password
        </h2>


        <?php if (isset($error_password)): ?>

            <div class="alert-error">

                <?php
                echo htmlspecialchars(
                    $error_password
                );
                ?>

            </div>

        <?php endif; ?>


        <?php if (isset($sukses_password)): ?>

            <div class="alert-sukses">

                <?php
                echo htmlspecialchars(
                    $sukses_password
                );
                ?>

            </div>

        <?php endif; ?>


        <form
            method="POST"
            action="profile.php"
            class="form-grid"
        >


            <div class="field field-full">

                <label>
                    Password Lama
                </label>

                <input
                    type="password"
                    name="password_lama"
                    required
                >

            </div>


            <div class="field">

                <label>
                    Password Baru
                </label>

                <input
                    type="password"
                    name="password_baru"
                    required
                >

            </div>


            <div class="field">

                <label>
                    Konfirmasi Password Baru
                </label>

                <input
                    type="password"
                    name="konfirmasi_baru"
                    required
                >

            </div>


            <div class="form-actions field-full">


                <button
                    type="submit"
                    name="ganti_password"
                    class="btn-solid"
                >
                    Ubah Password
                </button>


                <button
                    type="button"
                    class="btn-outline"
                    onclick="togglePassword()"
                >
                    ← Kembali ke Profil
                </button>


            </div>


        </form>

    </section>



    <!-- ==========================================
         RIWAYAT TRANSAKSI
         ========================================== -->

    <section class="panel">


        <h2>
            Riwayat Transaksi
        </h2>


        <?php if (mysqli_num_rows($riwayat) == 0): ?>


            <p class="kosong-riwayat">

                Kamu belum pernah belanja.

                <a href="index.php">
                    Mulai belanja sekarang
                </a>

            </p>


        <?php else: ?>


            <div class="riwayat-list">


                <?php while ($r = mysqli_fetch_assoc($riwayat)): ?>


                    <div class="riwayat-item">


                        <!-- INFO TRANSAKSI -->

                        <div class="riwayat-info">


                            <strong>

                                #
                                <?php
                                echo htmlspecialchars(
                                    $r['id_transaksi']
                                );
                                ?>

                            </strong>


                            <span class="riwayat-tanggal">

                                <?php
                                echo date(
                                    'd/m/Y',
                                    strtotime($r['tanggal'])
                                );
                                ?>

                            </span>


                            <!-- STATUS -->

                            <span
                                class="badge-status status-<?php
                                echo htmlspecialchars(
                                    $r['status_pesanan']
                                );
                                ?>"
                            >

                                <?php
                                echo htmlspecialchars(
                                    label_status(
                                        $r['status_pesanan']
                                    )
                                );
                                ?>

                            </span>


                        </div>



                        <!-- TOTAL -->

                        <div class="riwayat-total">

                            Rp

                            <?php
                            echo number_format(
                                $r['total_harga'],
                                0,
                                ',',
                                '.'
                            );
                            ?>

                        </div>



                        <!-- INVOICE -->

                        <a
                            href="invoice.php?id=<?php
                            echo $r['id_transaksi'];
                            ?>"
                            class="btn-kecil"
                        >
                            Lihat Invoice
                        </a>


                    </div>


                <?php endwhile; ?>


            </div>


        <?php endif; ?>


    </section>


</main>



<!-- ==========================================
     JAVASCRIPT
     ========================================== -->

<script>

function toggleEdit() {

    const view = document.getElementById('viewMode');
    const edit = document.getElementById('editMode');

    const isHidden = edit.style.display === 'none';

    edit.style.display = isHidden ? 'block' : 'none';

    view.style.display = isHidden ? 'none' : 'block';
}


function togglePassword() {

    const view = document.getElementById('viewMode');
    const pass = document.getElementById('passwordMode');

    const isHidden = pass.style.display === 'none';

    pass.style.display = isHidden ? 'block' : 'none';

    view.style.display = isHidden ? 'none' : 'block';
}

</script>


</body>
</html>