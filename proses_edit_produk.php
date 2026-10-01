<?php
include '../includes/cek_admin.php';
include '../koneksi.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_produk = $_POST['id_produk'];
    $nama = $_POST['nama'];
    $id_kategori = $_POST['id_kategori'];
    $sub_kategori = $_POST['sub_kategori'];
    $harga = $_POST['harga'];
    $stok = $_POST['stok'];
    $tanggal_kadaluwarsa = $_POST['tanggal_kadaluwarsa'] ?: NULL;
    $deskripsi = $_POST['deskripsi'];

    $tanggal_sql = $tanggal_kadaluwarsa ? "'$tanggal_kadaluwarsa'" : "NULL";

    mysqli_query($conn, "
        UPDATE tb_produk SET
            nama = '$nama',
            id_kategori = '$id_kategori',
            sub_kategori = '$sub_kategori',
            harga = '$harga',
            stok = '$stok',
            tanggal_kadaluwarsa = $tanggal_sql,
            deskripsi = '$deskripsi'
        WHERE id = '$id_produk'
    ");

    header("Location: dashboard.php?update_sukses=1");
    exit;
}