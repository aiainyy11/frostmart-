<?php 
include '../includes/cek_admin.php';
include '../koneksi.php';

$id_produk = $_GET['id'] ?? null;

if (!$id_produk) {
    header("Location: dashboard.php");
    exit;
}

// ini untuk mengecek jika produk tersebut pernah muncul di transaksi manapun
$cek = mysqli_query($conn, "SELECT COUNT(*) AS jumlah FROM tb_detail WHERE id_produk='$id_produk'");
$jumlah_dipakai = mysqli_fetch_assoc($cek)['jumlah'];

if ($jumlah_dipakai > 0) {
    header("Location: dashboard.php?gagal_hapus=1");
    exit;
}

// ini untuk menghapus data sekaligus file fisik foto produk
$foto_list = mysqli_query($conn, "SELECT * FROM tb_foto_produk WHERE id_produk = '$id_produk'");
while ($f = mysqli_fetch_assoc($foto_list)) {
    $path_file ="../" . $f['url_foto'];
    if (file_exists($path_file)) {
        unlink($path_file);
    }
}
// yang ada di dalam tb_produk tetap hapus manual
mysqli_query($conn, "DELETE FROM tb_produk WHERE id='$id_produk'");

header("Location: dashboard.php?hapus_sukses=1");
exit;