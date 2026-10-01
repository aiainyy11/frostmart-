<?php
include '../includes/cek_admin.php';
include '../koneksi.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_produk = $_POST['id_produk'];

    $folder_upload = "../uploads/produk/";
    if (!is_dir($folder_upload)) {
        mkdir($folder_upload, 0777, true);
    }

    // Cari urutan terakhir yang udah ada, biar foto baru nyambung urutannya (bukan mulai dari 0 lagi)
    $q_max = mysqli_query($conn, "SELECT MAX(urutan) AS max_urutan FROM tb_foto_produk WHERE id_produk = '$id_produk'");
    $urutan_terakhir = mysqli_fetch_assoc($q_max)['max_urutan'];
    $urutan_mulai = $urutan_terakhir !== null ? $urutan_terakhir + 1 : 0;

    $jumlah_foto = count($_FILES['foto']['name']);

    for ($i = 0; $i < $jumlah_foto; $i++) {
        $nama_file_asli = $_FILES['foto']['name'][$i];
        $tmp_name = $_FILES['foto']['tmp_name'][$i];
        $nama_file_baru = time() . '_' . $i . '_' . $nama_file_asli;
        $path_tujuan = $folder_upload . $nama_file_baru;

        if (move_uploaded_file($tmp_name, $path_tujuan)) {
            $url_foto = "uploads/produk/" . $nama_file_baru;
            $urutan = $urutan_mulai + $i;
            mysqli_query($conn, "
                INSERT INTO tb_foto_produk (id_produk, url_foto, urutan)
                VALUES ('$id_produk', '$url_foto', '$urutan')
            ");
        }
    }

    header("Location: edit_produk.php?id=$id_produk");
    exit;
}