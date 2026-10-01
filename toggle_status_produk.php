<?php
include '../includes/cek_admin.php';
include '../koneksi.php';

$id_produk = $_GET['id'] ?? null;

if (!$id_produk) {
    header("Location: dashboard.php");
    exit;
}

//ini untuk mengambil status produk sekarang
$q = mysqli_query($conn, "SELECT status FROM tb_produk WHERE id='$id_produk'");
$produk = mysqli_fetch_assoc($q);

if ($produk) {
    //kalau status sekarang sedang aktif, ganti menjadi sembunyikan, dan sebaliknya
    $status_baru = $produk['status'] == 'aktif' ? 'disembunyikan' : 'aktif';
    mysqli_query($conn, "UPDATE tb_produk SET status='$status_baru' WHERE id='$id_produk'");
}

header("Location: dashboard.php");
exit;