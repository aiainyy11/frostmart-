<?php
include '../includes/cek_admin.php';
include '../koneksi.php';

$id_foto = $_GET['id'] ?? null;
$id_produk = $_GET['id_produk'] ?? null;

if ($id_foto) {
    // Ambil dulu path filenya sebelum baris databasenya dihapus
    $q = mysqli_query($conn, "SELECT url_foto FROM tb_foto_produk WHERE id = '$id_foto'");
    $foto = mysqli_fetch_assoc($q);

    if ($foto) {
        $path_file = "../" . $foto['url_foto'];
        // Hapus file fisiknya dari folder uploads (kalau ada)
        if (file_exists($path_file)) {
            unlink($path_file);
        }
        // Hapus baris datanya dari database
        mysqli_query($conn, "DELETE FROM tb_foto_produk WHERE id = '$id_foto'");
    }
}

header("Location: edit_produk.php?id=$id_produk");
exit;